@extends('gingerminds-core::layouts.crud.form')

@section('title')
    @lang('gingerminds-core::translation.title_m_edit', ['model' => __('gingerminds-media-manager::translation.media.name_s')])
@endsection

@section('breadcrumb')
    <x-gingerminds-core::navigation.breadcrumb
        :title="__('gingerminds-core::translation.title_m_edit', ['model' => __('gingerminds-media-manager::translation.media.name_s')])"
        :items="[
            ['label' => __('gingerminds-media-manager::translation.media.name_p'), 'url' => route('gingerminds-media-manager.medias.index')],
            ['label' => __('gingerminds-core::translation.title_m_edit', ['model' => __('gingerminds-media-manager::translation.media.name_s')]), 'active' => true],
        ]"
    />
@endsection

@php
    $action = route('gingerminds-media-manager.medias.update', $media);
    $indexRoute = route('gingerminds-media-manager.medias.index');
    $method = 'PATCH';
    $id = 'edit-media-form';
    $title = __('gingerminds-core::translation.title_m_edit', ['model' => __('gingerminds-media-manager::translation.media.name_s')]);
@endphp

@section('fields')
    @include('gingerminds-media-manager::pages.media.partials.fields')
@endsection
