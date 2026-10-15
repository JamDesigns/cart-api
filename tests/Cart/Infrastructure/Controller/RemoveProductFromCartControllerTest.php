<?php
    namespace App\Tests\Cart\Infrastructure\Controller;

    use App\Cart\Application\Command\AddProductToCart\AddProductToCartCommand;
    use App\Cart\Application\Command\AddProductToCart\AddProductToCartHandler;
    use App\Cart\Domain\Model\Currency;
    use App\Cart\Infrastructure\Persistence\Doctrine\DoctrineCartRepository;
    use Doctrine\ORM\EntityManagerInterface;
    use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
    use Symfony\Component\Uid\Uuid;

    class RemoveProductFromCartControllerTest extends WebTestCase
    {
    private ?string $cartId = null;

    /**
     * Verifies that a product can be removed from a cart through the API.
     */
    public function test_it_removes_product_from_cart(): void
    {
        echo "🧪 Running functional test: DELETE /cart/{id}/product/{product}\n";

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

        // Send DELETE request
        $client->request('DELETE', "/cart/{$cartId}/product/{$productId}");

        $this->assertResponseIsSuccessful();
        $this->assertJson($client->getResponse()->getContent());

        $response = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals('Product removed from cart', $response['message']);

        // Assert product was removed
        $updatedCart = $repo->find($cartId);
        $this->assertCount(0, $updatedCart->items());
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
