<?php
namespace App\Checkout\Application\Command\CheckoutCart;

class CheckoutCartCommand
{
    /**
     * Creates a checkout cart command.
     *
     * @param string $cartId
     */
    public function __construct(
        public readonly string $cartId
    ) {}
}