<?php

declare(strict_types=1);

namespace Gingerminds\LaravelMediaManager\Http\Controllers\Basket;

use Gingerminds\LaravelMediaManager\Models\Basket\Basket;
use Gingerminds\LaravelMediaManager\Repositories\Basket\BasketRepository;
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

        $disk    = config('gingerminds-media-manager.basket.storage_disk', 'local');
        $zipPath = tempnam(sys_get_temp_dir(), 'basket_') . '.zip';
        $zip     = new ZipArchive();
        $zip->open($zipPath, ZipArchive::CREATE);

        foreach ($medias as $media) {
            $path = $media->file?->path;

            if ($path === null) {
                continue;
            }

            $filePath = Storage::disk($disk)->path($path);
            if (file_exists($filePath)) {
                $zip->addFile($filePath, $path);
            }
        }

        $zip->close();
        $basket->delete();

        return response()->download($zipPath, 'basket.zip')->deleteFileAfterSend(true);
    }
}
