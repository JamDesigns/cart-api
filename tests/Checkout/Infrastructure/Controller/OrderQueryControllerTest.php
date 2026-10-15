<?php
namespace App\Tests\Checkout\Infrastructure\Controller;

use App\Checkout\Infrastructure\Persistence\Doctrine\OrderEntity;
use App\Checkout\Infrastructure\Persistence\Doctrine\OrderItemEntity;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Uid\Uuid;

class OrderQueryControllerTest extends WebTestCase
{
    private EntityManagerInterface $em;
    private KernelBrowser $client;
    private array $orderIds = [];

    /**
     * Creates two test orders using the existing database schema.
     */
    protected function setUp(): void
    {
        echo "🧪 Running functional test: GET /orders (list all orders)\n";

        $this->client = static::createClient();
        $this->em     = static::getContainer()->get(EntityManagerInterface::class);

        $order1Id = Uuid::v4()->toRfc4122();
        $order2Id = Uuid::v4()->toRfc4122();

        $this->orderIds = [$order1Id, $order2Id];

        $order1 = new OrderEntity($order1Id, 3000, 'EUR');
        $order1->addItem(new OrderItemEntity(Uuid::v4()->toRfc4122(), 1, 3000));

        $order2 = new OrderEntity($order2Id, 5000, 'EUR');
        $order2->addItem(new OrderItemEntity(Uuid::v4()->toRfc4122(), 2, 2500));

        $this->em->persist($order1);
        $this->em->persist($order2);
        $this->em->flush();
    }

    /**
     * Verifies that the orders endpoint returns both test orders.
     */
    public function test_it_returns_all_orders(): void
    {
        $this->client->request('GET', '/orders');

        $this->assertResponseIsSuccessful();
        $this->assertJson($this->client->getResponse()->getContent());

        $data = json_decode($this->client->getResponse()->getContent(), true);

        $returnedOrderIds = array_column($data, 'id');

        foreach ($this->orderIds as $orderId) {
            $this->assertContains($orderId, $returnedOrderIds);
        }
    }

    /**
     * Removes only the orders and order items created by this test.
     */
    protected function tearDown(): void
    {
        try {
            $connection = $this->em->getConnection();

            foreach ($this->orderIds as $orderId) {
                $connection->executeStatement(
                    'DELETE FROM order_items WHERE order_id = :orderId',
                    ['orderId' => $orderId]
                );

                $connection->executeStatement(
                    'DELETE FROM orders WHERE id = :orderId',
                    ['orderId' => $orderId]
                );
            }
        } finally {
            parent::tearDown();
        }
    }
}
