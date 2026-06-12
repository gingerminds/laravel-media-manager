<?php

declare(strict_types=1);

namespace Gingerminds\LaravelMediaManager\Http\Controllers\File;

use Gingerminds\LaravelMediaManager\Models\File\File;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ShowFileController
{
    public function __invoke(string $id): StreamedResponse
    {
        $file = File::findOrFail($id);

        abort_unless(
            Storage::disk($file->disk)->exists($file->path),
            404
        );

        return Storage::disk($file->disk)->response(
            $file->path,
            $file->original_name
        );
    }
}
