<?php

namespace Gingerminds\LaravelMediaManager\Services\File;

use Gingerminds\LaravelMediaManager\Models\File\File;
use Illuminate\Support\Facades\Storage;

class GlideCacheService
{
    public const string CACHE_PREFIX = '.cache';

    public function clear(File $file): void
    {
        $disk     = Storage::disk($file->disk);
        $cacheDir = self::CACHE_PREFIX . '/' . $file->path;

        if ($disk->exists($cacheDir)) {
            $disk->deleteDirectory($cacheDir);
        }
    }

    public function clearAll(string $disk): void
    {
        $storage = Storage::disk($disk);

        if ($storage->exists(self::CACHE_PREFIX)) {
            $storage->deleteDirectory(self::CACHE_PREFIX);
        }
    }
}
