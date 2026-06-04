<?php

declare(strict_types=1);

namespace Gingerminds\LaravelMediaManager\Providers;

use ApiPlatform\State\ProviderInterface;
use Illuminate\Support\ServiceProvider;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

class LaravelMediaManagerServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $providerPath = __DIR__ . '/../ApiProvider';
        $iterator     = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($providerPath)
        );
        $toTag = [];
        foreach ($iterator as $file) {
            if (!$file->isFile() || $file->getExtension() !== 'php') {
                continue;
            }
            $relativePath = $file->getPathname();
            $relativePath = substr($relativePath, strlen($providerPath) + 1, -4); // retire le préfixe et .php
            $class        = 'Gingerminds\\LaravelMediaManager\\ApiProvider\\'
                . str_replace(DIRECTORY_SEPARATOR, '\\', $relativePath);
            if (class_exists($class) && is_subclass_of($class, ProviderInterface::class)) {
                $toTag[] = $class;
            }
        }
        if ($toTag !== []) {
            $this->app->tag($toTag, ProviderInterface::class);
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
    }
}
