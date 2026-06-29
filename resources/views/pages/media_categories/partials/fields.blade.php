<div class="col-lg-8">
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

<div class="col-lg-4">
    <div class="card">
        <div class="card-body">
            @php
                $selectedParentId = old('parent_id', isset($mediaCategory)
                    ? $mediaCategory->parent_id
                    : (request()->query('parent_id') !== null ? (int) request()->query('parent_id') : null)
                );
            @endphp
            <x-gingerminds-core::form.inputs.select
                id="parent_id"
                :label="__('gingerminds-media-manager::translation.media_categories.form.parent_id')"
                :required="false"
                size="xl"
            >
                <option value="">— @lang('gingerminds-core::translation.none') —</option>
                @foreach($categories as $categoryOption)
                    @if(!isset($mediaCategory) || $categoryOption->id !== $mediaCategory->id)
                        <option
                            value="{{ $categoryOption->id }}"
                            {{ (int)$selectedParentId === (int)$categoryOption->id ? 'selected' : '' }}
                        >{{ $categoryOption->name }}</option>
                    @endif
                @endforeach
            </x-gingerminds-core::form.inputs.select>
        </div>
    </div>
</div>
