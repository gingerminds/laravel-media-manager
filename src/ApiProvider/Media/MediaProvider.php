<?php

declare(strict_types=1);

namespace Gingerminds\LaravelMediaManager\ApiProvider\Media;

use ApiPlatform\State\ProviderInterface;
use Gingerminds\LaravelCore\ApiProvider\AbstractApiProvider;
use Gingerminds\LaravelCore\ApiProvider\ApiProviderInterface;
use Gingerminds\LaravelMediaManager\Models\Media\Media;
use Gingerminds\LaravelMediaManager\Repositories\Media\MediaRepository;

/**
 * @implements ProviderInterface<Media>
 */
class MediaProvider extends AbstractApiProvider implements ProviderInterface, ApiProviderInterface
{
    public function __construct(MediaRepository $repository)
    {
        parent::__construct($repository);
    }
}
