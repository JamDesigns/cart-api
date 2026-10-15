<?php
    namespace App\Tests\Cart\Infrastructure\Controller;

    use App\Cart\Application\Command\AddProductToCart\AddProductToCartCommand;
    use App\Cart\Application\Command\AddProductToCart\AddProductToCartHandler;
    use App\Cart\Domain\Model\Currency;
    use Doctrine\ORM\EntityManagerInterface;
    use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
    use Symfony\Component\Uid\Uuid;

    class GetCartControllerTest extends WebTestCase
    {
    private ?string $cartId = null;

    /**
     * Verifies that the API returns the details of an existing cart.
     */
    public function test_it_returns_cart_details(): void
    {
        echo "🧪 Running functional test: GET /cart/{id}\n";

        $client    = static::createClient();
        $container = static::getContainer();

        $handler = $container->get(AddProductToCartHandler::class);

        $cartId    = Uuid::v4()->toRfc4122();
        $productId = Uuid::v4()->toRfc4122();

        $this->cartId = $cartId;

        // Preload cart using handler
        $handler(new AddProductToCartCommand(
            $cartId,
            $productId,
            2,
            1500,
            new Currency('EUR')
        ));

        // Perform request
        $client->request('GET', "/cart/{$cartId}");

        $this->assertResponseIsSuccessful();
        $this->assertResponseFormatSame('json');

        $responseData = json_decode($client->getResponse()->getContent(), true);

        $this->assertEquals($cartId, $responseData['id']);
        $this->assertEquals(3000, $responseData['total']);
        $this->assertEquals('EUR', $responseData['currency']);
        $this->assertCount(1, $responseData['items']);
        $this->assertEquals($productId, $responseData['items'][0]['product']);
    }

    /**
     * Removes only the cart and cart items created by this test.
     */
    protected function tearDown(): void
    {
        try {
            if ($this->cartId !== null) {
                $em         = static::getContainer()->get(EntityManagerInterface::class);
                $connection = $em->getConnection();

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
