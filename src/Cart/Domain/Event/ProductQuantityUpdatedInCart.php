<?php
namespace App\Cart\Domain\Event;

use App\Cart\Domain\Model\Product;
use App\Cart\Domain\Model\Quantity;
use App\Shared\Domain\Event\DomainEvent;

final class ProductQuantityUpdatedInCart implements DomainEvent
{
    /**
     * Creates a product quantity updated in cart domain event.
     *
     * @param string   $cartId
     * @param Product  $product
     * @param Quantity $quantity
     */
    public function __construct(
        private readonly string $cartId,
        private readonly Product $product,
        private readonly Quantity $quantity
    ) {}

    /**
     * Returns the event name.
     *
     * @return string
     */
    public function getName(): string
    {
        return 'ProductQuantityUpdatedInCart';
    }

    /**
     * Returns the event description.
     *
     * @return string
     */
    public function getDescription(): string
    {
        return 'Product quantity updated in cart';
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
            'quantity'   => $this->quantity->value(),
        ];
    }
}
