<?php

declare(strict_types=1);

namespace Gingerminds\LaravelMediaManager\ApiProvider\Basket;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use Gingerminds\LaravelCore\ApiProvider\AbstractApiProvider;
use Gingerminds\LaravelCore\ApiProvider\ApiProviderInterface;
use Gingerminds\LaravelMediaManager\Models\Basket\Basket;
use Gingerminds\LaravelMediaManager\Repositories\Basket\BasketRepository;
use Illuminate\Support\Facades\Gate;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * @implements ProviderInterface<Basket>
 */
class BasketProvider extends AbstractApiProvider implements ProviderInterface, ApiProviderInterface
{
    public function __construct(private readonly BasketRepository $basketRepository)
    {
        parent::__construct($basketRepository);
    }

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): object|array|null
    {
        if (!isset($uriVariables['token'])) {
            return parent::provide($operation, $uriVariables, $context);
        }

        $basket = $this->basketRepository->findByToken($uriVariables['token']);

        if (!$basket instanceof Basket) {
            throw new NotFoundHttpException();
        }

        $user = auth()->guard('sanctum')->user();

        if ($user !== null) {
            auth()->setUser($user);
        }

        if (Gate::denies('view', $basket)) {
            abort(403, 'This action is unauthorized. (BasketPolicy)');
        }

        return $basket;
    }
}
