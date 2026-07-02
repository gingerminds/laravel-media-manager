<?php

declare(strict_types=1);

namespace Gingerminds\LaravelMediaManager\Services\File;

use Gingerminds\LaravelMediaManager\Exceptions\FileUploadException;
use Gingerminds\LaravelMediaManager\Models\File\File;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

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
        $basename  = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $safeName  = str($basename)->slug()->toString() . ($extension !== '' ? '.' . $extension : '');

        $path = $file->storeAs($folder ?? $this->folder, $safeName, $this->disk);

        if ($path === false) {
            throw FileUploadException::couldNotStore();
        }

        return File::create([
            'disk'          => $this->disk,
            'path'          => $path,
            'mime_type'     => $file->getMimeType() ?? 'application/octet-stream',
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

        $this->glideCacheService->clear($file);

        Storage::disk($file->disk)->delete($file->path);

        $file->delete();
    }

    public function url(?File $file): ?string
    {
        if (!$file instanceof File) {
            return null;
        }

        return Storage::disk($file->disk)->url($file->path);
    }
}
