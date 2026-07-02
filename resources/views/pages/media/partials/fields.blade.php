<div class="col-lg-12">
    <div class="card">
        <div class="card-body">
            <div class="row mb-3">
                <x-gingerminds-core::form.inputs.basic
                        id="name"
                        :label="__('gingerminds-core::translation.form.title')"
                        :value="isset($media) ? $media->name : null"
                        :required="false"
                />
            </div>
            <div class="row">
                <x-gingerminds-media-manager::form.inputs.file
                        id="file"
                        :label="__('gingerminds-media-manager::translation.form.file')"
                        accept="image/*,.pdf,.xlsx,video/mp4,.zip,application/zip"
                        :existing-file="isset($media) ? $media->file : null"
                />
                <x-gingerminds-media-manager::form.inputs.file
                        id="thumbnail"
                        :label="__('gingerminds-media-manager::translation.form.thumbnail')"
                        accept="image/*"
                        :required="false"
                        :existing-file="isset($media) ? $media->thumbnail : null"
                />
            </div>
        </div>
    </div>
</div>

<input type="hidden" name="media_category_id" value="{{ isset($media) && $media->media_category_id ? $media->media_category_id : request()->query('media_category_id') }}">
