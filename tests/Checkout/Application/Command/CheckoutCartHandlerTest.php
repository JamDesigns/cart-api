<?php
namespace App\Tests\Checkout\Application\Command;

use App\Cart\Application\Command\AddProductToCart\AddProductToCartCommand;
use App\Cart\Application\Command\AddProductToCart\AddProductToCartHandler;
use App\Cart\Domain\Model\Currency;
use App\Cart\Domain\Model\Product;
use App\Cart\Infrastructure\Persistence\InMemoryCartRepository;
use App\Checkout\Application\Command\CheckoutCart\CheckoutCartCommand;
use App\Checkout\Application\Command\CheckoutCart\CheckoutCartHandler;
use App\Checkout\Infrastructure\Persistence\InMemoryOrderRepository;
use App\Shared\Application\Transaction\TransactionManager;
use App\Shared\Domain\Event\EventBus;
use PHPUnit\Framework\TestCase;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class CheckoutCartHandlerTest extends TestCase
{
    /**
     * Verifies that checkout creates an order and removes the cart.
     */
    public function test_it_generates_an_order_from_cart(): void
    {
        echo "🧪 Running unit test: CheckoutCartHandler\n";

        $cartId   = 'checkout-cart-123';
        $product  = new Product('44444444-4444-4444-4444-444444444444');
        $currency = new Currency('EUR');

        $eventDispatcher = $this->createMock(EventDispatcherInterface::class);
        $eventBus        = new EventBus($eventDispatcher);

        $transactionManager = $this->createMock(TransactionManager::class);
        $transactionManager
            ->expects($this->once())
            ->method('execute')
            ->willReturnCallback(
                static fn(callable $operation): mixed => $operation()
            );

        $cartRepo   = new InMemoryCartRepository();
        $addHandler = new AddProductToCartHandler($cartRepo, $eventBus);

        $addHandler(new AddProductToCartCommand(
            $cartId,
            $product->value(),
            2,
            1500,
            $currency
        ));

        $orderRepo = new InMemoryOrderRepository();

        $handler = new CheckoutCartHandler(
            $cartRepo,
            $orderRepo,
            $eventBus,
            $transactionManager
        );

        $handler(new CheckoutCartCommand($cartId));

        // Assert that the order has been saved
        $savedOrders = $orderRepo->findAll();

        $this->assertCount(1, $savedOrders);

        $order = $orderRepo->find($cartId);

        $this->assertNotNull($order);
        $this->assertEquals(3000, $order->total());
        $this->assertEquals('EUR', $order->currency());
        $this->assertCount(1, $order->items());
        $this->assertEquals($product->value(), $order->items()[0]->product());

        // Assert that checkout removes the original cart
        $this->assertNull($cartRepo->find($cartId));
    }
}
