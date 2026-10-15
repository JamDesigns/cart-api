<?php
namespace App\Tests\Checkout\Feature;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Uid\Uuid;

class CheckoutFlowFunctionalTest extends WebTestCase
{
    private KernelBrowser $client;
    private EntityManagerInterface $em;
    private ?string $cartId = null;

    protected function setUp(): void
    {
        echo "🧪 Running functional flow test: Checkout flow with DB\n";

        $this->client = static::createClient();
        $this->em     = static::getContainer()->get(EntityManagerInterface::class);
    }

    public function test_checkout_process_persists_order(): void
    {
        $cartId       = Uuid::v4()->toRfc4122();
        $this->cartId = $cartId;

        $product = Uuid::v4()->toRfc4122();

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
                'unitPrice' => 1200,
                'currency'  => 'EUR',
            ])
        );

        $this->assertResponseStatusCodeSame(201);

        // 🧾 Perform checkout
        $this->client->request('POST', "/checkout/{$cartId}");

        $this->assertResponseStatusCodeSame(201);
        $response = json_decode($this->client->getResponse()->getContent(), true);

        // ✅ Verify in DB
        $order = $this->em
            ->getRepository(\App\Checkout\Infrastructure\Persistence\Doctrine\OrderEntity::class)
            ->findOneBy(['id' => $cartId]);

        $this->assertNotNull($order);
        $this->assertEquals(2400, $order->total());
        $this->assertEquals('EUR', $order->currency());
        $this->assertCount(1, $order->items());

        // Verify that the original cart has been removed
        $cart = $this->em
            ->getRepository(\App\Cart\Infrastructure\Persistence\Doctrine\CartEntity::class)
            ->findOneBy(['id' => $cartId]);

        $this->assertNull($cart);
    }

    /**
     * Removes only the cart and order records created by this test.
     */
    protected function tearDown(): void
    {
        try {
            if ($this->cartId !== null) {
                $connection = $this->em->getConnection();

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
