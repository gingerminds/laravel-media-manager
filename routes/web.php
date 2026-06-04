<?php

declare(strict_types=1);

use Gingerminds\LaravelMultisite\Http\Controllers\Language\LanguageController;
use Gingerminds\LaravelMultisite\Http\Controllers\Site\SiteController;
use Illuminate\Support\Facades\Route;

Route::middleware('web')
    ->prefix(config('gingerminds-core.admin_prefix'))
    ->name('gingerminds-media-manager.')
    ->group(function () {
    });
