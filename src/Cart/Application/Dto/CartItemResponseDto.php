<?php
namespace App\Cart\Application\Dto;

use App\Cart\Domain\Model\CartItem;

final class CartItemResponseDto
{
    /**
     * Creates a cart item response DTO.
     *
     * @param string $productId
     * @param int    $quantity
     * @param int    $unitAmount
     * @param int    $totalAmount
     */
    public function __construct(
        public readonly string $productId,
        public readonly int $quantity,
        public readonly int $unitAmount,
        public readonly int $totalAmount
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
            'productId'   => $this->productId,
            'quantity'    => $this->quantity,
            'unitAmount'  => $this->unitAmount,
            'totalAmount' => $this->totalAmount,
        ];
    }
}
