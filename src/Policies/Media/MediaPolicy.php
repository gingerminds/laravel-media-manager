<?php

declare(strict_types=1);

namespace Gingerminds\LaravelMediaManager\Policies\Media;

use Gingerminds\LaravelCore\Models\User\User;
use Gingerminds\LaravelCore\Policies\AbstractResourcePolicy;

class MediaPolicy extends AbstractResourcePolicy
{
    protected function resourceName(): string
    {
        return 'medias';
    }

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(?User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(?User $user): bool
    {
        return true;
    }
}
