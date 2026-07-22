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
            // Dissociate and persist the FK change *before* deleting the old
            // file row: `medias.file_id` cascades on delete, so deleting the
            // file while the DB row still references it would cascade-delete
            // this media itself.
            $oldFile = $resourceModel->file;
            $resourceModel->file()->dissociate();
            $resourceModel->save();
            $this->uploadService->delete($oldFile);
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
            // Same reasoning as the `file_remove` branch above: dissociate
            // and save before deleting, to avoid the cascade wiping out
            // the parent media row.
            $oldThumbnail = $resourceModel->thumbnail;
            $resourceModel->thumbnail()->dissociate();
            $resourceModel->save();
            $this->uploadService->delete($oldThumbnail);
        }

        $resourceModel->save();
        return $resourceModel;
    }
}
