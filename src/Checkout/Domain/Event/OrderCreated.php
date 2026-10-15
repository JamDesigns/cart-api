<?php
namespace App\Checkout\Domain\Event;

use App\Shared\Domain\Event\DomainEvent;

final class OrderCreated implements DomainEvent
{
    /**
     * Creates an order created domain event.
     *
     * @param string $orderId
     * @param string $cartId
     * @param int    $totalAmount
     * @param string $currency
     */
    public function __construct(
        private readonly string $orderId,
        private readonly string $cartId,
        private readonly int $totalAmount,
        private readonly string $currency
    ) {}

    /**
     * Returns the event name.
     *
     * @return string
     */
    public function getName(): string
    {
        return 'OrderCreated';
    }

    /**
     * Returns the event description.
     *
     * @return string
     */
    public function getDescription(): string
    {
        return 'Order successfully created from the cart';
    }

    /**
     * Returns the event payload.
     *
     * @return array
     */
    public function getPayload(): array
    {
        return [
            'order_id'     => $this->orderId,
            'cart_id'      => $this->cartId,
            'total_amount' => $this->totalAmount,
            'currency'     => $this->currency,
        ];
    }
}
