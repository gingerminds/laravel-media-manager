@foreach($items as $media)
    <tr>
        @php
            $previewImgId = $media->file?->id;

            if (str_contains($media->file?->mime_type, 'application')) {
                $previewImgId = $media->thumbnail?->id;
            }
        @endphp
        <td><img src="/api/files/{{ $previewImgId }}/micro" alt=""></td>
        <td>{{ $media->name }}</td>
        <td class="text-end">
            <div class="btn-group" role="group">
                <a class="btn btn-sm btn-outline-primary" href="{{ route('gingerminds-media-manager.medias.edit', $media) }}">
                    <i class="bi bi-pencil-square"></i>
                </a>
                <button type="button"
                        class="btn btn-outline-danger btn-sm js-remove-item"
                        data-bs-toggle="modal"
                        data-bs-target="#removeModal"
                        data-model="@lang('gingerminds-media-manager::translation.media.name_s')"
                        data-remove-name="{{ $media->name ?? $media->id }}"
                        data-destroy-url="{{ route('gingerminds-media-manager.medias.destroy', $media) }}"
                >
                    <i class="bi-i bi-trash"></i>
                </button>
            </div>
        </td>
    </tr>
@endforeach
