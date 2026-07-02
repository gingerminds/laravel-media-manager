@foreach($items as $mediaCategory)
    <tr>
        <td>{{ $mediaCategory->id }}</td>
        <td>{{ $mediaCategory->code }}</td>
        <td>{{ $mediaCategory->name }}</td>
        <td class="text-end">
            <fieldset class="btn-group">
                <a class="btn btn-sm btn-outline-primary" href="{{ route('gingerminds-media-manager.media-categories.edit', $mediaCategory) }}">
                    <i class="bi bi-pencil-square"></i>
                </a>
                <button type="button"
                        class="btn btn-outline-danger btn-sm js-remove-item"
                        data-bs-toggle="modal"
                        data-bs-target="#removeModal"
                        data-model="@lang('gingerminds-media-manager::translation.media_categories.name_s')"
                        data-remove-name="{{ $mediaCategory->name ?? $mediaCategory->id }}"
                        data-destroy-url="{{ route('gingerminds-media-manager.media-categories.destroy', $mediaCategory) }}"
                >
                    <i class="bi-i bi-trash"></i>
                </button>
            </fieldset>
        </td>
    </tr>
@endforeach
