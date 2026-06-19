<?php

declare(strict_types=1);

use Gingerminds\LaravelMediaManager\Http\Controllers\Media\MediaCategoryController;
use Gingerminds\LaravelMediaManager\Http\Controllers\Media\MediaController;
use Gingerminds\LaravelMediaManager\Models\Media\Media;
use Gingerminds\LaravelMediaManager\Models\Media\MediaCategory;
use Gingerminds\LaravelMediaManager\Repositories\Media\MediaCategoryRepository;
use Gingerminds\LaravelMediaManager\Repositories\Media\MediaRepository;

return [
    'resources' => [
        'media' => [
            'model' => Media::class,
            'controller' => MediaController::class,
            'repository' => MediaRepository::class
        ],

        'media_category' => [
            'model' => MediaCategory::class,
            'controller' => MediaCategoryController::class,
            'repository' => MediaCategoryRepository::class
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
        'thumbnail' => ['w' => 150, 'h' => 150, 'fit' => 'crop',    'q' => 80],
        'card'      => ['w' => 400, 'h' => 300, 'fit' => 'contain', 'q' => 85],
        'hero'      => ['w' => 1280,'h' => 720, 'fit' => 'crop',    'q' => 90],
    ],
];
