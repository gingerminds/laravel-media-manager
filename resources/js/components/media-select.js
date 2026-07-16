/**
 * Stable public entry point — kept at this exact path/name because
 * gingerminds-laravel-cms imports `initMediaSelectFields` from here via a
 * relative cross-package path (`../../../gingerminds-media-manager/components/media-select.js`,
 * see its add-block.js/repeater.js), and this package's own app.js does too.
 * The actual implementation now lives in ./media-select/ (split into
 * cooperating modules — see that folder's media-select.js docblock).
 */
export { initMediaSelectFields } from './media-select/media-select.js';
