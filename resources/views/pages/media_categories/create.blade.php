@extends('gingerminds-core::layouts.crud.form')

@section('title')
    @lang('gingerminds-core::translation.title_m_create', ['model' => __('gingerminds-media-manager::translation.media_categories.name_s')])
@endsection

@section('breadcrumb')
    <x-gingerminds-core::navigation.breadcrumb
        :title="__('gingerminds-core::translation.title_m_create', ['model' => __('gingerminds-media-manager::translation.media_categories.name_s')])"
        :items="[
            ['label' => __('gingerminds-media-manager::translation.media_categories.name_p'), 'url' => route('gingerminds-media-manager.media_categories.index')],
            ['label' => __('gingerminds-core::translation.title_m_create', ['model' => __('gingerminds-media-manager::translation.media_categories.name_s')]), 'active' => true],
        ]"
    />
@endsection

@php
    $action = route('gingerminds-media-manager.media_categories.store');
    $indexRoute = route('gingerminds-media-manager.media_categories.index');
    $method = 'POST';
    $id = 'create-media_category-form';
    $title = __('gingerminds-core::translation.title_m_create', ['model' => __('gingerminds-media-manager::translation.media_categories.name_s')]);
@endphp

@section('fields')
    @include('gingerminds-media-manager::pages.media_categories.partials.fields')
@endsection
