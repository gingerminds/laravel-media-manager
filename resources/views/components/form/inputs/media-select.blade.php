@php use Gingerminds\LaravelMediaManager\Resolver\ResourceResolver; @endphp
@props([
    'id',
    'name' => null,
    'label' => null,
    'multiple' => false,
    'selected' => null,
    'required' => false,
    'disabled' => false,
    'size' => null,
    'helper' => null,
    'filters' => [],
    'categoryCodes' => [],
    'endpoint' => '/api/media',
    'categoryEndpoint' => '/api/media-categories',
    'perPage' => 24,
    'languages' => null,
])

@php
    /**
     * Generic media selector field (single or multiple selection).
     *
     * Single-selection usage (e.g. ProductTranslation::booklet):
     *   <x-gingerminds-media-manager::form.inputs.media-select
     *       id="translations_{{ $language->id }}_booklet_id"
     *       name="translations[{{ $language->id }}][booklet_id]"
     *       :label="__('...')"
     *       :selected="$translation?->booklet"
     *   />
     *
     * Multi-selection usage with a locked filter (e.g. Product::visuals):
     *   <x-gingerminds-media-manager::form.inputs.media-select
     *       id="visuals"
     *       name="visuals"
     *       :multiple="true"
     *       :selected="$product->visuals"
     *       :filters="['media_category_id' => 12]"
     *   />
     *
     * The number of results shown when the modal opens (and per "Load more" page)
     * is configurable per field via the per-page prop (default: 24):
     *   <x-gingerminds-media-manager::form.inputs.media-select ... :per-page="12" />
     *
     * Restrict the proposed categories by their code (instead of their id, which is
     * not stable) with category-codes:
     *   - not set (default): unchanged behavior, every category is proposed.
     *   - several codes: the category select only proposes these categories (with
     *     their relative hierarchy preserved).
     *   - a single code: the select disappears, the search is pre-filtered on that
     *     category and takes up the full width.
     *   <x-gingerminds-media-manager::form.inputs.media-select
     *       ...
     *       :multiple="true"
     *       :category-codes="['picture', 'movie']"
     *   />
     *
     * The package itself has no notion of "language": whether a media has any
     * language association is entirely project-specific. This is why the field
     * never resolves this on its own — it only displays a language hint when the
     * caller explicitly passes the full universe of ISO codes via `languages`:
     *   - not set / empty (default): unchanged behavior, no language info shown.
     *   - set: each media in the results/preview shows, next to its name, either
     *     the list of its ISO codes, "All languages" if it is attached to every
     *     code in `languages`, or "None" if it isn't attached to any. This
     *     requires the resolved Media model to expose a `language_isos` API
     *     property (array of ISO codes), which is a project-level concern.
     *   <x-gingerminds-media-manager::form.inputs.media-select
     *       ...
     *       :languages="$allLanguageIsos"
     *   />
     */

    $fieldName = $name ?? $id;
    $errorKey = str_replace(['[', ']'], ['.', ''], $fieldName);

    $sizeClass = match ($size) {
        'tiny' => 'col-md-2 col-sm-12',
        'sm' => 'col-md-4 col-sm-12',
        'lg' => 'col-md-8 col-sm-12',
        'xl' => 'col-md-12',
        default => 'col-md-6 col-sm-12'
    };

    // Normalizes the initial selection (Model|Collection|array|null) into a plain array.
    $normalizeItem = static function ($media): ?array {
        if ($media === null) {
            return null;
        }

        return [
            'id' => (string) $media->id,
            'name' => (string) ($media->name ?? ''),
            'thumbnail_reference' => $media->thumbnail_reference ?? null,
            'file_reference' => $media->file_reference ?? null,
            // Only populated when the resolved Media model exposes it (project-specific, see doc block above).
            'language_isos' => $media->language_isos ?? [],
        ];
    };

    $isUuid = static fn (?string $value): bool => $value !== null
        && preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i', $value) === 1;

    $thumbUrl = static function (array $item) use ($isUuid): ?string {
        $ref = $item['thumbnail_reference'] ?: $item['file_reference'];
        return $isUuid($ref) ? "/api/files/{$ref}/thumbnail" : null;
    };

    // Universe of ISO codes the caller wants medias checked against (optional,
    // see the doc block above). Not set/empty: language info stays hidden.
    $languageUniverse = array_values(array_filter($languages ?? []));

    $languageLabel = static function (array $isos) use ($languageUniverse): ?string {
        if (empty($languageUniverse)) {
            return null;
        }

        if (empty($isos)) {
            return __('gingerminds-media-manager::translation.media_select.no_languages');
        }

        if (count(array_intersect($isos, $languageUniverse)) >= count($languageUniverse)) {
            return __('gingerminds-media-manager::translation.media_select.all_languages');
        }

        return strtoupper(implode(', ', $isos));
    };

    $selectedItems = collect($multiple ? ($selected ?? []) : array_filter([$normalizeItem($selected)]))
        ->map(fn ($item) => is_array($item) ? $item : $normalizeItem($item))
        ->filter()
        ->values();

    // Restrict the proposed categories by code (stable over time, unlike the id).
    // Not set: every category, no restriction
    $allowedCategoryIds = [];
    if (!empty($categoryCodes)) {
        // No column restriction here: "name" can be a raw column (default package
        // behavior) or a computed accessor (e.g. a project that overrides
        // MediaCategory to make the name translatable via currentTranslation).
        // We therefore access ->name normally afterwards, never through an
        // explicit SELECT that assumes a real column.
        $categoryModelClass = ResourceResolver::model('media_category');
        $resolvedCategories = $categoryModelClass::query()
            ->whereIn('code', $categoryCodes)
            ->get();

        $allowedCategoryIds = $resolvedCategories->pluck('id')->all();

        if ($resolvedCategories->count() === 1) {
            $filters = array_merge($filters, ['media_category_id' => $resolvedCategories->first()->id]);
        }
    }

    $lockCategory = array_key_exists('media_category_id', $filters) && $filters['media_category_id'] !== null && $filters['media_category_id'] !== '';

    $modalId = 'media-select-modal-' . $id;

    $config = [
        'id' => $id,
        'name' => $fieldName,
        'multiple' => (bool) $multiple,
        'modalId' => $modalId,
        'endpoint' => $endpoint,
        'categoryEndpoint' => $categoryEndpoint,
        'filters' => $filters,
        'lockCategory' => $lockCategory,
        'allowedCategoryIds' => $allowedCategoryIds,
        'perPage' => (int) $perPage,
        'languages' => $languageUniverse,
        'i18n' => [
            'empty' => __('gingerminds-media-manager::translation.media_select.no_results'),
            'error' => __('gingerminds-media-manager::translation.media_select.error'),
            'noSelection' => __('gingerminds-media-manager::translation.media_select.no_selection'),
            'allLanguages' => __('gingerminds-media-manager::translation.media_select.all_languages'),
            'noLanguages' => __('gingerminds-media-manager::translation.media_select.no_languages'),
        ],
    ];
