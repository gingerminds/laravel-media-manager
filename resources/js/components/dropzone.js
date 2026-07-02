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

function initDropzone(wrapperId) {
    const wrapper  = document.getElementById(wrapperId);
    if (!wrapper) return;

    const baseId   = wrapperId.replace('-dropzone', '');
    const input    = document.getElementById(baseId);
    const area     = document.getElementById(baseId + '-area');
    const trigger  = document.getElementById(baseId + '-trigger');
    const fileList = document.getElementById(baseId + '-files');
    const maxMb    = parseFloat(wrapper.dataset.maxSize || 10);
    const multiple = input.multiple;

    let selectedFiles = [];

    function renderFiles() {
        if (!fileList) return;
        fileList.innerHTML = '';

        if (selectedFiles.length === 0) {
            fileList.classList.add('d-none');
            return;
        }

        fileList.classList.remove('d-none');

        selectedFiles.forEach(function (file, index) {
            const tooBig = file.size > maxMb * 1048576;
            const li = document.createElement('li');
            li.className = 'dropzone-file-item';
            li.innerHTML =
                '<i class="bi ' + getFileIcon(file.type) + ' dropzone-file-icon" aria-hidden="true"></i>' +
                '<span class="dropzone-file-name" title="' + file.name + '">' + file.name + '</span>' +
                '<span class="dropzone-file-size">' + formatSize(file.size) + '</span>' +
                (tooBig ? '<span class="dropzone-file-error" role="alert">Trop volumineux</span>' : '') +
                '<button type="button" class="dropzone-file-remove" data-index="' + index + '" aria-label="Retirer ' + file.name + '">' +
                '<i class="bi bi-x" aria-hidden="true"></i></button>';
            fileList.appendChild(li);
        });

        fileList.querySelectorAll('.dropzone-file-remove').forEach(function (btn) {
            btn.addEventListener('click', function () {
                selectedFiles.splice(parseInt(this.dataset.index), 1);
                syncInput();
                renderFiles();
            });
        });
    }

    function syncInput() {
        const dt = new DataTransfer();
        selectedFiles.forEach(function (f) { dt.items.add(f); });
        input.files = dt.files;
    }

    function addFiles(newFiles) {
        Array.from(newFiles).forEach(function (file) {
            if (!multiple) {
                selectedFiles = [file];
            } else if (!selectedFiles.find(function (f) { return f.name === file.name && f.size === file.size; })) {
                selectedFiles.push(file);
            }
        });
        syncInput();
        renderFiles();
    }

    area.addEventListener('click', function () { input.click(); });
    if (trigger) trigger.addEventListener('click', function (e) { e.stopPropagation(); input.click(); });

    input.addEventListener('change', function () { addFiles(this.files); });

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
        if (e.key === 'Enter' || e.key === ' ') { e.preventDefault(); input.click(); }
    });
}

document.querySelectorAll('[id$="-dropzone"]').forEach(function (el) {
    initDropzone(el.id);
});