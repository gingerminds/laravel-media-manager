<?php

declare(strict_types=1);

namespace Gingerminds\LaravelMediaManager\Http\Controllers\Basket;

use Gingerminds\LaravelMediaManager\Exceptions\ZipArchiveException;
use Gingerminds\LaravelMediaManager\Models\Basket\Basket;
use Gingerminds\LaravelMediaManager\Models\Media\Media;
use Gingerminds\LaravelMediaManager\Repositories\Basket\BasketRepository;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use ZipArchive;

class BasketDownloadController
{
    public function __construct(private readonly BasketRepository $repository)
    {
    }

    public function __invoke(string $token): BinaryFileResponse
    {
        $basket = $this->resolveBasketOrFail($token);

        $this->authorizeDownload($basket);

        $medias    = $this->getMediasOrFail($basket);
        $mediaDisk = config('gingerminds-media-manager.disk', 'public');
        $zipPath   = sys_get_temp_dir() . '/basket_' . uniqid('', true) . '.zip';

        $zip = $this->openZipArchive($zipPath);

        [$addedFiles, $tempFiles] = $this->addMediaFilesToZip($zip, $medias, $mediaDisk);

        $this->closeZipArchive($zip, $tempFiles);

        if ($addedFiles === 0 || !file_exists($zipPath)) {
            $this->cleanupTempFiles($tempFiles);
            throw new UnprocessableEntityHttpException('No valid files found to download.');
        }

        $basket->delete();

        $response = response()->download($zipPath, 'basket.zip');

        app()->terminating(function () use ($tempFiles) {
            $this->cleanupTempFiles($tempFiles);
        });

        return $response;
    }

    private function resolveBasketOrFail(string $token): Basket
    {
        $basket = $this->repository->findByToken($token);

        if (!$basket instanceof Basket) {
            throw new NotFoundHttpException();
        }

        return $basket;
    }

    private function authorizeDownload(Basket $basket): void
    {
        $user = auth()->guard('sanctum')->user();

        if ($user !== null) {
            auth()->setUser($user);
        }

        if (Gate::denies('download', $basket)) {
            abort(403, 'This action is unauthorized. (BasketPolicy)');
        }
    }

    /**
     * @return Collection<int, Media>
     */
    private function getMediasOrFail(Basket $basket): Collection
    {
        $medias = $basket->medias;

        if ($medias->isEmpty()) {
            throw new UnprocessableEntityHttpException('The basket is empty.');
        }

        return $medias;
    }

    private function openZipArchive(string $zipPath): ZipArchive
    {
        $zip = new ZipArchive();

        if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            throw ZipArchiveException::couldNotCreate();
        }

        return $zip;
    }

    /**
     * @param Collection<int, Media> $medias
     * @return array{0: int, 1: array<int, string>}
     */
    private function addMediaFilesToZip(ZipArchive $zip, Collection $medias, string $mediaDisk): array
    {
        $addedFiles = 0;
        $tempFiles  = [];

        foreach ($medias as $media) {
            $path = $media->file?->path;

            if ($path === null || !Storage::disk($mediaDisk)->exists($path)) {
                continue;
            }

            $extension = pathinfo($path, PATHINFO_EXTENSION);
            $safeName  = $media->id
                . '_'
                . $addedFiles
                . ($extension !== '' && $extension !== '0' ? '.' . $extension : '');

            $tmpFile = sys_get_temp_dir() . '/' . $safeName;
            file_put_contents($tmpFile, Storage::disk($mediaDisk)->get($path));

            $zip->addFile($tmpFile, $safeName);
            $tempFiles[] = $tmpFile;
            $addedFiles++;
        }

        return [$addedFiles, $tempFiles];
    }

    /**
     * @param array<int, string> $tempFiles
     */
    private function closeZipArchive(ZipArchive $zip, array $tempFiles): void
    {
        if ($zip->close() === false) {
            $this->cleanupTempFiles($tempFiles);
            throw ZipArchiveException::couldNotClose();
        }
    }

    /**
     * @param array<int, string> $tempFiles
     */
    private function cleanupTempFiles(array $tempFiles): void
    {
        foreach ($tempFiles as $tmpFile) {
            @unlink($tmpFile);
        }
    }
}
