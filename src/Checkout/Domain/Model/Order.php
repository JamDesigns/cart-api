<?php
namespace App\Checkout\Domain\Model;

use App\Cart\Domain\Model\Cart;
use App\Checkout\Domain\Event\OrderCreated;
use App\Shared\Domain\Event\DomainEvent;

class Order
{
    /** @var OrderItem[] */
    private array $items = [];

    /** @var DomainEvent[] */
    private array $domainEvents = [];

    public function __construct(
        private readonly string $id,
        private readonly string $currency,
        private readonly int $totalAmount
    ) {}

    /**
     * Creates an order from a cart.
     *
     * @param string $orderId
     * @param Cart   $cart
     *
     * @return self
     */
    public static function fromCart(string $orderId, Cart $cart): self
    {
        $currency = $cart->total()->currency()->code();
        $total    = $cart->total()->amount();

        $order = new self($orderId, $currency, $total);

        foreach ($cart->items() as $item) {
            $order->items[] = new OrderItem(
                $item->product()->value(),
                $item->quantity()->value(),
                $item->unitPrice()->amount()
            );
        }

        $order->domainEvents[] = new OrderCreated(
            $order->id(),
            $cart->id(),
            $order->total(),
            $order->currency()
        );

        return $order;
    }

    /**
     * Reconstructs an order from primitive values.
     *
     * @param string $id
     * @param string $currency
     * @param int    $totalAmount
     * @param array  $items
     *
     * @return self
     */
    public static function fromPrimitives(
        string $id,
        string $currency,
        int $totalAmount,
        array $items
    ): self {
        $order = new self($id, $currency, $totalAmount);

        foreach ($items as $item) {
            $order->items[] = new OrderItem(
                $item['product'],
                $item['quantity'],
                $item['unitPrice']
            );
        }

        return $order;
    }

    public function id(): string
    {
        return $this->id;
    }

    public function total(): int
    {
        return $this->totalAmount;
    }

    public function currency(): string
    {
        return $this->currency;
    }

    /**
     * @return OrderItem[]
     */
    public function items(): array
    {
        return $this->items;
    }

    /**
     * Returns and clears the recorded domain events.
     *
     * @return DomainEvent[]
     */
    public function pullDomainEvents(): array
    {
        $events             = $this->domainEvents;
        $this->domainEvents = [];

        return $events;
    }
}
