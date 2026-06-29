<?php

namespace Gingerminds\LaravelMediaManager\Http\Controllers\Media;

use Gingerminds\LaravelCore\Http\Controllers\AbstractController;
use Gingerminds\LaravelMediaManager\Http\Requests\Media\MediaCategoryRequest;
use Gingerminds\LaravelMediaManager\Models\Media\MediaCategory;
use Gingerminds\LaravelMediaManager\Repositories\Media\MediaCategoryRepository;
use Gingerminds\LaravelMediaManager\Resolver\ResourceResolver;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class MediaCategoryController extends AbstractController
{
    public const string LABEL_S = 'gingerminds-media-manager::translation.media_categories.name_s';

    public function __construct(
        protected readonly MediaCategoryRepository $repository
    ) {
    }

    public function index(Request $request): Factory|View
    {
        $this->authorize('viewAny', ResourceResolver::model('media_category'));

        $rootItems = $this->repository->getRootItems();

        /** @var view-string $view */
        $view = 'gingerminds-media-manager::pages.media_categories.index';

        return view($view, [
            'resource'  => ResourceResolver::model('media_category'),
            'rootItems' => $rootItems,
        ]);
    }

    public function create(): View
    {
        /** @var view-string $view */
        $view = 'gingerminds-media-manager::pages.media_categories.create';

        return view($view, [
            'categories' => $this->repository->getAllForSelect(),
        ]);
    }

    public function edit(MediaCategory $mediaCategory): View
    {
        /** @var view-string $view */
        $view = 'gingerminds-media-manager::pages.media_categories.edit';

        return view($view, [
            'mediaCategory' => $mediaCategory,
            'categories'    => $this->repository->getAllForSelect(),
        ]);
    }

    public function store(MediaCategoryRequest $request): RedirectResponse
    {
        $this->authorize('create', ResourceResolver::model('media_category'));

        /** @var MediaCategory $mediaCategory */
        $mediaCategory = $this->repository->update($request, new MediaCategory());

        return redirect()->route('gingerminds-media-manager.media-categories.index')
            ->with('success', __('gingerminds-core::translation.successfully_created', [
                'model' => __(self::LABEL_S)
                    . ' '
                    . ($mediaCategory->name ?? $mediaCategory->id),
            ]));
    }

    public function update(MediaCategoryRequest $request, MediaCategory $mediaCategory): RedirectResponse
    {
        $this->authorize('update', $mediaCategory);

        $this->repository->update($request, $mediaCategory);

        return redirect()->route('gingerminds-media-manager.media-categories.edit', $request->id)
            ->with('success', __('gingerminds-core::translation.successfully_updated', [
                'model' => __(self::LABEL_S)
                    . ' '
                    . ($mediaCategory->name ?? $mediaCategory->id),
            ]));
    }

    public function destroy(MediaCategory $mediaCategory): RedirectResponse
    {
        $this->authorize('delete', $mediaCategory);
        $mediaCategory->delete();

        return redirect()->route('gingerminds-media-manager.media-categories.index')
            ->with('success', __('gingerminds-core::translation.successfully_deleted', [
                'model' => __(self::LABEL_S)
                    . ' '
                    . ($mediaCategory->name ?? $mediaCategory->id),
            ]));
    }
}
