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

export function renderCard(item, config) {
    const div = document.createElement('div');
    div.className = 'media-select-item';
    div.setAttribute('data-media-item', '');
    div.dataset.id = item.id;
    div.dataset.name = item.name || '';
    div.dataset.thumb = item.thumbnail_reference || '';
    div.dataset.file = item.file_reference || '';
    div.dataset.languages = Array.isArray(item.language_isos) ? item.language_isos.join(',') : '';

    const url = thumbUrl(item);
    const thumbHtml = url
        ? `<img src="${url}" alt="" loading="lazy">`
        : '<i class="bi bi-file-earmark-fill"></i>';

    const languagesLabel = languageLabel(item, config);
    const languagesHtml = languagesLabel
        ? `<div class="media-select-item-languages">${escapeHtml(languagesLabel)}</div>`
        : '';

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
    div.dataset.id = item.id;
    div.dataset.name = item.name || '';
    div.dataset.thumb = item.thumbnail_reference || '';
    div.dataset.file = item.file_reference || '';
    div.dataset.languages = Array.isArray(item.language_isos) ? item.language_isos.join(',') : '';

    const url = thumbUrl(item);
    const thumbHtml = url
        ? `<img src="${url}" alt="">`
        : '<i class="bi bi-file-earmark-fill"></i>';

    const languagesLabel = languageLabel(item, config);
    const languagesHtml = languagesLabel
        ? `<div class="media-select-chip-languages">${escapeHtml(languagesLabel)}</div>`
        : '';

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
