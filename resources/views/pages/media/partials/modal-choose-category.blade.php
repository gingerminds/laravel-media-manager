@props([
    'mediaCategories',
    'createRoute',
])

<div class="modal fade" id="modalChooseCategory" tabindex="-1" aria-labelledby="modalChooseCategoryLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalChooseCategoryLabel">
                    @lang('gingerminds-media-manager::translation.media_categories.action.choose')
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <x-gingerminds-core::form.inputs.select
                        id="categorySelect"
                        :label="false"
                        :required="false"
                        size="xl"
                        :multiple="false"
                >
                    <option value="">— @lang('gingerminds-core::translation.none') —</option>
                    @foreach($mediaCategories as $category)
                        <option value="{{ $category->id }}">{{ $category->name }}</option>
                    @endforeach
                </x-gingerminds-core::form.inputs.select>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary me-2" data-bs-dismiss="modal">
                    @lang('gingerminds-core::translation.action.cancel')
                </button>
                <button type="button" class="btn btn-primary" id="btnConfirmCategory">
                    <i class="bi bi-plus-lg me-1"></i>
                    @lang('gingerminds-core::translation.title_m_create', ['model' => __('gingerminds-media-manager::translation.media.name_s')])
                </button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            document.getElementById('btnConfirmCategory').addEventListener('click', function () {
                const categoryId = document.getElementById('categorySelect').value;
                const baseUrl = "{{ $createRoute }}";
                const url = categoryId ? `${baseUrl}?media_category_id=${categoryId}` : baseUrl;

                window.location.href = url;
            });
        });
    </script>
@endpush