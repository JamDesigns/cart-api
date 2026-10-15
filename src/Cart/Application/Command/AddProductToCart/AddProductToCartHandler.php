<?php
namespace App\Cart\Application\Command\AddProductToCart;

use App\Cart\Application\Dto\CartResponseDto;
use App\Cart\Domain\Model\Cart;
use App\Cart\Domain\Model\Money;
use App\Cart\Domain\Model\Product;
use App\Cart\Domain\Model\Quantity;
use App\Cart\Domain\Repository\CartRepository;
use App\Shared\Domain\Event\EventBus;

class AddProductToCartHandler
{
    /**
     * Creates the add product to cart command handler.
     *
     * @param CartRepository $repository
     * @param EventBus       $eventBus
     */
    public function __construct(
        private readonly CartRepository $repository,
        private readonly EventBus $eventBus
    ) {}

    /**
     * Handles the add product to cart command.
     *
     * @param AddProductToCartCommand $command
     *
     * @return CartResponseDto
     *
     * @throws \InvalidArgumentException
     */
    public function __invoke(AddProductToCartCommand $command): CartResponseDto
    {
        $cart = $this->repository->find($command->cartId) ?? new Cart($command->cartId);

        $product  = new Product($command->product);
        $quantity = new Quantity($command->quantity);
        $money    = new Money($command->unitPriceInCents, $command->currency);

        $cart->addProduct($product, $quantity, $money);

        $this->repository->save($cart);

        foreach ($cart->pullDomainEvents() as $event) {
            $this->eventBus->recordEvent($event);
        }

        $this->eventBus->dispatchEvents();

        return CartResponseDto::fromCart($cart);
    }
}
