<?php

declare(strict_types=1);

namespace Gingerminds\LaravelMediaManager\StateProcessor\Basket;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use Gingerminds\LaravelMediaManager\Models\Basket\Basket;
use Illuminate\Support\Facades\Gate;

/**
 * @implements ProcessorInterface<Basket, null>
 */
class BasketDeleteStateProcessor implements ProcessorInterface
{
    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): null
    {
        $user = auth()->guard('sanctum')->user();

        if (Gate::forUser($user)->denies('delete', $data)) {
            abort(403, 'This action is unauthorized. (BasketPolicy)');
        }

        $data->delete();

        return null;
    }
}
