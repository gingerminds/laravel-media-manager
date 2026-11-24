<?php

declare(strict_types=1);

namespace Gingerminds\LaravelMediaManager\Services\File;

use Gingerminds\LaravelMediaManager\Exceptions\FileUploadException;
use Gingerminds\LaravelMediaManager\Models\File\File;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class FileUploadService
{
    public function __construct(
        private readonly GlideCacheService $glideCacheService,
        private readonly string $disk = 'public',
        private readonly string $folder = 'uploads',
    ) {
    }

    public function store(UploadedFile $file, ?string $folder = null): File
    {
        $extension = $file->getClientOriginalExtension();
        $suffix    = $extension !== '' ? '.' . $extension : '';
        $slug      = str(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME))->slug()->toString();
        $directory = $folder ?? $this->folder;
        $storage   = Storage::disk($this->disk);
        $path      = $directory . '/' . $slug . $suffix;

        // Same name, different content: never overwrite another File's path.
        if ($storage->exists($path) && !$this->hasSameContent($file, $path)) {
            do {
                $path = $directory . '/' . $slug . '-' . Str::lower(Str::random(6)) . $suffix;
            } while ($storage->exists($path));
        }

        // Read before storeAs() moves/streams the file away — normalize()
        // needs a still-existing real path to double-check an OOXML-flavored
        // mime guess (see MimeTypeNormalizer), and getMimeType() itself
        // re-detects from that path on every call rather than caching it.
        $mimeType = MimeTypeNormalizer::normalize(
            $file->getMimeType() ?? 'application/octet-stream',
            $file->getRealPath() ?: null,
        );

        // Identical content already on disk: share its path instead of writing a copy.
        if (!$storage->exists($path) && $file->storeAs($directory, basename($path), $this->disk) === false) {
            throw FileUploadException::couldNotStore();
        }

        return File::create([
            'disk'          => $this->disk,
            'path'          => $path,
            'mime_type'     => $mimeType,
            'original_name' => $file->getClientOriginalName(),
            'size'          => (int) $file->getSize(),
        ]);
    }

    public function replace(
        UploadedFile $file,
        ?File $existing,
        ?string $folder = null,
        ?callable $beforeDelete = null
    ): File {
        if ($existing instanceof File && $beforeDelete !== null) {
            $beforeDelete($existing);
        }

        $this->delete($existing);

        return $this->store($file, $folder);
    }

    public function delete(?File $file): void
    {
        if (!$file instanceof File) {
            return;
        }

        $pathStillUsed = File::query()
            ->where('disk', $file->disk)
            ->where('path', $file->path)
            ->whereKeyNot($file->getKey())
            ->exists();

        if (!$pathStillUsed) {
            $this->glideCacheService->clear($file);

            Storage::disk($file->disk)->delete($file->path);
        }

        $file->delete();
    }

    private function hasSameContent(UploadedFile $file, string $path): bool
    {
        $realPath = $file->getRealPath();

        if ($realPath === false) {
            return false;
        }

        $existing = Storage::disk($this->disk)->checksum($path, ['checksum_algo' => 'md5']);

        return $existing === hash_file('md5', $realPath);
    }

    public function url(?File $file): ?string
    {
        if (!$file instanceof File) {
            return null;
        }

        return Storage::disk($file->disk)->url($file->path);
    }
}
