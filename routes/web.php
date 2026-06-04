<?php

declare(strict_types=1);

use Gingerminds\LaravelMediaManager\Http\Controllers\Media\MediaController;
use Illuminate\Support\Facades\Route;

Route::middleware('web')
    ->prefix(config('gingerminds-core.admin_prefix'))
    ->name('gingerminds-media-manager.')
    ->group(function () {
        Route::resource('medias', MediaController::class);
    });
