<?php
namespace App\Tests\Cart\Application\Command;

use App\Cart\Application\Command\AddProductToCart\AddProductToCartCommand;
use App\Cart\Application\Command\AddProductToCart\AddProductToCartHandler;
use App\Cart\Application\Command\RemoveProductFromCart\RemoveProductFromCartCommand;
use App\Cart\Application\Command\RemoveProductFromCart\RemoveProductFromCartHandler;
use App\Cart\Domain\Model\Currency;
use App\Cart\Infrastructure\Persistence\InMemoryCartRepository;
use App\Shared\Domain\Event\EventBus;
use PHPUnit\Framework\TestCase;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class AddProductToCartHandlerIntegrationTest extends TestCase
{
    public function test_adds_product_to_cart_using_in_memory_repository(): void
    {
        echo "🧪 Running integration test: AddProductToCartHandler\n";

        $repo     = new InMemoryCartRepository();
        $eventBus = new EventBus($this->createStub(EventDispatcherInterface::class));
        $handler  = new AddProductToCartHandler($repo, $eventBus);

        $cartId    = 'test-cart-1';
        $product   = '00000000-0000-0000-0000-000000000001';
        $quantity  = 3;
        $unitPrice = 1200; // €12.00
        $currency  = new Currency('EUR');

        $command = new AddProductToCartCommand($cartId, $product, $quantity, $unitPrice, $currency);

        $handler($command);

        $cart = $repo->find($cartId);

        $this->assertNotNull($cart);
        $this->assertCount(1, $cart->items());
        $this->assertEquals($quantity, $cart->items()[0]->quantity()->value());
        $this->assertEquals($unitPrice, $cart->items()[0]->unitPrice()->amount());
        $this->assertTrue($cart->items()[0]->unitPrice()->currency()->equals($currency));
    }

    public function test_updates_quantity_if_product_already_exists(): void
    {
        echo "➕ Running integration test: update quantity if product already exists in cart\n";

        $repo     = new InMemoryCartRepository();
        $eventBus = new EventBus($this->createStub(EventDispatcherInterface::class));
        $handler  = new AddProductToCartHandler($repo, $eventBus);

        $cartId   = 'test-cart-2';
        $product  = '00000000-0000-0000-0000-000000000002';
        $currency = new Currency('EUR');

        $handler(new AddProductToCartCommand($cartId, $product, 2, 1000, $currency));
        $handler(new AddProductToCartCommand($cartId, $product, 3, 1000, $currency));

        $cart = $repo->find($cartId);

        $this->assertNotNull($cart);
        $this->assertCount(1, $cart->items());
        $this->assertEquals(5, $cart->items()[0]->quantity()->value());
        $this->assertEquals(5000, $cart->items()[0]->totalPrice()->amount());
    }

    public function test_removes_product_from_cart(): void
    {
        echo "🗑️  Running integration test: remove product from cart (via handler)\n";

        $repo          = new InMemoryCartRepository();
        $eventBus      = new EventBus($this->createStub(EventDispatcherInterface::class));
        $addHandler    = new AddProductToCartHandler($repo, $eventBus);
        $removeHandler = new RemoveProductFromCartHandler($repo, $eventBus);

        $cartId   = 'test-cart-3';
        $product1 = '00000000-0000-0000-0000-000000000003';
        $product2 = '00000000-0000-0000-0000-000000000004';
        $currency = new Currency('EUR');

        $addHandler(new AddProductToCartCommand($cartId, $product1, 1, 1000, $currency));
        $addHandler(new AddProductToCartCommand($cartId, $product2, 1, 1500, $currency));

        $removeHandler(new RemoveProductFromCartCommand($cartId, $product1));

        $updatedCart = $repo->find($cartId);

        $this->assertNotNull($updatedCart);
        $this->assertCount(1, $updatedCart->items());
        $this->assertEquals($product2, $updatedCart->items()[0]->product()->value());
    }
}
