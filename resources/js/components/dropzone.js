import { Modal } from 'bootstrap';

function formatSize(bytes) {
    if (bytes < 1024) return bytes + ' o';
    if (bytes < 1048576) return (bytes / 1024).toFixed(1) + ' Ko';
    return (bytes / 1048576).toFixed(1) + ' Mo';
}

function getFileIcon(type) {
    if (type.startsWith('image/')) return 'bi-file-image';
    if (type === 'application/pdf') return 'bi-file-pdf';
    if (type.includes('word')) return 'bi-file-word';
    if (type.includes('excel') || type.includes('spreadsheet')) return 'bi-file-excel';
    if (type.includes('zip') || type.includes('archive')) return 'bi-file-zip';
    return 'bi-file-earmark';
}

/**
 * Returns a local, revocable preview URL for image files picked by the user
 * (nothing is uploaded yet, so this stays client-side).
 */
function localPreviewUrl(file) {
    if (!file.type?.startsWith('image/')) return null;
    if (!file._previewUrl) {
        file._previewUrl = URL.createObjectURL(file);
    }
    return file._previewUrl;
}

function initFileField(fieldId) {
    const field = document.getElementById(fieldId);
    if (!field) return;

    const baseId    = fieldId.replace(/-field$/, '');
    const input     = document.getElementById(baseId);
    const fileList  = document.getElementById(baseId + '-files');
    const modalEl   = document.getElementById(baseId + '-modal');
    const wrapper   = document.getElementById(baseId + '-dropzone');
    const area      = document.getElementById(baseId + '-area');
    const trigger   = document.getElementById(baseId + '-trigger');
    const removeFlag = document.getElementById(baseId + '-remove-flag');

    if (!input || !wrapper || !area) return;

    const maxMb    = parseFloat(field.dataset.maxSize || 10);
    const multiple = input.multiple;

    const labelTooLarge = field.dataset.labelTooLarge || 'File too large';
    const labelRemove   = field.dataset.labelRemove || 'Remove';
    const labelSee       = field.dataset.labelSee || 'See';

    // The existing (already uploaded) file, if any, rendered as a "virtual" entry.
    const existingEntry = field.dataset.existingName ? {
        existing: true,
        name: field.dataset.existingName,
        sizeLabel: field.dataset.existingSizeLabel || '',
        url: field.dataset.existingUrl || null,
        thumbnailUrl: field.dataset.existingThumbnailUrl || null,
    } : null;

    let selectedFiles = existingEntry ? [existingEntry] : [];

    function renderFiles() {
        if (!fileList) return;
        fileList.innerHTML = '';

        if (selectedFiles.length === 0) {
            fileList.classList.add('d-none');
            return;
        }

        fileList.classList.remove('d-none');

        selectedFiles.forEach(function (entry, index) {
            const isExisting = entry.existing === true;
            const sizeLabel  = isExisting ? entry.sizeLabel : formatSize(entry.size);
            const thumbUrl   = isExisting ? entry.thumbnailUrl : localPreviewUrl(entry);
            const tooBig     = !isExisting && entry.size > maxMb * 1048576;

            let actionsHtml = '';
            if (isExisting && entry.url) {
                actionsHtml += '<a href="' + entry.url + '" target="_blank" class="file-preview-action file-preview-action-start file-preview-see" aria-label="' + labelSee + ' ' + entry.name + '">' +
                    '<i class="bi bi-eye-fill" aria-hidden="true"></i></a>';
            }
            actionsHtml += '<button type="button" class="file-preview-action file-preview-remove" data-index="' + index + '" aria-label="' + labelRemove + ' ' + entry.name + '">' +
                '<i class="bi bi-x-circle-fill" aria-hidden="true"></i></button>';

            const fallbackIcon = isExisting ? 'bi-file-earmark-fill' : getFileIcon(entry.type);

            const li = document.createElement('li');
            li.className = 'file-preview-item' + (tooBig ? ' file-preview-item-error' : '');
            li.innerHTML =
                actionsHtml +
                '<div class="file-preview-thumb">' +
                    (thumbUrl
                        ? '<img src="' + thumbUrl + '" alt="">'
                        : '<i class="bi ' + fallbackIcon + '" aria-hidden="true"></i>') +
                '</div>' +
                '<div class="file-preview-name" title="' + entry.name + '">' + entry.name + '</div>' +
                '<div class="file-preview-size">' + sizeLabel + '</div>' +
                (tooBig ? '<div class="file-preview-error" role="alert">' + labelTooLarge + '</div>' : '');
            fileList.appendChild(li);
        });

        fileList.querySelectorAll('.file-preview-remove').forEach(function (btn) {
            btn.addEventListener('click', function () {
                const removed = selectedFiles.splice(parseInt(this.dataset.index, 10), 1)[0];
                if (removed?._previewUrl) {
                    URL.revokeObjectURL(removed._previewUrl);
                }
                if (removed?.existing && removeFlag) {
                    removeFlag.value = '1';
                }
                syncInput();
                renderFiles();
            });
        });
    }

    function syncInput() {
        const dt = new DataTransfer();
        selectedFiles.forEach(function (entry) {
            if (!entry.existing) dt.items.add(entry);
        });
        input.files = dt.files;
    }

    function addFiles(newFiles) {
        if (newFiles.length === 0) return;

        // A real replacement is happening: any pending removal of the existing
        // file is moot since it's about to be superseded by the new upload.
        if (removeFlag) removeFlag.value = '0';

        Array.from(newFiles).forEach(function (file) {
            if (!multiple) {
                selectedFiles = [file];
            } else if (!selectedFiles.find(function (f) { return !f.existing && f.name === file.name && f.size === file.size; })) {
                selectedFiles.push(file);
            }
        });

        syncInput();
        renderFiles();

        if (modalEl) {
            Modal.getOrCreateInstance(modalEl).hide();
        }
    }

    function openPicker() {
        // Reset first so picking the exact same file twice in a row still fires "change".
        input.value = '';
        input.click();
    }

    if (trigger) {
        trigger.addEventListener('click', function (e) {
            // The trigger button lives inside .dropzone-area: stop the click from
            // bubbling up, otherwise the area's own listener also fires and the
            // file picker re-opens itself right after a file is chosen.
            e.stopPropagation();
            openPicker();
        });
    }
    area.addEventListener('click', openPicker);

    input.addEventListener('change', function () {
        addFiles(this.files);
    });

    ['dragenter', 'dragover'].forEach(function (evt) {
        wrapper.addEventListener(evt, function (e) {
            e.preventDefault();
            wrapper.classList.add('dragover');
        });
    });

    ['dragleave', 'drop'].forEach(function (evt) {
        wrapper.addEventListener(evt, function (e) {
            e.preventDefault();
            wrapper.classList.remove('dragover');
        });
    });

    wrapper.addEventListener('drop', function (e) {
        addFiles(e.dataTransfer.files);
    });

    area.setAttribute('tabindex', '0');
    area.addEventListener('keydown', function (e) {
        if (e.key === 'Enter' || e.key === ' ') { e.preventDefault(); openPicker(); }
    });

    renderFiles();
}

// `scope` defaults to the whole document (initial page load, unchanged
// behavior) but can be a specific container to (re-)scan after ajax-injected
// markup — the block canvas's edit modal (add-block.js) does this for its
// `file` type fields, the same way it already does for wysiwyg fields.
// Guarded by a dataset flag so re-scanning an already-initialized field
// (e.g. a fragment re-rendered without a full page reload) is a no-op.
export function initFileFields(scope = document) {
    scope.querySelectorAll('[id$="-field"]').forEach(function (el) {
        if (el.dataset.fileFieldInit) {
            return;
        }
        el.dataset.fileFieldInit = '1';
        initFileField(el.id);
    });
}

document.addEventListener('DOMContentLoaded', () => initFileFields());
