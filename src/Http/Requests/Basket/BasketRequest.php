<?php

declare(strict_types=1);

namespace Gingerminds\LaravelMediaManager\Http\Requests\Basket;

use Gingerminds\LaravelCore\Http\Requests\FormRequestInterface;
use Illuminate\Foundation\Http\FormRequest;

class BasketRequest extends FormRequest implements FormRequestInterface
{
    /** @return string[] */
    public function rules(): array
    {
        return [];
    }
}
