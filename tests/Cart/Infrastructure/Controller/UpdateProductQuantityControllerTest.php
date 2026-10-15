<?php
    namespace App\Tests\Cart\Infrastructure\Controller;

    use App\Cart\Application\Command\AddProductToCart\AddProductToCartCommand;
    use App\Cart\Application\Command\AddProductToCart\AddProductToCartHandler;
    use App\Cart\Domain\Model\Currency;
    use App\Cart\Infrastructure\Persistence\Doctrine\DoctrineCartRepository;
    use Doctrine\ORM\EntityManagerInterface;
    use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
    use Symfony\Component\Uid\Uuid;

    class UpdateProductQuantityControllerTest extends WebTestCase
    {
    private ?string $cartId = null;

    /**
     * Verifies that the API updates the quantity of an existing cart product.
     */
    public function test_it_updates_quantity_of_existing_product(): void
    {
        echo "🧪 Running functional test: PATCH /cart/{id}/product/{product}\n";

        $client    = static::createClient();
        $container = static::getContainer();

        $repo    = $container->get(DoctrineCartRepository::class);
        $handler = $container->get(AddProductToCartHandler::class);

        $cartId    = Uuid::v4()->toRfc4122();
        $productId = Uuid::v4()->toRfc4122();
        $currency  = new Currency('EUR');

        $this->cartId = $cartId;

        // Preload cart using handler
        $handler(new AddProductToCartCommand(
            $cartId,
            $productId,
            1,
            1000,
            $currency
        ));

        $payload = ['quantity' => 5];

        $client->request(
            'PATCH',
            "/cart/{$cartId}/product/{$productId}",
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($payload)
        );

        $this->assertResponseIsSuccessful();
        $this->assertJson($client->getResponse()->getContent());

        $response = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals('Quantity updated', $response['message']);

        // Assert quantity updated
        $updatedCart = $repo->find($cartId);
        $this->assertEquals(5, $updatedCart->items()[0]->quantity()->value());
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
