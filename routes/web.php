<?php

declare(strict_types=1);

use Gingerminds\LaravelMediaManager\Resolver\ResourceResolver;
use Illuminate\Support\Facades\Route;

Route::middleware(['web', 'gingerminds-core.auth'])
    ->prefix(config('gingerminds-core.admin_prefix'))
    ->name('gingerminds-media-manager.')
    ->group(function () {
        Route::resource(
            'medias',
            ResourceResolver::controller('media')
        );
        Route::post(
            'media-categories/reorder',
            [ResourceResolver::controller('media_category'), 'reorder']
        )->name('media-categories.reorder');
        Route::resource(
            'media-categories',
            ResourceResolver::controller('media_category')
        );
    });
