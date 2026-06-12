<?php

declare(strict_types=1);

namespace Gingerminds\LaravelMediaManager\Http\Controllers\File;

use Gingerminds\LaravelMediaManager\Models\File\File;
use Gingerminds\LaravelMediaManager\Services\Processor\ImageProcessor;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ShowFormattedFileController
{
    public function __construct(
        private readonly ImageProcessor $processor
    ) {
    }

    public function __invoke(string $id, string $format): StreamedResponse
    {
        $file = File::findOrFail($id);

        abort_unless($file->isImage(), 400, 'This file is not an image.');
        abort_unless(
            array_key_exists($format, config('gingerminds-media-manager.presets', [])),
            404,
            'Unknown preset.'
        );

        $cachedPath = $this->processor->process($file->path, $format);

        return Storage::disk($file->disk)->response($cachedPath);
    }
}
