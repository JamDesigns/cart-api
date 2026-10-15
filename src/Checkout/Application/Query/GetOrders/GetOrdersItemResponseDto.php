<?php
namespace App\Checkout\Application\Query\GetOrders;

use App\Checkout\Domain\Model\OrderItem;

final class GetOrdersItemResponseDto
{
    /**
     * Creates a get orders item response DTO.
     *
     * @param string $product
     * @param int    $quantity
     * @param int    $unitPrice
     */
    public function __construct(
        public readonly string $product,
        public readonly int $quantity,
        public readonly int $unitPrice
    ) {}

    /**
     * Creates the response DTO from an order item.
     *
     * @param OrderItem $item
     *
     * @return self
     */
    public static function fromOrderItem(OrderItem $item): self
    {
        return new self(
            $item->product(),
            $item->quantity(),
            $item->unitPrice()
        );
    }

    /**
     * Returns the order item response as primitives.
     *
     * @return array
     */
    public function toArray(): array
    {
        return [
            'product'   => $this->product,
            'quantity'  => $this->quantity,
            'unitPrice' => $this->unitPrice,
        ];
    }
}
