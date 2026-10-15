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

class RemoveProductFromCartHandlerIntegrationTest extends TestCase
{
    public function test_it_removes_product_using_handler(): void
    {
        echo "🗑️  Running integration test: RemoveProductFromCartHandler\n";

        $repo     = new InMemoryCartRepository();
        $eventBus = new EventBus($this->createMock(EventDispatcherInterface::class));

        $addHandler    = new AddProductToCartHandler($repo, $eventBus);
        $removeHandler = new RemoveProductFromCartHandler($repo, $eventBus);

        $cartId   = 'test-cart-4';
        $product1 = '00000000-0000-0000-0000-000000000005';
        $product2 = '00000000-0000-0000-0000-000000000006';
        $currency = new Currency('EUR');

        $addHandler(new AddProductToCartCommand($cartId, $product1, 1, 1000, $currency));
        $addHandler(new AddProductToCartCommand($cartId, $product2, 1, 1500, $currency));

        $removeHandler(new RemoveProductFromCartCommand($cartId, $product1));

        $cart = $repo->find($cartId);

        $this->assertNotNull($cart);
        $this->assertCount(1, $cart->items());
        $this->assertEquals($product2, $cart->items()[0]->product()->value());
    }
}
