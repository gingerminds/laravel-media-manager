<?php

declare(strict_types=1);

namespace Gingerminds\LaravelMediaManager\Repositories\Media;

use Gingerminds\LaravelCore\Http\Requests\FormRequestInterface;
use Gingerminds\LaravelCore\Models\ResourceModelInterface;
use Gingerminds\LaravelCore\Repositories\AbstractRepository;
use Gingerminds\LaravelCore\Repositories\RepositoryInterface;
use Gingerminds\LaravelMediaManager\Models\File\File;
use Gingerminds\LaravelMediaManager\Models\Media\Media;
use Gingerminds\LaravelMediaManager\Resolver\ResourceResolver;
use Gingerminds\LaravelMediaManager\Services\File\FileUploadService;
use InvalidArgumentException;

/**
 * @extends AbstractRepository<Media>
 * @implements RepositoryInterface<Media>
 */
class MediaRepository extends AbstractRepository implements RepositoryInterface
{
    public function __construct(
        protected readonly FileUploadService $uploadService,
    ) {
    }

    public function getModelClass(): string
    {
        return ResourceResolver::model('media');
    }

    public function update(
        ?FormRequestInterface $request,
        ResourceModelInterface $resourceModel
    ): ResourceModelInterface {
        if (!$resourceModel instanceof Media) {
            throw new InvalidArgumentException(
                'ResourceModelInterface must be an instance of ' . Media::class
            );
        }

        if (!$request instanceof FormRequestInterface) {
            return $resourceModel;
        }

        $uploadedFile      = $request->file('file');
        $uploadedThumbnail = $request->file('thumbnail');

        if ($uploadedFile !== null) {
            /** @var File|null $oldFile */
            $oldFile = $resourceModel->file;

            $file = $this->uploadService->replace(
                $uploadedFile,
                $oldFile,
                'medias',
                function () use ($resourceModel) {
                    $resourceModel->file()->dissociate();
                    $resourceModel->save();
                }
            );

            $resourceModel->fill([
                'name' => $request->input('name') ?? $file->original_name,
            ]);

            $resourceModel->file()->associate($file);
        } elseif ($request->boolean('file_remove') && $resourceModel->file !== null) {
            $this->uploadService->delete($resourceModel->file);
            $resourceModel->file()->dissociate();
        }

        if ($uploadedThumbnail !== null) {
            /** @var File|null $oldFile */
            $oldFile = $resourceModel->thumbnail;

            $file = $this->uploadService->replace(
                $uploadedThumbnail,
                $oldFile,
                'medias',
                function () use ($resourceModel) {
                    $resourceModel->thumbnail()->dissociate();
                    $resourceModel->save();
                }
            );

            $resourceModel->thumbnail()->associate($file);
        } elseif ($request->boolean('thumbnail_remove') && $resourceModel->thumbnail !== null) {
            $this->uploadService->delete($resourceModel->thumbnail);
            $resourceModel->thumbnail()->dissociate();
        }

        $resourceModel->save();
        return $resourceModel;
    }
}
