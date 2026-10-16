<?php
namespace App\Tests\Cart\Domain\Model;

use App\Cart\Application\Command\AddProductToCart\AddProductToCartCommand;
use App\Cart\Application\Command\AddProductToCart\AddProductToCartHandler;
use App\Cart\Application\Command\RemoveProductFromCart\RemoveProductFromCartCommand;
use App\Cart\Application\Command\RemoveProductFromCart\RemoveProductFromCartHandler;
use App\Cart\Application\Command\UpdateProductQuantity\UpdateProductQuantityCommand;
use App\Cart\Application\Command\UpdateProductQuantity\UpdateProductQuantityHandler;
use App\Cart\Domain\Model\Currency;
use App\Cart\Infrastructure\Persistence\InMemoryCartRepository;
use App\Shared\Domain\Event\EventBus;
use PHPUnit\Framework\TestCase;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class CartTest extends TestCase
{
    private InMemoryCartRepository $repo;
    private AddProductToCartHandler $addHandler;
    private UpdateProductQuantityHandler $updateHandler;
    private RemoveProductFromCartHandler $removeHandler;

    protected function setUp(): void
    {
        $this->repo = new InMemoryCartRepository();

        $eventBus = new EventBus(
            $this->createStub(EventDispatcherInterface::class)
        );

        $this->addHandler    = new AddProductToCartHandler($this->repo, $eventBus);
        $this->updateHandler = new UpdateProductQuantityHandler($this->repo, $eventBus);
        $this->removeHandler = new RemoveProductFromCartHandler($this->repo, $eventBus);
    }

    public function test_it_updates_quantity_if_product_already_in_cart(): void
    {
        echo "🔄 Running test: update existing product quantity in cart\n";

        $cartId    = 'cart-001';
        $productId = '123e4567-e89b-12d3-a456-426614174000';
        $currency  = new Currency('EUR');

        // Add first time
        ($this->addHandler)(new AddProductToCartCommand(
            $cartId,
            $productId,
            2,
            1000,
            $currency
        ));

        // Add same product again
        ($this->addHandler)(new AddProductToCartCommand(
            $cartId,
            $productId,
            3,
            1000,
            $currency
        ));

        $cart  = $this->repo->find($cartId);
        $items = $cart->items();

        $this->assertCount(1, $items, 'There should only be one product line');
        $this->assertEquals(5, $items[0]->quantity()->value(), 'The amount must have been added');
        $this->assertEquals(5000, $items[0]->totalPrice()->amount(), 'Correct total price');
    }

    public function test_it_throws_when_cart_does_not_exist(): void
    {
        echo "❌ Running test: update product in non-existent cart\n";

        $cartId    = 'cart-002';
        $productId = '999e9999-9999-9999-9999-999999999999';

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Cart not found');

        ($this->updateHandler)(new UpdateProductQuantityCommand(
            $cartId,
            $productId,
            3
        ));
    }

    public function test_it_throws_when_updating_quantity_of_nonexistent_product(): void
    {
        echo "❌ Running test: update quantity of missing product in existing cart\n";

        $cartId    = 'cart-002-bis';
        $productId = '999e9999-9999-9999-9999-999999999999';

        $this->repo->save(new \App\Cart\Domain\Model\Cart($cartId));

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Product not found in cart');

        ($this->updateHandler)(new UpdateProductQuantityCommand(
            $cartId,
            $productId,
            3
        ));
    }

    public function test_it_removes_a_product_from_cart(): void
    {
        echo "🗑️  Running test: remove product from cart\n";

        $cartId   = 'cart-003';
        $prod1    = 'aaaabbbb-cccc-dddd-eeee-000000000001';
        $prod2    = 'aaaabbbb-cccc-dddd-eeee-000000000002';
        $currency = new Currency('EUR');

        ($this->addHandler)(new AddProductToCartCommand($cartId, $prod1, 1, 1000, $currency));
        ($this->addHandler)(new AddProductToCartCommand($cartId, $prod2, 1, 1500, $currency));

        $cart = $this->repo->find($cartId);
        $this->assertCount(2, $cart->items());

        ($this->removeHandler)(new RemoveProductFromCartCommand($cartId, $prod1));

        $updated = $this->repo->find($cartId);
        $items   = $updated->items();
        $this->assertCount(1, $items);
        $this->assertEquals($prod2, $items[0]->product()->value());
    }

    public function test_it_calculates_total_price_correctly(): void
    {
        echo "🧾 Running test: calculate total price\n";

        $cartId   = 'cart-004';
        $currency = new Currency('EUR');
        $p1       = 'aaaabbbb-0000-0000-0000-000000000001';
        $p2       = 'aaaabbbb-0000-0000-0000-000000000002';

        ($this->addHandler)(new AddProductToCartCommand($cartId, $p1, 2, 1000, $currency));
        ($this->addHandler)(new AddProductToCartCommand($cartId, $p2, 3, 1500, $currency));

        $cart  = $this->repo->find($cartId);
        $total = $cart->total();

        $this->assertEquals(6500, $total->amount());
        $this->assertTrue($total->currency()->equals($currency));
    }

    public function test_it_checks_if_cart_has_product(): void
    {
        echo "🔍 Running test: check if product exists in cart\n";

        $cartId   = 'cart-005';
        $prod1    = '11111111-1111-1111-1111-111111111111';
        $prod2    = '22222222-2222-2222-2222-222222222222';
        $currency = new Currency('EUR');

        ($this->addHandler)(new AddProductToCartCommand($cartId, $prod1, 1, 1000, $currency));

        $cart  = $this->repo->find($cartId);
        $items = $cart->items();

        $this->assertCount(1, $items, 'Cart should have one item');
        $this->assertEquals($prod1, $items[0]->product()->value(), 'The cart must contain the added product');

        ($this->removeHandler)(new RemoveProductFromCartCommand($cartId, $prod1));
        $cartAfterRemoval = $this->repo->find($cartId);
        $this->assertCount(0, $cartAfterRemoval->items(), 'The cart must not contain any products after removal');
    }

    public function test_cart_has_id(): void
    {
        echo "🆔 Running test: cart id\n";
        $cart = new \App\Cart\Domain\Model\Cart('custom-cart-id');
        $this->assertEquals('custom-cart-id', $cart->id());
    }
}
