<div class="col-lg-12">
    <div class="card">
        <div class="card-body">
            <div class="row mb-3">
                <x-gingerminds-media-manager::form.inputs.file
                        id="file"
                        :label="__('gingerminds-media-manager::translation.form.file')"
                        accept="image/*,.pdf,.xlsx,video/mp4,.zip,application/zip"
                        size="sm"
                        :existing-file="isset($media) ? $media->file_name : null"
                />
            </div>
        </div>
    </div>
</div>
