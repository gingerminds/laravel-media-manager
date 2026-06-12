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
];
