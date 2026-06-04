<?php

namespace Gingerminds\LaravelMediaManager\Repositories\Media;

use Gingerminds\LaravelCore\Http\Requests\FormRequestInterface;
use Gingerminds\LaravelCore\Models\ResourceModelInterface;
use Gingerminds\LaravelCore\Repositories\AbstractRepository;
use Gingerminds\LaravelCore\Repositories\RepositoryInterface;
use Gingerminds\LaravelMediaManager\Models\Media\Media;
use InvalidArgumentException;

/**
 * @extends AbstractRepository<Media>
 * @implements RepositoryInterface<Media>
 */
class MediaRepository extends AbstractRepository implements RepositoryInterface
{
    public function getModelClass(): string
    {
        return Media::class;
    }

    public function update(
        ?FormRequestInterface $request,
        ResourceModelInterface $resourceModel
    ): ResourceModelInterface {
        if (!$resourceModel instanceof Media) {
            throw new InvalidArgumentException('ResourceModelInterface must be an instance of ' . Media::class);
        }

        if (!$request instanceof FormRequestInterface) {
            return $resourceModel;
        }

        $resourceModel->fill($request->all());
        $resourceModel->save();

        return $resourceModel;
    }
}
