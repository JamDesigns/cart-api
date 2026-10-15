<?php
namespace App\Cart\Application\Command\ClearCart;

use App\Cart\Application\Exception\CartNotFound;
use App\Cart\Domain\Repository\CartRepository;

class ClearCartHandler
{
    /**
     * Creates the clear cart command handler.
     *
     * @param CartRepository $repository
     */
    public function __construct(
        private readonly CartRepository $repository
    ) {}

    /**
     * Handles the clear cart command.
     *
     * @param ClearCartCommand $command
     *
     * @return ClearCartResponseDto
     *
     * @throws CartNotFound
     */
    public function __invoke(ClearCartCommand $command): ClearCartResponseDto
    {
        $cart = $this->repository->find($command->cartId);

        if (! $cart) {
            throw new CartNotFound($command->cartId);
        }

        $this->repository->delete($command->cartId);

        return new ClearCartResponseDto($cart->id());
    }
}
