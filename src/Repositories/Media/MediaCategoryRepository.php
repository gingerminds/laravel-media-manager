<?php

declare(strict_types=1);

namespace Gingerminds\LaravelMediaManager\Repositories\Media;

use Gingerminds\LaravelCore\Http\Requests\FormRequestInterface;
use Gingerminds\LaravelCore\Models\ResourceModelInterface;
use Gingerminds\LaravelCore\Repositories\AbstractRepository;
use Gingerminds\LaravelCore\Repositories\RepositoryInterface;
use Gingerminds\LaravelMediaManager\Models\Media\MediaCategory;
use Gingerminds\LaravelMediaManager\Resolver\ResourceResolver;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use InvalidArgumentException;

/**
 * @extends AbstractRepository<MediaCategory>
 * @implements RepositoryInterface<MediaCategory>
 */
class MediaCategoryRepository extends AbstractRepository implements RepositoryInterface
{
    public function getModelClass(): string
    {
        return ResourceResolver::model('media_category');
    }

    /**
     * Defaults the collection (including the headless API GetCollection) to
     * manual `position` order, so every consumer — admin tree, category
     * picker, API facets — sees the same order without each having to
     * re-implement it. Left alone when an explicit ?sort=&sortBy= is given.
     *
     * @param  Builder<MediaCategory>  $query
     * @return Builder<MediaCategory>
     */
    protected function initGetQueryBuilder(Builder $query, Request $request): Builder
    {
        $query = parent::initGetQueryBuilder($query, $request);

        if (! $request->filled('sort') || ! $request->filled('sortBy')) {
            $query->orderBy('position');
        }

        return $query;
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

    /**
     * @return Collection<int, MediaCategory>
     */
    public function getRootItems(): Collection
    {
        /** @var class-string<MediaCategory> $modelClass */
        $modelClass = $this->getModelClass();

        return $modelClass::query()
            ->whereNull('parent_id')
            ->orderBy('position')
            ->with('adminChildren')
            ->get();
    }

    /**
     * @return Collection<int, MediaCategory>
     */
    public function getAllForSelect(): Collection
    {
        /** @var class-string<MediaCategory> $modelClass */
        $modelClass = $this->getModelClass();

        return $modelClass::query()->orderBy('position')->get();
    }
}
