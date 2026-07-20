/**
 * Pure DOM-building functions for the media-select modal's result cards and
 * the field's own selected-item chips. Kept as plain functions (not
 * MediaSelect methods) taking the field's `config` explicitly: nothing here
 * depends on the class instance itself, only on data already available on
 * an item/the field's config.
 */
import { escapeHtml, thumbUrl } from './utils.js';

/**
 * Builds the "language" hint shown next to a media's name (ISO codes,
 * "All languages" or "None"), or null when the field wasn't configured
 * with the `languages` prop (feature disabled, unchanged behavior).
 */
export function languageLabel(item, config) {
    const universe = config.languages;
    if (!Array.isArray(universe) || universe.length === 0) {
        return null;
    }

    const isos = Array.isArray(item.language_isos) ? item.language_isos : [];
    if (isos.length === 0) {
        return config.i18n.noLanguages;
    }

    const matched = isos.filter((iso) => universe.includes(iso));
    if (matched.length >= universe.length) {
        return config.i18n.allLanguages;
    }

    return isos.map((iso) => iso.toUpperCase()).join(', ');
}

/**
 * Copies the id/name/thumb/file/languages dataset shared by both the result
 * card and the selected-item chip onto the given element.
 */
function applyMediaDataset(el, item) {
    el.dataset.id = item.id;
    el.dataset.name = item.name || '';
    el.dataset.thumb = item.thumbnail_reference || '';
    el.dataset.file = item.file_reference || '';
    el.dataset.languages = Array.isArray(item.language_isos) ? item.language_isos.join(',') : '';
}

/**
 * Builds the thumbnail markup shared by the card and the chip. The card
 * lazy-loads its image (it can render many at once in a scrollable list);
 * the chip doesn't (only a handful are ever shown at a time).
 */
function buildThumbHtml(item, { lazy = false } = {}) {
    const url = thumbUrl(item);
    if (!url) {
        return '<i class="bi bi-file-earmark-fill"></i>';
    }

    return `<img src="${url}" alt=""${lazy ? ' loading="lazy"' : ''}>`;
}

/** Builds the language hint markup, wrapped in the caller's own class name (card vs chip). */
function buildLanguagesHtml(item, config, className) {
    const label = languageLabel(item, config);

    return label ? `<div class="${className}">${escapeHtml(label)}</div>` : '';
}

export function renderCard(item, config) {
    const div = document.createElement('div');
    div.className = 'media-select-item';
    div.setAttribute('data-media-item', '');
    applyMediaDataset(div, item);

    const thumbHtml = buildThumbHtml(item, { lazy: true });
    const languagesHtml = buildLanguagesHtml(item, config, 'media-select-item-languages');

    div.innerHTML = `
        <div class="media-select-item-thumb">${thumbHtml}</div>
        <div class="media-select-item-name" title="${escapeHtml(item.name)}">${escapeHtml(item.name)}</div>
        ${languagesHtml}
        <div class="media-select-item-check"><i class="bi bi-check-circle-fill"></i></div>
    `;

    return div;
}

export function renderChip(item, config) {
    const div = document.createElement('div');
    div.className = 'media-select-chip';
    div.setAttribute('data-chip', '');
    applyMediaDataset(div, item);

    const thumbHtml = buildThumbHtml(item);
    const languagesHtml = buildLanguagesHtml(item, config, 'media-select-chip-languages');

    div.innerHTML = `
        <button type="button" class="media-select-chip-remove" data-role="remove-chip">
            <i class="bi bi-x-circle-fill"></i>
        </button>
        <div class="media-select-chip-thumb">${thumbHtml}</div>
        <div class="media-select-chip-name" title="${escapeHtml(item.name)}">${escapeHtml(item.name)}</div>
        ${languagesHtml}
    `;

    return div;
}
