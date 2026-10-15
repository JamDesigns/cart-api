<?php
namespace App\Cart\Application\Command\ClearCart;

final class ClearCartResponseDto
{
    /**
     * Creates a clear cart response DTO.
     *
     * @param string $cartId
     */
    public function __construct(
        public readonly string $cartId
    ) {}
}
