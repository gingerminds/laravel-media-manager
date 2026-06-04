@foreach($items as $media)
    <tr>
        <td>{{ $media->id }}</td>
        <td class="text-end">
            <div class="btn-group" role="group">
                <a class="btn btn-sm btn-outline-primary" href="{{ route('gingerminds-media-manager.medias.edit', $media) }}">
                    <i class="bi bi-edit"></i>
                </a>
                <button type="button"
                        class="btn btn-outline-danger btn-sm js-remove-item"
                        data-bs-toggle="modal"
                        data-bs-target="#removeModal"
                        data-model="@lang('gingerminds-media-manager::translation.media.name_s')"
                        data-remove-name="{{ $media->name ?? $media->id }}"
                        data-destroy-url="{{ route('gingerminds-media-manager.medias.destroy', $media) }}"
                >
                    <i class="bx-i bi-trash"></i>
                </button>
            </div>
        </td>
    </tr>
@endforeach
