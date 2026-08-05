<?php

declare(strict_types=1);

namespace Gingerminds\LaravelMediaManager\Http\Controllers\File;

use Gingerminds\LaravelMediaManager\Http\Controllers\File\Concerns\RespondsWithCachedFile;
use Gingerminds\LaravelMediaManager\Models\File\File;
use Gingerminds\LaravelMediaManager\Services\Processor\ImageProcessor;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ShowFormattedFileController
{
    use RespondsWithCachedFile;

    public function __construct(
        private readonly ImageProcessor $processor
    ) {
    }

    public function __invoke(Request $request, string $id, string $format): Response
    {
        $file = File::findOrFail($id);

        abort_unless($file->isImage(), 400, 'This file is not an image.');
        abort_unless(
            array_key_exists($format, config('gingerminds-media-manager.presets', [])),
            404,
            'Unknown preset.'
        );

        $cachedPath = $this->processor->process($file->path, $format);

        return $this->fileResponse($request, $file->disk, $cachedPath);
    }
}
