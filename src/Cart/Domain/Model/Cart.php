<?php
namespace App\Cart\Domain\Model;

use App\Cart\Domain\Event\ProductAddedToCart;
use App\Cart\Domain\Event\ProductQuantityUpdatedInCart;
use App\Cart\Domain\Event\ProductRemovedFromCart;
use App\Cart\Domain\Exception\ProductNotFoundInCart;
use App\Shared\Domain\Event\DomainEvent;

final class Cart
{
    /** @var CartItem[] */
    private array $items = [];

    /** @var DomainEvent[] */
    private array $domainEvents = [];

    public function __construct(private readonly string $id)
    {}

    public function id(): string
    {
        return $this->id;
    }

    /**
     * Returns the items (without mutating)
     *
     * @return CartItem[]
     */
    public function items(): array
    {
        return $this->items;
    }

    /**
     * Returns the total amount of the cart.
     */
    public function total(): Money
    {
        $total    = 0;
        $currency = null;

        foreach ($this->items as $item) {
            if ($currency === null) {
                $currency = $item->unitPrice()->currency();
            }
            $total += $item->totalPrice()->amount();
        }
        return new Money($total, $currency ?? new Currency('EUR'));
    }

    /**
     * Creates a Cart instance from primitive values.
     *
     * @param string $id
     * @param array $items
     *
     * @return self
     *
     * @throws \InvalidArgumentException When cart items use different currencies.
     */
    public static function fromPrimitives(string $id, array $items): self
    {
        $cart     = new self($id);
        $currency = null;

        foreach ($items as $itemData) {
            $item = CartItem::fromPrimitives($itemData);

            if ($currency !== null && ! $currency->equals($item->unitPrice()->currency())) {
                throw new \InvalidArgumentException(
                    'All products in a cart must use the same currency.'
                );
            }

            $currency ??= $item->unitPrice()->currency();
            $cart->items[] = $item;
        }

        return $cart;
    }

    /**
     * Converts the Cart instance to primitive values.
     *
     * @return array
     */
    public function toPrimitives(): array
    {
        return array_map(fn(CartItem $i) => $i->toPrimitives(), $this->items);
    }

    /**
     * Adds a product to the cart.
     *
     * If the product already exists, its quantity is increased while keeping
     * the unit price originally stored in the cart.
     *
     * @param Product  $product
     * @param Quantity $quantity
     * @param Money    $unitPrice
     *
     * @return void
     */
    public function addProduct(Product $product, Quantity $quantity, Money $unitPrice): void
    {
        foreach ($this->items as $item) {
            if ($item->product()->equals($product)) {
                $newQuantity = new Quantity(
                    $item->quantity()->value() + $quantity->value()
                );

                $item->updateQuantity($newQuantity);

                $this->recordDomainEvent(
                    new ProductQuantityUpdatedInCart($this->id, $product, $newQuantity)
                );

                return;
            }
        }

        foreach ($this->items as $item) {
            if (! $item->unitPrice()->isSameCurrency($unitPrice)) {
                throw new \InvalidArgumentException(
                    'All products in a cart must use the same currency.'
                );
            }
        }

        $this->items[] = new CartItem($product, $quantity, $unitPrice);

        $this->recordDomainEvent(
            new ProductAddedToCart($this->id, $product, $quantity)
        );
    }

    /**
     * Updates the quantity of a product in the cart.
     *
     * @param Product  $product
     * @param Quantity $quantity
     *
     * @return void
     *
     * @throws ProductNotFoundInCart
     */
    public function updateProductQuantity(Product $product, Quantity $quantity): void
    {
        foreach ($this->items as $item) {
            if ($item->product()->equals($product)) {
                $item->updateQuantity($quantity);

                $this->recordDomainEvent(
                    new ProductQuantityUpdatedInCart($this->id, $product, $quantity)
                );

                return;
            }
        }

        throw new ProductNotFoundInCart($product);
    }

    /**
     * Removes a product from the cart.
     *
     * @param Product $product
     *
     * @return void
     *
     * @throws ProductNotFoundInCart
     */
    public function removeProduct(Product $product): void
    {
        foreach ($this->items as $index => $item) {
            if ($item->product()->equals($product)) {
                unset($this->items[$index]);
                $this->items = array_values($this->items);

                $this->recordDomainEvent(
                    new ProductRemovedFromCart($this->id, $product)
                );

                return;
            }
        }

        throw new ProductNotFoundInCart($product);
    }

    /**
     * Records a domain event generated by the aggregate.
     *
     * @param DomainEvent $event
     *
     * @return void
     */
    private function recordDomainEvent(DomainEvent $event): void
    {
        $this->domainEvents[] = $event;
    }

    /**
     * Releases all domain events recorded by the aggregate.
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
