<?php
namespace App\Cart\Application\Query\GetCarts;

use App\Cart\Domain\Repository\CartRepository;

final class GetCartsHandler
{
    /**
     * Creates the get carts query handler.
     *
     * @param CartRepository $repository
     */
    public function __construct(
        private readonly CartRepository $repository
    ) {}

    /**
     * Handles the get carts query.
     *
     * @param GetCartsQuery $query
     *
     * @return GetCartsResponseDto[]
     */
    public function __invoke(GetCartsQuery $query): array
    {
        return array_map(
            static fn($cart) => GetCartsResponseDto::fromCart($cart),
            $this->repository->all()
        );
    }
}
