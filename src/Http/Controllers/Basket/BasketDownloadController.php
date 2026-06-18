<?php

declare(strict_types=1);

namespace Gingerminds\LaravelMediaManager\Http\Controllers\Basket;

use Gingerminds\LaravelMediaManager\Models\Basket\Basket;
use Gingerminds\LaravelMediaManager\Repositories\Basket\BasketRepository;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
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
        $basket = $this->repository->findByToken($token);

        if (!$basket instanceof Basket) {
            throw new NotFoundHttpException();
        }

        $user = auth()->guard('sanctum')->user();

        if ($user !== null) {
            auth()->setUser($user);
        }

        if (Gate::denies('download', $basket)) {
            abort(403, 'This action is unauthorized. (BasketPolicy)');
        }

        $medias = $basket->medias;

        if ($medias->isEmpty()) {
            throw new UnprocessableEntityHttpException('The basket is empty.');
        }

        $mediaDisk = config('gingerminds-media-manager.disk', 'public');
        $zipPath   = sys_get_temp_dir() . '/basket_' . uniqid('', true) . '.zip';
        $zip       = new ZipArchive();

        if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            throw new RuntimeException('Could not create zip archive.');
        }

        $addedFiles = 0;
        $tempFiles  = [];

        foreach ($medias as $media) {
            $path = $media->file?->path;

            if ($path === null) {
                continue;
            }

            if (!Storage::disk($mediaDisk)->exists($path)) {
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

        $closeResult = $zip->close();

        if ($closeResult === false) {
            foreach ($tempFiles as $tmpFile) {
                @unlink($tmpFile);
            }
            throw new RuntimeException('Failed to close zip archive.');
        }

        if ($addedFiles === 0 || !file_exists($zipPath)) {
            foreach ($tempFiles as $tmpFile) {
                @unlink($tmpFile);
            }
            throw new UnprocessableEntityHttpException('No valid files found to download.');
        }

        $basket->delete();

        $response = response()->download($zipPath, 'basket.zip');

        app()->terminating(function () use ($tempFiles) {
            foreach ($tempFiles as $tmpFile) {
                @unlink($tmpFile);
            }
        });

        return $response;
    }
}
