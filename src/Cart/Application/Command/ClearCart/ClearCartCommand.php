<?php
namespace App\Cart\Application\Command\ClearCart;

class ClearCartCommand
{
    /**
     * Creates a clear cart command.
     *
     * @param string $cartId
     */
    public function __construct(
        public readonly string $cartId
    ) {}
}
