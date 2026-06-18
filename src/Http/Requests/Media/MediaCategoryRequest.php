<?php

declare(strict_types=1);

namespace Gingerminds\LaravelMediaManager\Http\Requests\Media;

use Gingerminds\LaravelCore\Http\Requests\FormRequestInterface;
use Illuminate\Foundation\Http\FormRequest;

class MediaCategoryRequest extends FormRequest implements FormRequestInterface
{
    /** @return  string[] */
    public function rules(): array
    {
        return [
            'code' => 'required|string|max:255',
            'name' => 'required|string|max:255',
        ];
    }
}
