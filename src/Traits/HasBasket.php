<?php

declare(strict_types=1);

namespace Gingerminds\LaravelMediaManager\Traits;

use Gingerminds\LaravelMediaManager\Models\Basket\Basket;
use Illuminate\Database\Eloquent\Relations\MorphMany;

trait HasBasket
{
    public function baskets(): MorphMany
    {
        return $this->morphMany(Basket::class, 'owner');
    }
}
