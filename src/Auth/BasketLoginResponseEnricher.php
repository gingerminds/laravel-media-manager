<?php

declare(strict_types=1);

namespace Gingerminds\LaravelMediaManager\Auth;

use Gingerminds\LaravelCore\Contracts\Auth\LoginResponseEnricherInterface;
use Gingerminds\LaravelMediaManager\Repositories\Basket\BasketRepository;
use Illuminate\Contracts\Auth\Authenticatable;

class BasketLoginResponseEnricher implements LoginResponseEnricherInterface
{
    public function __construct(private readonly BasketRepository $basketRepository)
    {
    }

    /**
     * @param array<string, mixed> $data
     * @return array<string, mixed>
     */
    public function enrich(Authenticatable $user, array $data): array
    {
        $basket = $this->basketRepository->findOrCreateForOwner($user);

        return array_merge($data, ['basket_token' => $basket->token]);
    }
}
