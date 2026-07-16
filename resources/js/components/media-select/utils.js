/**
 * Small, dependency-free helpers shared by the media-select modules. Kept
 * separate from media-select.js so they stay easy to reuse/test in
 * isolation (e.g. `thumbUrl`/`extractCollection` mirror conventions other
 * admin JS in this package may want without pulling in the whole
 * MediaSelect class).
 */

export const UUID_RE = /^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i;

export function debounce(fn, delay) {
    let timer;
    return function (...args) {
        clearTimeout(timer);
        timer = setTimeout(() => fn.apply(this, args), delay);
    };
}

export function escapeHtml(value) {
    const div = document.createElement('div');
    div.textContent = value ?? '';
    return div.innerHTML;
}

export function thumbUrl(item) {
    const ref = item.thumbnail_reference || item.file_reference;
    if (ref && UUID_RE.test(ref)) {
        return `/api/files/${ref}/thumbnail`;
    }
    return null;
}

export function extractCollection(data) {
    if (Array.isArray(data)) {
        return data;
    }
    return data?.member || data?.['hydra:member'] || [];
}
