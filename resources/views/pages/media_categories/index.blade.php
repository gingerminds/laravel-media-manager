@extends('gingerminds-core::layouts.crud.list')

@php
    $filters = request()->get('filters', []);
    $indexRoute = 'gingerminds-media-manager.media-categories.index';
@endphp

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

@php
    $columns = [
        ['name' => '#', 'sortable' => false],
        ['name' => __('gingerminds-core::translation.form.code'), 'sortable' => true, 'property' => 'code'],
        ['name' => __('gingerminds-core::translation.form.name'), 'sortable' => true, 'property' => 'name'],
        ['name' => __('gingerminds-core::translation.actions'), 'sortable' => false],
    ];
    $sortBy = request()->query('sortBy');
    $sortOrder = request()->query('sort');
@endphp

@section('table_list')
    @include('gingerminds-media-manager::pages.media_categories.partials.list')
@endsection

@push('modals')
    <x-gingerminds-core::modal.modal-delete :model="__('translation.media_categories.name_s')" routing="gingerminds-media-manager.media_categories"/>
@endpush
