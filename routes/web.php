<?php

declare(strict_types=1);

use Gingerminds\LaravelMediaManager\Resolver\ResourceResolver;
use Illuminate\Support\Facades\Route;

Route::middleware('web')
    ->prefix(config('gingerminds-core.admin_prefix'))
    ->name('gingerminds-media-manager.')
    ->group(function () {
        Route::resource(
            'medias',
            ResourceResolver::controller('media')
        );
        Route::resource(
            'media-categories',
            ResourceResolver::controller('media_category')
        );
    });
