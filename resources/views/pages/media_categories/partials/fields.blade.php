<div class="col-lg-12">
    <div class="card">
        <div class="card-body">
            <div class="row mb-3">
                @include('gingerminds-core::components.form.inputs.basic', [
                    'type' => 'text',
                    'id' => 'code',
                    'label' => __('gingerminds-core::translation.form.code'),
                    'required' => true,
                    'value' => old('code', isset($mediaCategory) ? $mediaCategory->code : null)
                ])
                @include('gingerminds-core::components.form.inputs.basic', [
                    'type' => 'text',
                    'id' => 'name',
                    'label' => __('gingerminds-core::translation.form.name'),
                    'required' => true,
                    'value' => old('name', isset($mediaCategory) ? $mediaCategory->name : null)
                ])
            </div>
        </div>
    </div>
</div>
