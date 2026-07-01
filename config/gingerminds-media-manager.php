<?php

declare(strict_types=1);

use Gingerminds\LaravelMediaManager\ApiProvider\Media\MediaCategoryProvider;
use Gingerminds\LaravelMediaManager\Http\Controllers\Media\MediaCategoryController;
use Gingerminds\LaravelMediaManager\Http\Controllers\Media\MediaController;
use Gingerminds\LaravelMediaManager\Http\Requests\Media\MediaCategoryRequest;
use Gingerminds\LaravelMediaManager\Http\Requests\Media\MediaRequest;
use Gingerminds\LaravelMediaManager\Models\Media\Media;
use Gingerminds\LaravelMediaManager\Models\Media\MediaCategory;
use Gingerminds\LaravelMediaManager\Repositories\Media\MediaCategoryRepository;
use Gingerminds\LaravelMediaManager\Repositories\Media\MediaRepository;
use Gingerminds\LaravelMediaManager\ApiProvider\Media\MediaProvider;

return [
    'resources' => [
        'media' => [
            'model' => Media::class,
            'controller' => MediaController::class,
            'repository' => MediaRepository::class,
            'request' => MediaRequest::class,
            'provider' => MediaProvider::class
        ],

        'media_category' => [
            'model' => MediaCategory::class,
            'controller' => MediaCategoryController::class,
            'repository' => MediaCategoryRepository::class,
            'request' => MediaCategoryRequest::class,
            'provider' => MediaCategoryProvider::class,
        ],
    ],
    'basket' => [
        'enabled'        => true,
        'claim_strategy' => 'merge', // merge | replace | ignore
        'owner_models'   => [],
        'storage_disk'   => 'local',
    ],
    'disk'   => env('MEDIA_MANAGER_DISK', 'public'),
    'folder' => env('MEDIA_MANAGER_FOLDER', 'uploads'),

    'presets' => [
        'micro' => ['w' => 25, 'h' => 25, 'fit' => 'crop',    'q' => 70],
        'thumbnail' => ['w' => 150, 'h' => 150, 'fit' => 'crop',    'q' => 80],
        'card'      => ['w' => 400, 'h' => 300, 'fit' => 'contain', 'q' => 85],
        'hero'      => ['w' => 1280,'h' => 720, 'fit' => 'crop',    'q' => 90],
    ],
];
