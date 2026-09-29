<?php

namespace Gingerminds\LaravelMediaManager\Http\Controllers\Media;

use Gingerminds\LaravelCore\Http\Controllers\AbstractController;
use Gingerminds\LaravelMediaManager\Http\Requests\Media\MediaRequest;
use Gingerminds\LaravelMediaManager\Models\Media\Media;
use Gingerminds\LaravelMediaManager\Repositories\Media\MediaCategoryRepository;
use Gingerminds\LaravelMediaManager\Repositories\Media\MediaRepository;
use Gingerminds\LaravelMediaManager\Resolver\ResourceResolver;
use Gingerminds\LaravelMediaManager\Services\File\FileUploadService;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class MediaController extends AbstractController
{
    public const string LABEL_S = 'gingerminds-media-manager::translation.media.name_s';

    private const int MAX_SEARCH_ITEMS_PER_PAGE = 100;

    public function __construct(
        protected readonly MediaRepository $repository,
        protected readonly MediaCategoryRepository $mediaCategoryRepository,
        protected readonly FileUploadService $uploadService,
    ) {
    }

    public function index(Request $request): Factory|View
    {
        $this->authorize('viewAny', ResourceResolver::model('media'));

        $items = $this->repository->get($request);

        /** @var view-string $view */
        $view = 'gingerminds-media-manager::pages.media.index';

        return view($view, [
            'resource'        => ResourceResolver::model('media'),
            'items'           => $items,
            'mediaCategories' => $this->mediaCategoryRepository->getRootItems(),
        ]);
    }

    public function search(Request $request): JsonResponse
    {
        $this->authorize('viewAny', ResourceResolver::model('media'));

        $request->merge([
            'itemsPerPage' => min(
                (int) $request->query('itemsPerPage', 24),
                self::MAX_SEARCH_ITEMS_PER_PAGE
            ),
        ]);

        $items = $this->repository->withoutContextScopes()->get($request);

        return response()->json(
            collect($items->items())->map(fn (Media $media) => [
                'id'                  => $media->id,
                'name'                => $media->name,
                'thumbnail_reference' => $media->thumbnail_reference,
                'file_reference'      => $media->file_reference,
                'language_isos'       => $media->language_isos ?? [],
            ])->values()
        );
    }

    public function create(): View
    {
        /** @var view-string $view */
        $view = 'gingerminds-media-manager::pages.media.create';

        return view($view);
    }

    public function edit(Media $media): View
    {
        /** @var view-string $view */
        $view = 'gingerminds-media-manager::pages.media.edit';

        return view($view, [
            'media'      => $media,
            'categories' => $this->mediaCategoryRepository->getAllForSelect(),
        ]);
    }

    public function store(MediaRequest $request): RedirectResponse
    {
        $this->authorize('create', ResourceResolver::model('media'));

        /** @var Media $media */
        $media = $this->repository->update($request, new Media());

        return $this->redirectAfterStore('gingerminds-media-manager.medias', $media->id)
            ->with('success', __('gingerminds-core::translation.successfully_created', [
                'model' => __(self::LABEL_S)
                    . ' '
                    . ($media->name ?? $media->id),
            ]));
    }

    public function update(MediaRequest $request, Media $media): RedirectResponse
    {
        $this->authorize('update', $media);

        $this->repository->update($request, $media);

        return $this->redirectAfterUpdate('gingerminds-media-manager.medias', $media->id)
            ->with('success', __('gingerminds-core::translation.successfully_updated', [
                'model' => __(self::LABEL_S)
                    . ' '
                    . ($media->name ?? $media->id),
            ]));
    }

    public function destroy(Media $media): RedirectResponse
    {
        $this->authorize('delete', $media);

        $file      = $media->file;
        $thumbnail = $media->thumbnail;

        $media->delete();

        $this->uploadService->delete($file);
        $this->uploadService->delete($thumbnail);

        return redirect()->route('gingerminds-media-manager.medias.index')
            ->with('success', __('gingerminds-core::translation.successfully_deleted', [
                'model' => __(self::LABEL_S)
                    . ' '
                    . ($media->name ?? $media->id),
            ]));
    }
}
