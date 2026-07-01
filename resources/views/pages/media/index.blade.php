@extends('gingerminds-core::layouts.crud.list')

@php
    $filters = request()->get('filters', []);
    $indexRoute = 'gingerminds-media-manager.medias.index';
@endphp

@section('title')
    @lang('gingerminds-media-manager::translation.media.manage')
@endsection

@section('breadcrumb')
    <x-gingerminds-core::navigation.breadcrumb
            :title="__('gingerminds-core::translation.title_list', ['model' => __('gingerminds-media-manager::translation.media.name_p')])"
            :items="[
            ['label' => __('gingerminds-media-manager::translation.media.name_p'), 'url' => route('gingerminds-media-manager.medias.index')],
            ['label' => __('gingerminds-media-manager::translation.media.manage'), 'active' => true],
        ]"
    />
@endsection

@section('actions')
    @if(!empty($mediaCategories))
        <button type="button" class="btn btn-sm btn-success" data-bs-toggle="modal" data-bs-target="#modalChooseCategory">
            <i class="bi bi-plus-lg me-1"></i> @lang('gingerminds-core::translation.title_m_create', ['model' => __('gingerminds-media-manager::translation.media.name_s')])
        </button>
    @else
        <a href="{{ route('gingerminds-media-manager.medias.create') }}" class="btn btn-sm btn-success">
            <i class="bi bi-plus-lg me-1"></i> @lang('gingerminds-core::translation.title_m_create', ['model' => __('gingerminds-media-manager::translation.media.name_s')])
        </a>
    @endif
@endsection

@php
    $columns = [
        ['name' => '', 'sortable' => false],
        ['name' => __('gingerminds-core::translation.form.name'), 'sortable' => true, 'property' => 'name'],
        ['name' => __('gingerminds-core::translation.actions'), 'sortable' => false],
    ];
    $sortBy = request()->query('sortBy');
    $sortOrder = request()->query('sort');
@endphp

@section('table_list')
    @include('gingerminds-media-manager::pages.media.partials.list')
@endsection

@push('modals')
    <x-gingerminds-core::modal.modal-delete :model="__('translation.media.name_s')"
                                            routing="gingerminds-media-manager.medias"/>

    @include('gingerminds-media-manager::pages.media.partials.modal-choose-category', [
        'mediaCategories' => $mediaCategories,
        'createRoute' => route('gingerminds-media-manager.medias.create'),
    ])
@endpush
