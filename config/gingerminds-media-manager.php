<?php

declare(strict_types=1);

return [
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
