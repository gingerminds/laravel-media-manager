<?php

declare(strict_types=1);

namespace Gingerminds\LaravelMediaManager\Repositories\Basket;

use Gingerminds\LaravelCore\Http\Requests\FormRequestInterface;
use Gingerminds\LaravelCore\Models\ResourceModelInterface;
use Gingerminds\LaravelCore\Repositories\AbstractRepository;
use Gingerminds\LaravelCore\Repositories\RepositoryInterface;
use Gingerminds\LaravelMediaManager\Models\Basket\Basket;
use Illuminate\Support\Str;
use InvalidArgumentException;

/**
 * @extends AbstractRepository<Basket>
 * @implements RepositoryInterface<Basket>
 */
class BasketRepository extends AbstractRepository implements RepositoryInterface
{
    public function getModelClass(): string
    {
        return Basket::class;
    }

    public function update(
        ?FormRequestInterface $request,
        ResourceModelInterface $resourceModel
    ): ResourceModelInterface {
        if (!$resourceModel instanceof Basket) {
            throw new InvalidArgumentException('ResourceModelInterface must be an instance of ' . Basket::class);
        }

        if (!$request instanceof FormRequestInterface) {
            return $resourceModel;
        }

        $resourceModel->fill($request->all());
        $resourceModel->save();

        return $resourceModel;
    }

    public function findByToken(string $token): ?Basket
    {
        return Basket::where('token', $token)->first();
    }

    /**
     * @param array<mixed> $attributes
     */
    public function create(array $attributes = []): Basket
    {
        return Basket::create(array_merge(['token' => Str::uuid()->toString()], $attributes));
    }

    public function findOrCreateForOwner(mixed $owner): Basket
    {
        return Basket::firstOrCreate(
            [
                'owner_type' => get_class($owner),
                'owner_id'   => $owner->getKey(),
            ],
            ['token' => Str::uuid()->toString()]
        );
    }

    public function createForOwner(mixed $owner): Basket
    {
        Basket::where('owner_type', get_class($owner))
            ->where('owner_id', $owner->getKey())
            ->delete();

        return $this->create([
            'owner_type' => get_class($owner),
            'owner_id'   => $owner->getKey(),
        ]);
    }

    public function applyClaimStrategy(Basket $anonymous, Basket $userBasket, string $strategy): Basket
    {
        $mediaIds = $anonymous->medias()->pluck('media_id');

        match ($strategy) {
            'replace' => $userBasket->medias()->sync($mediaIds),
            'merge'   => $userBasket->medias()->syncWithoutDetaching($mediaIds),
            default   => null,
        };

        $anonymous->delete();

        return $userBasket->refresh();
    }
}
