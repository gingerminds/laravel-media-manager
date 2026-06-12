<?php

declare(strict_types=1);

namespace Gingerminds\LaravelMediaManager\Services\Processor;

use Illuminate\Support\Facades\Storage;
use League\Flysystem\Filesystem;
use League\Glide\Server;
use League\Glide\ServerFactory;
use RuntimeException;

class ImageProcessor
{
    private Server $server;

    public function __construct()
    {
        $disk    = config('gingerminds-media-manager.disk');
        $storage = Storage::disk($disk);

        $filesystem = new Filesystem($storage->getAdapter());

        $this->server = ServerFactory::create([
            'source'            => $filesystem,
            'cache'             => $filesystem,
            'cache_path_prefix' => '.cache',
            'driver'            => 'imagick',
            'presets'           => config('gingerminds-media-manager.presets', []),
        ]);
    }

    public function process(string $path, string $preset): string
    {
        $presets = config('gingerminds-media-manager.presets', []);

        if (!array_key_exists($preset, $presets)) {
            throw new RuntimeException("Unknown preset: {$preset}");
        }

        return $this->server->makeImage($path, ['p' => $preset]);
    }
}
