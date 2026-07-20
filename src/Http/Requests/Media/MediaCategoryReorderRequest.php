<?php

declare(strict_types=1);

namespace Gingerminds\LaravelMediaManager\Http\Requests\Media;

use Gingerminds\LaravelCore\Http\Requests\FormRequestInterface;
use Illuminate\Foundation\Http\FormRequest;

class MediaCategoryReorderRequest extends FormRequest implements FormRequestInterface
{
    /** @return array<string, list<string>|string> */
    public function rules(): array
    {
        return [
            'ids'       => 'required|array',
            'ids.*'     => 'integer|exists:media_categories,id',
            'parent_id' => 'nullable|integer|exists:media_categories,id',
        ];
    }
}
