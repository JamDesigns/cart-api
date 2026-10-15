<?php
    namespace App\Tests\Cart\Infrastructure\Controller;

    use Doctrine\ORM\EntityManagerInterface;
    use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
    use Symfony\Component\Uid\Uuid;

    class AddProductToCartControllerTest extends WebTestCase
    {
    private ?string $cartId = null;

    /**
     * Verifies that a product can be added to a cart through the API.
     */
    public function test_it_adds_product_to_cart(): void
    {
        echo "🧪 Running functional test: POST /cart/{id}/product\n";

        $client = static::createClient();

        $cartId       = Uuid::v4()->toRfc4122();
        $this->cartId = $cartId;

        $payload = [
            'product'   => Uuid::v4()->toRfc4122(),
            'quantity'  => 3,
            'unitPrice' => 2000, // 20 €
            'currency'  => 'EUR',
        ];

        $client->request(
            'POST',
            "/cart/{$cartId}/product",
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($payload)
        );

        $this->assertResponseStatusCodeSame(201);
        $this->assertJson($client->getResponse()->getContent());

        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals('Product added to cart', $data['message'] ?? null);
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
