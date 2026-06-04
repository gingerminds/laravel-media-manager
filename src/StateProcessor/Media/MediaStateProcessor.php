<?php

declare(strict_types=1);

namespace Gingerminds\LaravelMediaManager\StateProcessor\Media;

use ApiPlatform\State\ProcessorInterface;
use Gingerminds\LaravelCore\StateProcessor\BaseStateProcessor;
use Gingerminds\LaravelMediaManager\Http\Requests\Media\MediaRequest;
use Gingerminds\LaravelMediaManager\Models\Media\Media;
use Gingerminds\LaravelMediaManager\Repositories\Media\MediaRepository;
use Illuminate\Contracts\Validation\Factory as ValidationFactory;

/**
 * @implements ProcessorInterface<Media, Media>
 */
class MediaStateProcessor extends BaseStateProcessor implements ProcessorInterface
{
    public function __construct(
        MediaRepository $repository,
        ValidationFactory $validationFactory
    ) {
        $this->repository    = $repository;
        $this->formRequest   = new MediaRequest();
        $this->resourceModel = new Media();

        parent::__construct($validationFactory);
    }
}