@endphp

<div class="{{ $sizeClass }}">
    @if($label)
        <label class="form-label" for="{{ $id }}-open-btn">
            {{ $label }}
            @if($required)
                <span class="text-danger">*</span>
            @endif
        </label>
    @endif

    <div
            class="media-select"
            id="media-select-{{ $id }}"
            data-media-select="{{ json_encode($config) }}"
    >
        <div class="media-select-preview mb-2" data-role="preview" @if($multiple) data-sortable @endif>
            @forelse($selectedItems as $item)
                <div class="media-select-chip" data-chip data-id="{{ $item['id'] }}" data-name="{{ $item['name'] }}"
                     data-thumb="{{ $item['thumbnail_reference'] ?? '' }}"
                     data-file="{{ $item['file_reference'] ?? '' }}"
                     data-languages="{{ implode(',', $item['language_isos'] ?? []) }}">
                    @if(!$disabled)
                        <button type="button" class="media-select-chip-remove" data-role="remove-chip"
                                aria-label="@lang('gingerminds-media-manager::translation.media_select.remove')">
                            <i class="bi bi-x-circle-fill"></i>
                        </button>
                    @endif
                    <div class="media-select-chip-thumb">
                        @if($url = $thumbUrl($item))
                            <img src="{{ $url }}" alt="">
                        @else
                            <i class="bi bi-file-earmark-fill"></i>
                        @endif
                    </div>
                    <div class="media-select-chip-name" title="{{ $item['name'] }}">{{ $item['name'] }}</div>
                    @if($languageText = $languageLabel($item['language_isos'] ?? []))
                        <div class="media-select-chip-languages">{{ $languageText }}</div>
                    @endif
                </div>
            @empty
                <div class="media-select-empty text-muted small" data-role="empty-hint">
                    @lang('gingerminds-media-manager::translation.media_select.no_selection')
                </div>
            @endforelse
        </div>

        <div data-role="hidden-inputs">
            @if($multiple)
                @foreach($selectedItems as $item)
                    <input type="hidden" name="{{ $fieldName }}[]" value="{{ $item['id'] }}">
                @endforeach
            @else
                <input type="hidden" name="{{ $fieldName }}" value="{{ $selectedItems->first()['id'] ?? '' }}">
            @endif
        </div>

        @unless($disabled)
            <button
                    type="button"
                    class="btn btn-outline-primary btn-sm"
                    id="{{ $id }}-open-btn"
                    data-role="open-btn"
                    data-bs-toggle="modal"
                    data-bs-target="#{{ $modalId }}"
            >
                <i class="bi bi-images me-1"></i>
                {{ $multiple
                    ? __('gingerminds-media-manager::translation.media_select.add')
                    : __('gingerminds-media-manager::translation.media_select.select') }}
            </button>
        @endunless

        <div class="modal fade" id="{{ $modalId }}" tabindex="-1" aria-hidden="true"
             aria-labelledby="{{ $modalId }}-label">
            <div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="{{ $modalId }}-label">
                            @lang('gingerminds-media-manager::translation.media_select.modal_title')
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row g-2 mb-3">
                            <div class="{{ $lockCategory ? 'col-12' : 'col-md-8' }}">
                                <input
                                        type="text"
                                        class="form-control"
                                        data-role="search"
                                        placeholder="@lang('gingerminds-media-manager::translation.media_select.search_placeholder')"
                                        autocomplete="off"
                                >
                            </div>
                            @unless($lockCategory)
                                <div class="col-md-4">
                                    <select class="form-select" data-role="category">
                                        <option value="">@lang('gingerminds-media-manager::translation.media_select.all_categories')</option>
                                    </select>
                                </div>
                            @endunless
                        </div>

                        <div class="media-select-results" data-role="results"></div>

                        <div class="text-center mt-3">
                            <button type="button" class="btn btn-sm btn-outline-secondary d-none" data-role="load-more">
                                @lang('gingerminds-media-manager::translation.media_select.load_more')
                            </button>
                        </div>
                    </div>
                    <div class="modal-footer d-flex align-items-center">
                        @if($multiple)
                            <span class="text-muted small me-auto">
                                @lang('gingerminds-media-manager::translation.media_select.selected_count_label')
                                <span data-role="selected-count">0</span>
                            </span>
                        @endif
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                            @lang('gingerminds-core::translation.action.cancel')
                        </button>
                        <button type="button" class="btn btn-primary" data-role="confirm-btn">
                            @lang('gingerminds-media-manager::translation.media_select.confirm')
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @if($helper)
        <div class="form-text">{{ $helper }}</div>
    @endif

    @error($errorKey)
    <div class="invalid-feedback d-block">{{ $message }}</div>
    @enderror
</div>
