<?php

declare(strict_types=1);

namespace Gingerminds\LaravelMediaManager\Http\Requests\Media;

use Gingerminds\LaravelCore\Http\Requests\FormRequestInterface;
use Gingerminds\LaravelMediaManager\Models\Media\Media;
use Illuminate\Foundation\Http\FormRequest;

class MediaRequest extends FormRequest implements FormRequestInterface
{
    /** @return array<string, list<string>> */
    public function rules(): array
    {
        /** @var Media|null $media */
        $media = $this->route('media');

        $fileRequired = $media === null || $media->file_id === null;

        return [
            'name' => ['nullable', 'string', 'max:255'],
            'file' => [
                $fileRequired ? 'required' : 'nullable',
                'file',
                'mimes:jpeg,png,jpg,gif,svg,zip,xlsx,pdf',
                'max:5096',
            ],
            'thumbnail' => [
                'nullable',
                'file',
                'mimes:jpeg,png,jpg',
                'max:5096',
            ],
        ];
    }
}
