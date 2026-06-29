@php $depth = $depth ?? 0; @endphp

<option value="{{ $category->id }}">{{ str_repeat('— ', $depth) }}{{ $category->name }}</option>

@if($category->adminChildren->isNotEmpty())
    @foreach($category->adminChildren as $child)
        @include('gingerminds-media-manager::pages.media.partials.modal-choose-category-options', [
            'category' => $child,
            'depth'    => $depth + 1,
        ])
    @endforeach
@endif
