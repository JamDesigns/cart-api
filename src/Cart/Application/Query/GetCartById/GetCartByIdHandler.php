<?php
namespace App\Cart\Application\Query\GetCartById;

use App\Cart\Application\Dto\CartResponseDto;
use App\Cart\Application\Exception\CartNotFound;
use App\Cart\Domain\Repository\CartRepository;

class GetCartByIdHandler
{
    /**
     * Creates the get cart by id query handler.
     *
     * @param CartRepository $repository
     */
    public function __construct(
        private readonly CartRepository $repository
    ) {}

    /**
     * Handles the get cart by id query.
     *
     * @param GetCartByIdQuery $query
     *
     * @return CartResponseDto
     *
     * @throws CartNotFound
     */
    public function __invoke(GetCartByIdQuery $query): CartResponseDto
    {
        $cart = $this->repository->find($query->cartId);

        if (! $cart) {
            throw new CartNotFound($query->cartId);
        }

        return CartResponseDto::fromCart($cart);
    }
}
