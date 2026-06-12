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
@endphp

<div class="{{ $sizeClass }}">
    <label class="form-label" for="{{ $id }}">
        {{ $label }}
        @if($required)
            <span class="text-danger">*</span>
        @endif
    </label>

    <div
            class="dropzone-wrapper @error($id) is-invalid @enderror {{ $disabled ? 'dropzone-disabled' : '' }}"
            id="{{ $id }}-dropzone"
            role="region"
            aria-label="@lang('gingerminds-media-manager::translation.form.message.file.dropzone_for') {{ $label }}"
    >
        {{-- Input caché --}}
        <input
                type="file"
                id="{{ $id }}"
                name="{{ $id }}{{ $multiple ? '[]' : '' }}"
                class="dropzone-input visually-hidden"
                @if($accept) accept="{{ $accept }}" @endif
                @if($multiple) multiple @endif
                @if($required) required @endif
                @if($disabled) disabled @endif
                aria-describedby="{{ $id }}-help {{ $id }}-error"
                {{ $attributes }}
        />

        {{-- Zone de drop --}}
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
                        @if($disabled) disabled @endif
                >
                    <i class="bi bi-folder2-open me-1" aria-hidden="true"></i>
                    @lang('gingerminds-media-manager::translation.action.browse')
                </button>
            </div>
        </div>

        {{-- Preview des fichiers sélectionnés --}}
        @if($preview)
            <ul
                    class="dropzone-files list-unstyled mb-0 {{ $existingFile ? '' : 'd-none' }}"
                    id="{{ $id }}-files"
                    aria-live="polite"
                    aria-label="@lang('gingerminds-media-manager::translation.form.message.file.selected_files')"
            >
                @if($existingFile)
                    <li class="dropzone-file existing-file d-flex align-items-center gap-2">

                        <i class="bi bi-file-earmark dropzone-file-icon" aria-hidden="true"></i>

                        <span class="dropzone-file-name" title="{{ basename($existingFile) }}">
        {{ \Illuminate\Support\Str::limit(basename($existingFile), 25) }}
    </span>

                        <span class="dropzone-file-size text-muted">
        @if(isset($existingFileSize))
                                {{ $existingFileSize }}
                            @endif
    </span>

                        <a
                                href="{{ Storage::url($existingFile) }}"
                                target="_blank"
                                class="btn btn-sm btn-outline-primary ms-auto"
                        >
                            @lang('gingerminds-core::translation.action.see')
                        </a>

                    </li>
                @endif
            </ul>
        @endif
    </div>

    @if($helper)
        <div class="form-text" id="{{ $id }}-help">{{ $helper }}</div>
    @endif

    @error($id)
    <div class="invalid-feedback d-block" id="{{ $id }}-error">{{ $message }}</div>
    @enderror
</div>