<?php

namespace Gingerminds\LaravelMediaManager\Services\File;

use Gingerminds\LaravelMediaManager\Models\File\File;
use Illuminate\Support\Facades\Storage;

class GlideCacheService
{
    public function clear(File $file): void
    {
        $disk     = Storage::disk($file->disk);
        $cacheDir = '.cache/' . $file->path;

        if ($disk->exists($cacheDir)) {
            $disk->deleteDirectory($cacheDir);
        }
    }
}
