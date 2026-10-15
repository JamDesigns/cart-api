<?php
namespace App\Checkout\Application\Command\CheckoutCart;

use App\Checkout\Domain\Model\Order;
use App\Checkout\Domain\Model\OrderItem;

final class CheckoutCartResponseDto
{
    /**
     * Creates a checkout cart response DTO.
     *
     * @param string                        $id
     * @param int                           $total
     * @param string                        $currency
     * @param CheckoutCartItemResponseDto[] $items
     */
    public function __construct(
        public readonly string $id,
        public readonly int $total,
        public readonly string $currency,
        public readonly array $items
    ) {}

    /**
     * Creates the response DTO from an order.
     *
     * @param Order $order
     *
     * @return self
     */
    public static function fromOrder(Order $order): self
    {
        return new self(
            $order->id(),
            $order->total(),
            $order->currency(),
            array_map(
                static fn(OrderItem $item) => CheckoutCartItemResponseDto::fromOrderItem($item),
                $order->items()
            )
        );
    }

    /**
     * Returns the checkout response as primitives.
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
                static fn(CheckoutCartItemResponseDto $item) => $item->toArray(),
                $this->items
            ),
        ];
    }
}