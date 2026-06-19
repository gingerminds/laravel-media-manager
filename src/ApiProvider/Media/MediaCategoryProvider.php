<?php

declare(strict_types=1);

namespace Gingerminds\LaravelMediaManager\ApiProvider\Media;

use ApiPlatform\State\ProviderInterface;
use Gingerminds\LaravelCore\ApiProvider\AbstractApiProvider;
use Gingerminds\LaravelCore\ApiProvider\ApiProviderInterface;
use Gingerminds\LaravelMediaManager\Models\Media\MediaCategory;
use Gingerminds\LaravelMediaManager\Repositories\Media\MediaCategoryRepository;

/**
 * @implements ProviderInterface<MediaCategory>
 */
class MediaCategoryProvider extends AbstractApiProvider implements ProviderInterface, ApiProviderInterface
{
    public function __construct(MediaCategoryRepository $repository)
    {
        parent::__construct($repository);
    }
}
