<?php

declare(strict_types=1);

namespace Gingerminds\LaravelMediaManager\StateProcessor\Basket;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use Gingerminds\LaravelMediaManager\Models\Basket\Basket;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

/**
 * @implements ProcessorInterface<Basket, Basket>
 */
class BasketRemoveMediaStateProcessor implements ProcessorInterface
{
    public function __construct(private readonly Request $request)
    {
    }

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): Basket
    {
        $user = auth()->guard('sanctum')->user();

        if (Gate::forUser($user)->denies('modify', $data)) {
            abort(403, 'This action is unauthorized. (BasketPolicy)');
        }

        $data->medias()->detach((int) $this->request->route('mediaId'));

        return $data->load('medias');
    }
}
