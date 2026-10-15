<?php
namespace App\Cart\Application\Query\GetCarts;

use App\Cart\Domain\Model\CartItem;

final class GetCartsItemResponseDto
{
    /**
     * Creates a get carts item response DTO.
     *
     * @param string $product
     * @param int    $quantity
     * @param int    $unitPrice
     * @param int    $total
     */
    public function __construct(
        public readonly string $product,
        public readonly int $quantity,
        public readonly int $unitPrice,
        public readonly int $total
    ) {}

    /**
     * Creates the response DTO from a cart item.
     *
     * @param CartItem $item
     *
     * @return self
     */
    public static function fromCartItem(CartItem $item): self
    {
        return new self(
            $item->product()->value(),
            $item->quantity()->value(),
            $item->unitPrice()->amount(),
            $item->totalPrice()->amount()
        );
    }

    /**
     * Returns the cart item response as primitives.
     *
     * @return array
     */
    public function toArray(): array
    {
        return [
            'product'   => $this->product,
            'quantity'  => $this->quantity,
            'unitPrice' => $this->unitPrice,
            'total'     => $this->total,
        ];
    }
}
