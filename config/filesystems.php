<?php

declare(strict_types=1);

return [
    'disks' => [

        'glide' => [
            'driver' => 'local',
            'root' => storage_path('app/glide'),
            'url' => env('APP_URL') . '/glide',
            'visibility' => 'public',
        ],
    ],
];
