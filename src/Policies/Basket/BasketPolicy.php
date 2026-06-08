<?php

declare(strict_types=1);

namespace Gingerminds\LaravelMediaManager\Policies\Basket;

use Gingerminds\LaravelMediaManager\Models\Basket\Basket;
use Illuminate\Contracts\Auth\Authenticatable;

class BasketPolicy
{
    public function view(?Authenticatable $user, Basket $basket): bool
    {
        return $this->isAllowed($user, $basket);
    }

    public function modify(?Authenticatable $user, Basket $basket): bool
    {
        return $this->isAllowed($user, $basket);
    }

    public function download(?Authenticatable $user, Basket $basket): bool
    {
        return $this->isAllowed($user, $basket);
    }

    public function delete(?Authenticatable $user, Basket $basket): bool
    {
        return $this->isAllowed($user, $basket);
    }

    private function isAllowed(?Authenticatable $user, Basket $basket): bool
    {
        if (empty($basket->owner_type) || empty($basket->owner_id)) {
            return true;
        }

        if (!$user instanceof Authenticatable) {
            return false;
        }

        return $basket->owner_type        === get_class($user)
            && (string) $basket->owner_id === (string) $user->getAuthIdentifier();
    }
}
