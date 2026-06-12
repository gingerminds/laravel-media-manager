<?php

declare(strict_types=1);

namespace Gingerminds\LaravelMediaManager\Services\File;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

class FileUploadService
{
    public function __construct(
        private readonly string $disk = 'public',
        private readonly string $folder = 'uploads',
    ) {
    }

    /**
     * @return array{file_name: string, mime_type: string, size: int}
     */
    public function store(UploadedFile $file, ?string $folder = null): array
    {
        $path = $file->store($folder ?? $this->folder, $this->disk);

        if ($path === false) {
            throw new RuntimeException('Failed to store uploaded file.');
        }

        return [
            'file_name' => $path,
            'mime_type' => $file->getMimeType() ?? 'application/octet-stream',
            'size'      => (int) $file->getSize(),
        ];
    }

    /**
     * @return array{file_name: string, mime_type: string, size: int}
     */
    public function replace(UploadedFile $file, ?string $existing, ?string $folder = null): array
    {
        $this->delete($existing);

        return $this->store($file, $folder);
    }

    public function delete(?string $path): void
    {
        if ($path && Storage::disk($this->disk)->exists($path)) {
            Storage::disk($this->disk)->delete($path);
        }
    }

    public function url(?string $path): ?string
    {
        if (!$path) {
            return null;
        }

        return Storage::disk($this->disk)->url($path);
    }
}
