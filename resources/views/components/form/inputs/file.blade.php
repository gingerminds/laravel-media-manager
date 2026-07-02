@props([
    'id',
    'label',
    'size' => null,
    'required' => true,
    'disabled' => false,
    'accept' => null,
    'multiple' => false,
    'maxSize' => 5,
    'helper' => null,
    'preview' => true,
    'existingFile' => null,
    'previewPreset' => 'thumbnail',
])

@php
    $sizeClass = match ($size) {
        'tiny' => 'col-md-2 col-sm-12',
        'sm'   => 'col-md-4 col-sm-12',
        'lg'   => 'col-md-8 col-sm-12',
        'xl'   => 'col-md-12',
        default => 'col-md-6 col-sm-12'
    };

    $typeMap = [
        'image/*'         => 'JPG, PNG, GIF, WebP',
        '.pdf'            => 'PDF',
        '.xlsx'           => 'XLSX',
        'video/mp4'       => 'MP4',
        '.zip'            => 'ZIP',
        'application/zip' => 'ZIP',
    ];

    $acceptLabel = $accept
        ? implode(', ', array_unique(array_filter(
            array_map(
                fn($token) => $typeMap[trim($token)] ?? strtoupper(ltrim(trim($token), '.')),
                explode(',', $accept)
            )
        )))
        : __('gingerminds-media-manager::translation.form.validation.file.all_types');

    // $existingFile accepts either a File model or a plain storage path (legacy usage).
    $existingFileModel = $existingFile instanceof \Gingerminds\LaravelMediaManager\Models\File\File ? $existingFile : null;
    $existingFilePath  = $existingFileModel?->path ?? (is_string($existingFile) ? $existingFile : null);
    $existingFileName  = $existingFileModel?->original_name ?? ($existingFilePath ? basename($existingFilePath) : null);

    // Manual formatting on purpose: Illuminate\Support\Number::fileSize() requires the "intl" extension.
    $formatFileSize = function (int $bytes): string {
        if ($bytes < 1024) {
            return $bytes . ' o';
        }
        if ($bytes < 1048576) {
            return number_format($bytes / 1024, 1) . ' Ko';
        }
        return number_format($bytes / 1048576, 1) . ' Mo';
    };

    $existingFileSizeLabel = $existingFileModel?->size !== null
        ? $formatFileSize($existingFileModel->size)
        : null;
    $existingFileIsImage     = $existingFileModel?->isImage() ?? false;
    $existingFileThumbnailUrl = $existingFileModel && $existingFileIsImage
        ? '/api/files/' . $existingFileModel->id . '/' . $previewPreset
        : null;
    $existingFileUrl = $existingFileModel
        ? '/api/files/' . $existingFileModel->id
        : ($existingFilePath ? Storage::url($existingFilePath) : null);

    $modalId = $id . '-modal';
@endphp

<div class="{{ $sizeClass }}">
    <label class="form-label" for="{{ $id }}">
        {{ $label }}
        @if($required)
            <span class="text-danger">*</span>
        @endif
    </label>

    <div
            class="file-field @error($id) is-invalid @enderror {{ $disabled ? 'file-field-disabled' : '' }}"
            id="{{ $id }}-field"
            data-max-size="{{ $maxSize }}"
            data-label-too-large="@lang('gingerminds-media-manager::translation.form.message.file.too_large')"
            data-label-remove="@lang('gingerminds-core::translation.action.remove')"
            data-label-see="@lang('gingerminds-core::translation.action.see')"
            @if($preview && $existingFileName)
            data-existing-name="{{ $existingFileName }}"
            data-existing-size-label="{{ $existingFileSizeLabel }}"
            data-existing-url="{{ $existingFileUrl }}"
            data-existing-thumbnail-url="{{ $existingFileThumbnailUrl }}"
            @endif
    >
        {{-- Input caché --}}
        <input
                type="file"
                id="{{ $id }}"
                name="{{ $id }}{{ $multiple ? '[]' : '' }}"
                class="file-input visually-hidden"
                @if($accept) accept="{{ $accept }}" @endif
                @if($multiple) multiple @endif
                @if($required) required @endif
                @if($disabled) disabled @endif
                aria-describedby="{{ $id }}-help {{ $id }}-error"
                {{ $attributes }}
        />

        {{-- Flag envoyé au serveur quand un fichier déjà existant est supprimé sans être remplacé --}}
        <input type="hidden" name="{{ $id }}_remove" value="0" id="{{ $id }}-remove-flag">

        {{-- Aperçu des fichiers existants / sélectionnés --}}
        @if($preview)
            <ul
                    class="file-preview-list {{ $multiple ? '' : 'file-preview-list-single' }} list-unstyled mb-2 d-none"
                    id="{{ $id }}-files"
                    aria-live="polite"
                    aria-label="@lang('gingerminds-media-manager::translation.form.message.file.selected_files')"
            ></ul>
        @endif

        {{-- Ouvre la modale d'upload --}}
        <button
                type="button"
                class="btn btn-outline-primary file-upload-trigger"
                data-bs-toggle="modal"
                data-bs-target="#{{ $modalId }}"
                @if($disabled) disabled @endif
        >
            <i class="bi bi-cloud-arrow-up me-1" aria-hidden="true"></i>
            @lang('gingerminds-media-manager::translation.action.upload')
        </button>
    </div>

    @if($helper)
        <div class="form-text" id="{{ $id }}-help">{{ $helper }}</div>
    @endif

    @error($id)
    <div class="invalid-feedback d-block" id="{{ $id }}-error">{{ $message }}</div>
    @enderror
</div>

{{-- Modale contenant la dropzone --}}
<div class="modal fade" id="{{ $modalId }}" tabindex="-1" aria-labelledby="{{ $modalId }}-label" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="{{ $modalId }}-label">{{ $label }}</h5>
                <button
                        type="button"
                        class="btn-close"
                        data-bs-dismiss="modal"
                        aria-label="@lang('gingerminds-media-manager::translation.action.close')"
                ></button>
            </div>
            <div class="modal-body">
                <section
                        class="dropzone-wrapper"
                        id="{{ $id }}-dropzone"
                        aria-label="@lang('gingerminds-media-manager::translation.form.message.file.dropzone_for') {{ $label }}"
                >
                    <div class="dropzone-area" id="{{ $id }}-area">
                        <div class="dropzone-content d-flex flex-column align-items-center gap-2">
                            <i class="bi bi-cloud-arrow-up dropzone-icon d-block text-center w-100" aria-hidden="true"></i>
                            <p class="dropzone-primary">
                                @lang('gingerminds-media-manager::translation.action.drag') {{
                                    $multiple
                                    ? strtolower(__('gingerminds-media-manager::translation.form.message.file.your_file'))
                                    : strtolower(__('gingerminds-media-manager::translation.form.message.file.your_files'))
                                    }} {{ strtolower(__('gingerminds-core::translation.here')) }}
                            </p>
                            <p class="dropzone-secondary">
                                {{ $acceptLabel }} &mdash; max {{ $maxSize }}
                                &nbsp;Mo{{ $multiple ? ' ' . __('gingerminds-media-manager::translation.form.message.file.per_file') : '' }}
                            </p>
                            <button
                                    type="button"
                                    class="btn btn-outline-primary btn-sm dropzone-btn"
                                    id="{{ $id }}-trigger"
                            >
                                <i class="bi bi-folder2-open me-1" aria-hidden="true"></i>
                                @lang('gingerminds-media-manager::translation.action.browse')
                            </button>
                        </div>
                    </div>
                </section>
            </div>
        </div>
    </div>
</div>
