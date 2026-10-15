<?php
namespace App\Cart\Application\Exception;

final class CartNotFound extends \RuntimeException
{
    /**
     * Creates an exception for a cart that does not exist.
     *
     * @param string $cartId
     */
    public function __construct(string $cartId)
    {
        parent::__construct(
            sprintf('Cart not found: %s', $cartId)
        );
    }
}
