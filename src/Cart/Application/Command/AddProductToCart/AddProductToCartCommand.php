<?php
namespace App\Cart\Application\Command\AddProductToCart;

use App\Cart\Domain\Model\Currency;

class AddProductToCartCommand
{
    /**
     * Creates an add product to cart command.
     *
     * @param string   $cartId
     * @param string   $product
     * @param int      $quantity
     * @param int      $unitPriceInCents
     * @param Currency $currency
     */
    public function __construct(
        public readonly string $cartId,
        public readonly string $product,
        public readonly int $quantity,
        public readonly int $unitPriceInCents,
        public readonly Currency $currency
    ) {}
}
