<?php

declare(strict_types=1);

namespace Gingerminds\LaravelMediaManager\Repositories\Media;

use Gingerminds\LaravelCore\Http\Requests\FormRequestInterface;
use Gingerminds\LaravelCore\Models\ResourceModelInterface;
use Gingerminds\LaravelCore\Repositories\AbstractRepository;
use Gingerminds\LaravelCore\Repositories\RepositoryInterface;
use Gingerminds\LaravelMediaManager\Models\Media\MediaCategory;
use InvalidArgumentException;

/**
 * @extends AbstractRepository<MediaCategory>
 * @implements RepositoryInterface<MediaCategory>
 */
class MediaCategoryRepository extends AbstractRepository implements RepositoryInterface
{
    public function getModelClass(): string
    {
        return MediaCategory::class;
    }

    public function update(
        ?FormRequestInterface $request,
        ResourceModelInterface $resourceModel
    ): ResourceModelInterface {
        if (!$resourceModel instanceof MediaCategory) {
            throw new InvalidArgumentException(
                'ResourceModelInterface must be an instance of ' . MediaCategory::class
            );
        }

        if (!$request instanceof FormRequestInterface) {
            return $resourceModel;
        }

        $resourceModel->fill($request->all());
        $resourceModel->save();

        return $resourceModel;
    }
}
