<?php
namespace App\Checkout\Application\Query\GetOrders;

use App\Checkout\Domain\Repository\OrderRepository;

final class GetOrdersHandler
{
    /**
     * Creates the get orders query handler.
     *
     * @param OrderRepository $repository
     */
    public function __construct(
        private readonly OrderRepository $repository
    ) {}

    /**
     * Handles the get orders query.
     *
     * @param GetOrdersQuery $query
     *
     * @return GetOrdersResponseDto[]
     */
    public function __invoke(GetOrdersQuery $query): array
    {
        return array_map(
            static fn($order) => GetOrdersResponseDto::fromOrder($order),
            $this->repository->findAll()
        );
    }
}
