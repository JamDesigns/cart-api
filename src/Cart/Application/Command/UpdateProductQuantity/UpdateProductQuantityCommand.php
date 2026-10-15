<?php
namespace App\Cart\Application\Command\UpdateProductQuantity;

class UpdateProductQuantityCommand
{
    /**
     * Creates an update product quantity command.
     *
     * @param string $cartId
     * @param string $product
     * @param int    $newQuantity
     */
    public function __construct(
        public readonly string $cartId,
        public readonly string $product,
        public readonly int $newQuantity
    ) {}
}
