/**
 * Generic media selector component (single or multiple selection).
 *
 * Works together with the Blade component x-gingerminds-media-manager::form.inputs.media-select.
 * Queries API Platform directly (/api/media, /api/media-categories)
 */
import { Modal } from 'bootstrap';

(function () {
    const SELECTOR = '[data-media-select]';
    const UUID_RE = /^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i;
    const categoryCache = new Map();

    function debounce(fn, delay) {
        let timer;
        return function (...args) {
            clearTimeout(timer);
            timer = setTimeout(() => fn.apply(this, args), delay);
        };
    }

    function escapeHtml(value) {
        const div = document.createElement('div');
        div.textContent = value ?? '';
        return div.innerHTML;
    }

    function thumbUrl(item) {
        const ref = item.thumbnail_reference || item.file_reference;
        if (ref && UUID_RE.test(ref)) {
            return `/api/files/${ref}/thumbnail`;
        }
        return null;
    }

    function extractCollection(data) {
        if (Array.isArray(data)) {
            return data;
        }
        return data?.member || data?.['hydra:member'] || [];
    }

    /**
     * Rebuilds the category tree from the flat list returned by
     * /api/media-categories (id + parent_id), and flattens it into an ordered
     * list [{ id, label }] with per-depth indentation, using the same convention
     * as modal-choose-category-options.blade.php ('— ').
     */
    function buildCategoryTreeOptions(categories, allowedIds) {
        // All keys are normalized to string to avoid false negatives if the API
        // returns id/parent_id with different JSON types (number vs string)
        // depending on the property.
        const ROOT = '__root__';

        let list = categories;
        if (Array.isArray(allowedIds) && allowedIds.length > 0) {
            const allowedSet = new Set(allowedIds.map(String));
            list = categories.filter((category) => allowedSet.has(String(category.id)));
        }
        const idSet = new Set(list.map((category) => String(category.id)));

        const byParent = new Map();
        list.forEach((category) => {
            const rawParent = category.parent_id;
            // If the actual parent isn't in the retained (filtered) list, the
            // category becomes a visual root of the displayed tree.
            const parentId = (rawParent === null || rawParent === undefined || !idSet.has(String(rawParent)))
                ? ROOT
                : String(rawParent);
            if (!byParent.has(parentId)) {
                byParent.set(parentId, []);
            }
            byParent.get(parentId).push(category);
        });
        byParent.forEach((siblings) => {
            siblings.sort((a, b) => String(a.name || '').localeCompare(String(b.name || '')));
        });

        const options = [];
        const walk = (parentId, depth, seen) => {
            (byParent.get(parentId) || []).forEach((category) => {
                const key = String(category.id);
                if (seen.has(key)) {
                    return; // safeguard against loops if the data is corrupted
                }
                seen.add(key);
                options.push({
                    id: category.id,
                    label: `${'— '.repeat(depth)}${category.name || ''}`,
                });
                walk(key, depth + 1, seen);
            });
        };
        walk(ROOT, 0, new Set());

        return options;
    }

    class MediaSelect {
        constructor(root) {
            this.root = root;
            this.config = JSON.parse(root.dataset.mediaSelect);
            this.modal = document.getElementById(this.config.modalId);
            this.previewEl = root.querySelector('[data-role="preview"]');
            this.hiddenInputsEl = root.querySelector('[data-role="hidden-inputs"]');

            this.selected = new Map();
            this.pending = new Map();
            this.page = 1;
            this.hasMore = false;

            this.previewEl.querySelectorAll('[data-chip]').forEach((chip) => {
                const id = String(chip.dataset.id);
                this.selected.set(id, {
                    id,
                    name: chip.dataset.name || '',
                    thumbnail_reference: chip.dataset.thumb || null,
                    file_reference: chip.dataset.file || null,
                });
            });

            this.bind();
            this.initSortable();
        }

        /**
         * Drag-and-drop reordering of the selected chips (multi-selection only).
         * Reuses Sortable.js, already loaded globally (window.Sortable) by
         * gingerminds-core for the admin's other reorderable lists.
         */
        initSortable() {
            if (!this.config.multiple || typeof window.Sortable === 'undefined') {
                return;
            }

            window.Sortable.create(this.previewEl, {
                animation: 150,
                draggable: '[data-chip]',
                ghostClass: 'sortable-ghost',
                filter: '[data-role="remove-chip"]',
                preventOnFilter: false,
                onEnd: () => {
                    const orderedIds = Array.from(this.previewEl.querySelectorAll('[data-chip]'))
                        .map((chip) => String(chip.dataset.id));

                    const reordered = new Map();
                    orderedIds.forEach((id) => {
                        if (this.selected.has(id)) {
                            reordered.set(id, this.selected.get(id));
                        }
                    });

                    this.selected = reordered;
                    this.syncHiddenInputs();
                },
            });
        }

        bind() {
            this.root.addEventListener('click', (event) => {
                const removeBtn = event.target.closest('[data-role="remove-chip"]');
                if (!removeBtn) {
                    return;
                }
                event.preventDefault();
                const chip = removeBtn.closest('[data-chip]');
                this.selected.delete(String(chip.dataset.id));
                this.renderPreview();
                this.syncHiddenInputs();
                this.root.dispatchEvent(new CustomEvent('media-select:change', {
                    detail: { selected: Array.from(this.selected.values()) },
                }));
            });

            if (!this.modal) {
                return;
            }

            this.searchInput = this.modal.querySelector('[data-role="search"]');
            this.categorySelect = this.modal.querySelector('[data-role="category"]');
            this.resultsEl = this.modal.querySelector('[data-role="results"]');
            this.loadMoreBtn = this.modal.querySelector('[data-role="load-more"]');
            this.confirmBtn = this.modal.querySelector('[data-role="confirm-btn"]');
            this.countEl = this.modal.querySelector('[data-role="selected-count"]');

            this.modal.addEventListener('show.bs.modal', () => {
                this.pending = new Map(this.selected);
                this.page = 1;
                if (this.searchInput) {
                    this.searchInput.value = '';
                }
                this.loadCategories();
                this.fetchResults(true);
                this.updateCount();
            });

            if (this.searchInput) {
                this.searchInput.addEventListener('input', debounce(() => {
                    this.page = 1;
                    this.fetchResults(true);
                }, 300));
            }

            if (this.categorySelect) {
                this.categorySelect.addEventListener('change', () => {
                    this.page = 1;
                    this.fetchResults(true);
                });
            }

            if (this.loadMoreBtn) {
                this.loadMoreBtn.addEventListener('click', () => {
                    this.page += 1;
                    this.fetchResults(false);
                });
            }

            this.resultsEl.addEventListener('click', (event) => {
                const card = event.target.closest('[data-media-item]');
                if (!card) {
                    return;
                }
                this.toggle(card);
            });

            if (this.confirmBtn) {
                this.confirmBtn.addEventListener('click', () => this.confirm());
            }
        }

        toggle(card) {
            const id = String(card.dataset.id);
            if (this.pending.has(id)) {
                this.pending.delete(id);
            } else {
                if (!this.config.multiple) {
                    this.pending.clear();
                }
                this.pending.set(id, {
                    id,
                    name: card.dataset.name || '',
                    thumbnail_reference: card.dataset.thumb || null,
                    file_reference: card.dataset.file || null,
                });
            }
            this.markActiveCards();
            this.updateCount();
        }

        confirm() {
            this.selected = new Map(this.pending);
            this.renderPreview();
            this.syncHiddenInputs();

            Modal.getOrCreateInstance(this.modal).hide();

            this.root.dispatchEvent(new CustomEvent('media-select:change', {
                detail: { selected: Array.from(this.selected.values()) },
            }));
        }

        markActiveCards() {
            this.resultsEl.querySelectorAll('[data-media-item]').forEach((card) => {
                card.classList.toggle('is-selected', this.pending.has(String(card.dataset.id)));
            });
        }

        updateCount() {
            if (this.countEl) {
                this.countEl.textContent = String(this.pending.size);
            }
        }

        buildUrl() {
            const params = new URLSearchParams();
            params.set('page', String(this.page));
            params.set('itemsPerPage', String(this.config.perPage || 24));

            const search = this.searchInput ? this.searchInput.value.trim() : '';
            if (search) {
                params.set('filters[search]', search);
            }

            const filters = { ...(this.config.filters || {}) };
            if (!this.config.lockCategory) {
                if (this.categorySelect?.value) {
                    // A specific category was chosen in the select.
                    filters.media_category_id = this.categorySelect.value;
                } else if (Array.isArray(this.config.allowedCategoryIds) && this.config.allowedCategoryIds.length > 0) {
                    // Nothing chosen ("All categories"), but the field is
                    // restricted to a list of categories (category-codes): filter
                    // on that list instead of showing everything.
                    filters.media_category_id = this.config.allowedCategoryIds;
                }
            }

            Object.entries(filters).forEach(([key, value]) => {
                if (value === null || value === undefined || value === '') {
                    return;
                }
                if (Array.isArray(value)) {
                    value.forEach((item) => params.append(`filters[${key}][]`, item));
                } else {
                    params.set(`filters[${key}]`, value);
                }
            });

            return `${this.config.endpoint}?${params.toString()}`;
        }

        fetchResults(reset) {
            if (reset) {
                this.resultsEl.innerHTML = `<div class="text-center text-muted py-4" style="grid-column: 1 / -1;">
                    <div class="spinner-border spinner-border-sm" role="status"></div>
                </div>`;
            }

            fetch(this.buildUrl(), {
                headers: { Accept: 'application/json' },
                credentials: 'same-origin',
            })
                .then((response) => response.json())
                .then((data) => {
                    const items = extractCollection(data);
                    this.hasMore = items.length >= (this.config.perPage || 24);
                    this.renderResults(items, reset);
                })
                .catch(() => {
                    if (reset) {
                        this.resultsEl.innerHTML = `<div class="text-danger small py-3" style="grid-column: 1 / -1;">
                            ${escapeHtml(this.config.i18n.error)}
                        </div>`;
                    }
                });
        }

        renderResults(items, reset) {
            if (reset) {
                this.resultsEl.innerHTML = '';
            }

            if (reset && items.length === 0) {
                this.resultsEl.innerHTML = `<div class="text-muted small py-4 text-center" style="grid-column: 1 / -1;">
                    ${escapeHtml(this.config.i18n.empty)}
                </div>`;
            }

            const fragment = document.createDocumentFragment();
            items.forEach((item) => fragment.appendChild(this.renderCard(item)));
            this.resultsEl.appendChild(fragment);

            this.markActiveCards();

            if (this.loadMoreBtn) {
                this.loadMoreBtn.classList.toggle('d-none', !this.hasMore);
            }
        }

        renderCard(item) {
            const div = document.createElement('div');
            div.className = 'media-select-item';
            div.setAttribute('data-media-item', '');
            div.dataset.id = item.id;
            div.dataset.name = item.name || '';
            div.dataset.thumb = item.thumbnail_reference || '';
            div.dataset.file = item.file_reference || '';

            const url = thumbUrl(item);
            const thumbHtml = url
                ? `<img src="${url}" alt="" loading="lazy">`
                : '<i class="bi bi-file-earmark-fill"></i>';

            div.innerHTML = `
                <div class="media-select-item-thumb">${thumbHtml}</div>
                <div class="media-select-item-name" title="${escapeHtml(item.name)}">${escapeHtml(item.name)}</div>
                <div class="media-select-item-check"><i class="bi bi-check-circle-fill"></i></div>
            `;

            return div;
        }

        renderPreview() {
            this.previewEl.innerHTML = '';

            if (this.selected.size === 0) {
                this.previewEl.innerHTML = `<div class="media-select-empty text-muted small" data-role="empty-hint">
                    ${escapeHtml(this.config.i18n.noSelection)}
                </div>`;
                return;
            }

            this.selected.forEach((item) => this.previewEl.appendChild(this.renderChip(item)));
        }

        renderChip(item) {
            const div = document.createElement('div');
            div.className = 'media-select-chip';
            div.setAttribute('data-chip', '');
            div.dataset.id = item.id;
            div.dataset.name = item.name || '';
            div.dataset.thumb = item.thumbnail_reference || '';
            div.dataset.file = item.file_reference || '';

            const url = thumbUrl(item);
            const thumbHtml = url
                ? `<img src="${url}" alt="">`
                : '<i class="bi bi-file-earmark-fill"></i>';

            div.innerHTML = `
                <button type="button" class="media-select-chip-remove" data-role="remove-chip">
                    <i class="bi bi-x-circle-fill"></i>
                </button>
                <div class="media-select-chip-thumb">${thumbHtml}</div>
                <div class="media-select-chip-name" title="${escapeHtml(item.name)}">${escapeHtml(item.name)}</div>
            `;

            return div;
        }

        syncHiddenInputs() {
            this.hiddenInputsEl.innerHTML = '';

            if (this.config.multiple) {
                this.selected.forEach((item) => {
                    const input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = `${this.config.name}[]`;
                    input.value = item.id;
                    this.hiddenInputsEl.appendChild(input);
                });
                return;
            }

            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = this.config.name;
            input.value = this.selected.size ? Array.from(this.selected.values())[0].id : '';
            this.hiddenInputsEl.appendChild(input);
        }

        loadCategories() {
            if (this.config.lockCategory || !this.categorySelect || this.categorySelect.dataset.loaded) {
                return;
            }

            const endpoint = this.config.categoryEndpoint;

            if (!categoryCache.has(endpoint)) {
                const url = `${endpoint}?${new URLSearchParams({ itemsPerPage: '200' }).toString()}`;
                categoryCache.set(endpoint, fetch(url, {
                    headers: { Accept: 'application/json' },
                    credentials: 'same-origin',
                })
                    .then((response) => response.json())
                    .then((data) => extractCollection(data)));
            }

            categoryCache.get(endpoint).then((categories) => {
                buildCategoryTreeOptions(categories, this.config.allowedCategoryIds).forEach(({ id, label }) => {
                    const option = document.createElement('option');
                    option.value = id;
                    option.textContent = label;
                    this.categorySelect.appendChild(option);
                });
                this.categorySelect.dataset.loaded = '1';
            }).catch(() => {});
        }
    }

    function init() {
        document.querySelectorAll(SELECTOR).forEach((root) => {
            if (root.dataset.mediaSelectInit) {
                return;
            }
            root.dataset.mediaSelectInit = '1';
            root.mediaSelect = new MediaSelect(root);
        });
    }

    document.addEventListener('DOMContentLoaded', init);
})();
