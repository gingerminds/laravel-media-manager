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

    // Taille max en Ko (kilobytes), utilisée par la règle de validation Laravel "max".
    'max_file_size'      => env('MEDIA_MANAGER_MAX_FILE_SIZE', 5096),
    'max_thumbnail_size' => env('MEDIA_MANAGER_MAX_THUMBNAIL_SIZE', 5096),

    // Requêtes/minute par IP sur GET /api/files/*.
    'files_rate_limit' => (int) env('MEDIA_MANAGER_FILES_RATE_LIMIT', 600),

    // Format de sortie par défaut des images générées via les presets Glide (fm).
    // Peut être surchargé preset par preset en ajoutant une clé 'fm' à ce preset.
    'default_format' => env('MEDIA_MANAGER_DEFAULT_FORMAT', 'webp'),

    'presets' => [
        'micro' => ['w' => 25, 'h' => 25, 'fit' => 'crop',    'q' => 70],
        'thumbnail' => ['w' => 150, 'h' => 150, 'fit' => 'crop',    'q' => 80],
        'card'      => ['w' => 400, 'h' => 300, 'fit' => 'contain', 'q' => 85],
        'hero'      => ['w' => 1280,'h' => 720, 'fit' => 'crop',    'q' => 90],
        // Exemple de surcharge de format pour un preset donné :
        // 'card' => ['w' => 400, 'h' => 300, 'fit' => 'contain', 'q' => 85, 'fm' => 'png'],
    ],
];
