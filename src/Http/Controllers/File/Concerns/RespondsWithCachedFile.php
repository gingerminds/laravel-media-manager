<?php

declare(strict_types=1);

namespace Gingerminds\LaravelMediaManager\Http\Controllers\File\Concerns;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\Response;

trait RespondsWithCachedFile
{
    private function fileResponse(Request $request, string $disk, string $path, ?string $name = null): Response
    {
        $storage      = Storage::disk($disk);
        $lastModified = $storage->lastModified($path);
        $etag         = '"' . md5($path . $lastModified) . '"';
        $cacheControl = 'public, max-age=86400, must-revalidate';

        if ($request->headers->get('If-None-Match') === $etag) {
            return response('', 304, [
                'Cache-Control' => $cacheControl,
                'ETag'          => $etag,
            ]);
        }

        $response = $storage->response($path, $name);
        $response->headers->set('Cache-Control', $cacheControl);
        $response->headers->set('ETag', $etag);
        $response->headers->set('Last-Modified', gmdate('D, d M Y H:i:s', $lastModified) . ' GMT');

        return $response;
    }
}
