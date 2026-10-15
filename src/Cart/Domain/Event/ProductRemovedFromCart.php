<?php
namespace App\Cart\Domain\Event;

use App\Cart\Domain\Model\Product;
use App\Shared\Domain\Event\DomainEvent;

final class ProductRemovedFromCart implements DomainEvent
{
    /**
     * Creates a product removed from cart domain event.
     *
     * @param string  $cartId
     * @param Product $product
     */
    public function __construct(
        private readonly string $cartId,
        private readonly Product $product
    ) {}

    /**
     * Returns the event name.
     *
     * @return string
     */
    public function getName(): string
    {
        return 'ProductRemovedFromCart';
    }

    /**
     * Returns the event description.
     *
     * @return string
     */
    public function getDescription(): string
    {
        return 'Product removed from cart';
    }

    /**
     * Returns the event payload.
     *
     * @return array
     */
    public function getPayload(): array
    {
        return [
            'cart_id'    => $this->cartId,
            'product_id' => $this->product->value(),
        ];
    }
}
