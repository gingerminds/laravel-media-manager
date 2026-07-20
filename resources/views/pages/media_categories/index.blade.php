@extends('gingerminds-core::layouts.crud.list-tree')

@section('title')
    @lang('gingerminds-media-manager::translation.media_categories.manage')
@endsection

@section('breadcrumb')
    <x-gingerminds-core::navigation.breadcrumb
        :title="__('gingerminds-core::translation.title_list', ['model' => __('gingerminds-media-manager::translation.media_categories.name_p')])"
        :items="[
            ['label' => __('gingerminds-media-manager::translation.media_categories.name_p'), 'url' => route('gingerminds-media-manager.media-categories.index')],
            ['label' => __('gingerminds-media-manager::translation.media_categories.manage'), 'active' => true],
        ]"
    />
@endsection

@section('actions')
    <a href="{{ route('gingerminds-media-manager.media-categories.create') }}" class="btn btn-sm btn-success">
        <i class="bi bi-plus-lg me-1"></i> @lang('gingerminds-core::translation.title_m_create', ['model' => __('gingerminds-media-manager::translation.media_categories.name_s')])
    </a>
@endsection

@section('tree')
    @if($rootItems->isEmpty())
        <div class="text-center text-muted py-5">
            <i class="bi bi-diagram-3 fs-1 d-block mb-2 opacity-25"></i>
            @lang('gingerminds-core::translation.message.no_result')
        </div>
    @else
        @include('gingerminds-media-manager::pages.media_categories.partials.tree', [
            'treeItems' => $rootItems,
            'depth' => 0,
        ])
    @endif
@endsection

@push('scripts')
    <script>
        window.treeReorderUrl = "{{ route('gingerminds-media-manager.media-categories.reorder') }}";
    </script>
@endpush

@push('modals')
    <x-gingerminds-core::modal.modal-delete
        :model="__('gingerminds-media-manager::translation.media_categories.name_s')"
        routing="gingerminds-media-manager.media-categories"/>
@endpush
