<?php
namespace App\Cart\Domain\Exception;

use App\Cart\Domain\Model\Product;

final class ProductNotFoundInCart extends \DomainException
{
    /**
     * Creates an exception for a product that does not exist in the cart.
     *
     * @param Product $product
     */
    public function __construct(Product $product)
    {
        parent::__construct(
            sprintf('Product not found in cart: %s', $product->value())
        );
    }
}
