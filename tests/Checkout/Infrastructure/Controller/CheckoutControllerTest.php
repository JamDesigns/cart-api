<?php
    namespace App\Tests\Checkout\Infrastructure\Controller;

    use App\Cart\Application\Command\AddProductToCart\AddProductToCartCommand;
    use App\Cart\Application\Command\AddProductToCart\AddProductToCartHandler;
    use App\Cart\Domain\Model\Currency;
    use App\Cart\Domain\Model\Product;
    use Doctrine\ORM\EntityManagerInterface;
    use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
    use Symfony\Component\Uid\Uuid;

    class CheckoutControllerTest extends WebTestCase
    {
    private ?string $cartId = null;

    /**
     * Verifies that checkout creates an order through the API.
     */
    public function test_it_processes_checkout(): void
    {
        echo "🧪 Running functional test: POST /checkout/{cartId}\n";

        $client    = static::createClient();
        $container = static::getContainer();

        $addHandler = $container->get(AddProductToCartHandler::class);

        $cartId   = Uuid::v4()->toRfc4122();
        $product  = new Product(Uuid::v4()->toRfc4122());
        $currency = new Currency('EUR');

        $this->cartId = $cartId;

        ($addHandler)(new AddProductToCartCommand(
            $cartId,
            $product->value(),
            2,
            1200,
            $currency
        ));

        $client->request('POST', "/checkout/{$cartId}");

        $this->assertResponseStatusCodeSame(201);
        $this->assertJson($client->getResponse()->getContent());
    }

    /**
     * Removes only the cart and order records created by this test.
     */
    protected function tearDown(): void
    {
        try {
            if ($this->cartId !== null) {
                $em         = static::getContainer()->get(EntityManagerInterface::class);
                $connection = $em->getConnection();

                $connection->executeStatement(
                    'DELETE FROM order_items WHERE order_id = :cartId',
                    ['cartId' => $this->cartId]
                );

                $connection->executeStatement(
                    'DELETE FROM orders WHERE id = :cartId',
                    ['cartId' => $this->cartId]
                );

                $connection->executeStatement(
                    'DELETE FROM cart_items WHERE cart_id = :cartId',
                    ['cartId' => $this->cartId]
                );

                $connection->executeStatement(
                    'DELETE FROM carts WHERE id = :cartId',
                    ['cartId' => $this->cartId]
                );
            }
        } finally {
            parent::tearDown();
        }
    }
}
