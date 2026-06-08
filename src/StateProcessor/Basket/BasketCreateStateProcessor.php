<?php

declare(strict_types=1);

namespace Gingerminds\LaravelMediaManager\StateProcessor\Basket;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use Gingerminds\LaravelMediaManager\Models\Basket\Basket;
use Gingerminds\LaravelMediaManager\Repositories\Basket\BasketRepository;
use Illuminate\Http\Request;

/**
 * @implements ProcessorInterface<Basket, Basket>
 */
class BasketCreateStateProcessor implements ProcessorInterface
{
    public function __construct(
        private readonly BasketRepository $repository,
        private readonly Request $request,
    ) {
    }

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): Basket
    {
        $user = auth()->guard('sanctum')->user();

        if ($user === null) {
            return $this->repository->create()->load('medias');
        }

        $userBasket     = $this->repository->createForOwner($user);
        $anonymousToken = $this->request->input('anonymous_token');

        if ($anonymousToken !== null) {
            $anonymous = $this->repository->findByToken($anonymousToken);
            if ($anonymous instanceof Basket && $anonymous->owner_type === null) {
                $strategy   = config('gingerminds-media-manager.basket.claim_strategy', 'merge');
                $userBasket = $this->repository->applyClaimStrategy($anonymous, $userBasket, $strategy);
            }
        }

        return $userBasket->load('medias');
    }
}
