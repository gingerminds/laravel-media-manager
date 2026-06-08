<?php

declare(strict_types=1);

namespace Gingerminds\LaravelMediaManager\Providers;

use ApiPlatform\State\ProcessorInterface;
use ApiPlatform\State\ProviderInterface;
use Gingerminds\LaravelMediaManager\Auth\BasketLoginResponseEnricher;
use Gingerminds\LaravelMediaManager\Models\Basket\Basket;
use Gingerminds\LaravelMediaManager\Policies\Basket\BasketPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

class LaravelMediaManagerServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__ . '/../../config/gingerminds-media-manager.php',
            'gingerminds-media-manager'
        );

        $this->tagClassesFromPath(
            __DIR__ . '/../ApiProvider',
            'Gingerminds\\LaravelMediaManager\\ApiProvider\\',
            ProviderInterface::class
        );

        // Processors
        $this->tagClassesFromPath(
            __DIR__ . '/../StateProcessor',
            'Gingerminds\\LaravelMediaManager\\StateProcessor\\',
            ProcessorInterface::class
        );

        if (config('gingerminds-media-manager.basket.enabled', true)) {
            $this->app->tag([BasketLoginResponseEnricher::class], 'gingerminds-core.login-enrichers');
        }
    }

    public function boot(): void
    {
        // Chargement des routes du package
        if (! $this->app->routesAreCached()) {
            $this->loadRoutesFrom(__DIR__ . '/../../routes/web.php');
        }

        // Chargement des migrations
        $this->loadMigrationsFrom(__DIR__ . '/../../database/migrations');

        // Chargement des vues
        $this->loadViewsFrom(
            __DIR__ . '/../../resources/views',
            'gingerminds-media-manager'
        );

        // Chargement des traductions
        $this->loadTranslationsFrom(
            __DIR__ . '/../../resources/lang',
            'gingerminds-media-manager'
        );

        // Publication de la config
        $this->publishes([
            __DIR__ . '/../../config/gingerminds-media-manager.php' => config_path('gingerminds-media-manager.php'),
        ], 'gingerminds-media-manager-config');

        // Enregistrement de la policy basket
        if (config('gingerminds-media-manager.basket.enabled', true)) {
            Gate::policy(Basket::class, BasketPolicy::class);
        }
    }

    private function tagClassesFromPath(string $path, string $namespace, string $interface): void
    {
        $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($path));
        $toTag    = [];

        foreach ($iterator as $file) {
            if (!$file->isFile() || $file->getExtension() !== 'php') {
                continue;
            }
            $relative = substr($file->getPathname(), strlen($path) + 1, -4);
            $class    = $namespace . str_replace(DIRECTORY_SEPARATOR, '\\', $relative);

            if (class_exists($class) && is_subclass_of($class, $interface)) {
                $toTag[] = $class;
            }
        }

        if ($toTag !== []) {
            $this->app->tag($toTag, $interface);
        }
    }
}
