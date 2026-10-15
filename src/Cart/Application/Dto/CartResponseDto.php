<?php
namespace App\Cart\Application\Dto;

use App\Cart\Domain\Model\Cart;

final class CartResponseDto
{
    /**
     * Creates a cart response DTO.
     *
     * @param string                $cartId
     * @param CartItemResponseDto[] $items
     * @param int                   $totalAmount
     * @param string                $currency
     */
    public function __construct(
        public readonly string $cartId,
        public readonly array $items,
        public readonly int $totalAmount,
        public readonly string $currency
    ) {}

    /**
     * Creates the response DTO from a cart.
     *
     * @param Cart $cart
     *
     * @return self
     */
    public static function fromCart(Cart $cart): self
    {
        return new self(
            $cart->id(),
            array_map(
                static fn($item) => CartItemResponseDto::fromCartItem($item),
                $cart->items()
            ),
            $cart->total()->amount(),
            $cart->total()->currency()->code()
        );
    }

    /**
     * Returns the cart response as primitives.
     *
     * @return array
     */
    public function toArray(): array
    {
        return [
            'cartId'      => $this->cartId,
            'items'       => array_map(
                static fn(CartItemResponseDto $item) => $item->toArray(),
                $this->items
            ),
            'totalAmount' => $this->totalAmount,
            'currency'    => $this->currency,
        ];
    }
}
