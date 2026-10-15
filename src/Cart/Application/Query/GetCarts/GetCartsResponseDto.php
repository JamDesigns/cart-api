<?php
namespace App\Cart\Application\Query\GetCarts;

use App\Cart\Domain\Model\Cart;

final class GetCartsResponseDto
{
    /**
     * Creates a get carts response DTO.
     *
     * @param string                    $id
     * @param int                       $total
     * @param string                    $currency
     * @param GetCartsItemResponseDto[] $items
     */
    public function __construct(
        public readonly string $id,
        public readonly int $total,
        public readonly string $currency,
        public readonly array $items
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
            $cart->total()->amount(),
            $cart->total()->currency()->code(),
            array_map(
                static fn($item) => GetCartsItemResponseDto::fromCartItem($item),
                $cart->items()
            )
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
            'id'       => $this->id,
            'total'    => $this->total,
            'currency' => $this->currency,
            'items'    => array_map(
                static fn(GetCartsItemResponseDto $item) => $item->toArray(),
                $this->items
            ),
        ];
    }
}
