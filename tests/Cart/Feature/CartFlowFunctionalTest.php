<?php
namespace App\Tests\Cart\Feature;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Uid\Uuid;

class CartFlowFunctionalTest extends WebTestCase
{
    private KernelBrowser $client;
    private EntityManagerInterface $em;
    private ?string $cartId = null;

    protected function setUp(): void
    {
        echo "🧪 Running functional flow test: Cart Add → Get → Delete\n";

        $this->client = static::createClient();
        $this->em     = static::getContainer()->get(EntityManagerInterface::class);
    }

    public function test_cart_flow_with_persistence(): void
    {
        $cartId       = Uuid::v4()->toRfc4122();
        $this->cartId = $cartId;
        $product      = Uuid::v4()->toRfc4122();

        // ➕ Add product to cart
        $this->client->request(
            'POST',
            "/cart/{$cartId}/product",
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                'product'   => $product,
                'quantity'  => 2,
                'unitPrice' => 1000,
                'currency'  => 'EUR',
            ])
        );

        $this->assertResponseStatusCodeSame(201);

        // 🔍 Get cart and assert product is there
        $this->client->request('GET', "/cart/{$cartId}");

        $this->assertResponseIsSuccessful();
        $responseData = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertEquals($cartId, $responseData['id']);
        $this->assertEquals(2000, $responseData['total']);
        $this->assertEquals('EUR', $responseData['currency']);
        $this->assertCount(1, $responseData['items']);
        $this->assertEquals($product, $responseData['items'][0]['product']);

        // 🗑️ Remove product
        $this->client->request('DELETE', "/cart/{$cartId}/product/{$product}");
        $this->assertResponseIsSuccessful();

        $response = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertEquals('Product removed from cart', $response['message']);

        // ✅ Confirm cart is now empty
        $this->client->request('GET', "/cart/{$cartId}");
        $data = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertCount(0, $data['items']);
        $this->assertEquals(0, $data['total']);
    }

    /**
     * Removes only the cart and cart items created by this test.
     */
    protected function tearDown(): void
    {
        try {
            if ($this->cartId !== null) {
                $connection = $this->em->getConnection();

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
