<?php

declare(strict_types=1);

namespace Gingerminds\LaravelMediaManager\Http\Requests\Media;

use Gingerminds\LaravelCore\Http\Requests\FormRequestInterface;
use Illuminate\Foundation\Http\FormRequest;

class MediaRequest extends FormRequest implements FormRequestInterface
{
    /** @return  string[] */
    public function rules(): array
    {
        return [
            'file' => 'required|file|mimes:jpeg,png,jpg,gif,svg,zip,xlsx,pdf|max:5096',
        ];
    }
}
