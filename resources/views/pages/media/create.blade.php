@extends('gingerminds-core::layouts.crud.form')

@section('title')
    @lang('gingerminds-core::translation.title_m_create', ['model' => __('gingerminds-media-manager::translation.media.name_s')])
@endsection

@section('breadcrumb')
    <x-gingerminds-core::navigation.breadcrumb
        :title="__('gingerminds-core::translation.title_m_create', ['model' => __('gingerminds-media-manager::translation.media.name_s')])"
        :items="[
            ['label' => __('gingerminds-media-manager::translation.media.name_p'), 'url' => route('gingerminds-media-manager.medias.index')],
            ['label' => __('gingerminds-core::translation.title_m_create', ['model' => __('gingerminds-media-manager::translation.media.name_s')]), 'active' => true],
        ]"
    />
@endsection

@php
    $action = route('gingerminds-media-manager.medias.store');
    $indexRoute = route('gingerminds-media-manager.medias.index');
    $method = 'POST';
    $id = 'create-media-form';
    $title = __('gingerminds-core::translation.title_m_create', ['model' => __('gingerminds-media-manager::translation.media.name_s')]);
@endphp

@section('fields')
    @include('gingerminds-media-manager::pages.media.partials.fields')
@endsection
