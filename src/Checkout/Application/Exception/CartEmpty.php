<?php
namespace App\Checkout\Application\Exception;

final class CartEmpty extends \RuntimeException
{
    /**
     * Creates an exception for an empty cart during checkout.
     *
     * @param string $cartId
     */
    public function __construct(string $cartId)
    {
        parent::__construct(
            sprintf('Cart is empty during checkout: %s', $cartId)
        );
    }
}
