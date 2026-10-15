<?php
namespace App\Cart\Application\Command\RemoveProductFromCart;

use App\Cart\Application\Dto\CartResponseDto;
use App\Cart\Application\Exception\CartNotFound;
use App\Cart\Domain\Exception\ProductNotFoundInCart;
use App\Cart\Domain\Model\Product;
use App\Cart\Domain\Repository\CartRepository;
use App\Shared\Domain\Event\EventBus;

class RemoveProductFromCartHandler
{
    /**
     * Creates the remove product from cart command handler.
     *
     * @param CartRepository $repository
     * @param EventBus       $eventBus
     */
    public function __construct(
        private readonly CartRepository $repository,
        private readonly EventBus $eventBus
    ) {}

    /**
     * Handles the remove product from cart command.
     *
     * @param RemoveProductFromCartCommand $command
     *
     * @return CartResponseDto
     *
     * @throws CartNotFound
     * @throws ProductNotFoundInCart
     * @throws \InvalidArgumentException
     */
    public function __invoke(RemoveProductFromCartCommand $command): CartResponseDto
    {
        $cart = $this->repository->find($command->cartId);

        if (! $cart) {
            throw new CartNotFound($command->cartId);
        }

        $product = new Product($command->product);

        $cart->removeProduct($product);

        $this->repository->save($cart);

        foreach ($cart->pullDomainEvents() as $event) {
            $this->eventBus->recordEvent($event);
        }

        $this->eventBus->dispatchEvents();

        return CartResponseDto::fromCart($cart);
    }
}
