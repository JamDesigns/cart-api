<?php
namespace App\Cart\Application\Query\GetCartById;

final class GetCartByIdQuery
{
    /**
     * Creates a get cart by id query.
     *
     * @param string $cartId
     */
    public function __construct(
        public readonly string $cartId
    ) {}
}
