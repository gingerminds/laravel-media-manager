<?php

namespace Gingerminds\LaravelMediaManager\Providers;

use Gingerminds\LaravelMediaManager\Models\Media\MediaCategory;
use Gingerminds\LaravelMediaManager\Policies\Media\MediaCategoryPolicy;
use Illuminate\Contracts\Auth\Access\Gate;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Spatie\Permission\PermissionRegistrar;

class LaravelMediaManagerAuthServiceProvider extends ServiceProvider
{
    protected $policies = [
        MediaCategory::class => MediaCategoryPolicy::class,
    ];

    /**
     * Register services.
     */
    public function register(): void
    {
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        $this->registerPolicies();

        app(PermissionRegistrar::class)
            ->registerPermissions(app(Gate::class));
    }
}
