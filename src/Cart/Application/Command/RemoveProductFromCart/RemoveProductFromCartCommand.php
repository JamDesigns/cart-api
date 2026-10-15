<?php
namespace App\Cart\Application\Command\RemoveProductFromCart;

class RemoveProductFromCartCommand
{
    /**
     * Creates a remove product from cart command.
     *
     * @param string $cartId
     * @param string $product
     */
    public function __construct(
        public readonly string $cartId,
        public readonly string $product
    ) {}
}
