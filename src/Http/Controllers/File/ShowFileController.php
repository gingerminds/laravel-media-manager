<?php

declare(strict_types=1);

namespace Gingerminds\LaravelMediaManager\Http\Controllers\File;

use Gingerminds\LaravelMediaManager\Http\Controllers\File\Concerns\RespondsWithCachedFile;
use Gingerminds\LaravelMediaManager\Models\File\File;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\Response;

class ShowFileController
{
    use RespondsWithCachedFile;

    public function __invoke(Request $request, string $id): Response
    {
        $file = File::findOrFail($id);

        abort_unless(
            Storage::disk($file->disk)->exists($file->path),
            404
        );

        return $this->fileResponse($request, $file->disk, $file->path, $file->original_name);
    }
}
