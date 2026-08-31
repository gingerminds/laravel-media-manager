<?php

namespace Gingerminds\LaravelMediaManager\Providers;

use Gingerminds\LaravelCore\Resolver\ResourceResolver as CoreResourceResolver;
use Gingerminds\LaravelMediaManager\Policies\Media\MediaCategoryPolicy;
use Gingerminds\LaravelMediaManager\Resolver\ResourceResolver;
use Illuminate\Contracts\Auth\Access\Gate;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Spatie\Permission\PermissionRegistrar;

class LaravelMediaManagerAuthServiceProvider extends ServiceProvider
{
    public function register(): void
    {
    }

    public function boot(): void
    {
        // Defensive: pins the same 'user' morph alias as gingerminds-core so
        // model_has_roles/model_has_permissions stay consistent regardless of
        // provider boot order.
        Relation::morphMap([
            'user' => CoreResourceResolver::model('user'),
        ]);

        $this->app->make(Gate::class)->policy(ResourceResolver::model('media_category'), MediaCategoryPolicy::class);

        $this->registerPolicies();

        app(PermissionRegistrar::class)
            ->registerPermissions(app(Gate::class));
    }
}
