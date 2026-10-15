<?php
    namespace App\Tests\Checkout\Infrastructure\Persistence\Doctrine;

    use App\Cart\Application\Command\AddProductToCart\AddProductToCartCommand;
    use App\Cart\Application\Command\AddProductToCart\AddProductToCartHandler;
    use App\Cart\Domain\Model\Currency;
    use App\Cart\Domain\Model\Product;
    use App\Cart\Infrastructure\Persistence\InMemoryCartRepository;
    use App\Checkout\Application\Command\CheckoutCart\CheckoutCartCommand;
    use App\Checkout\Application\Command\CheckoutCart\CheckoutCartHandler;
    use App\Checkout\Infrastructure\Persistence\Doctrine\DoctrineOrderRepository;
    use App\Checkout\Infrastructure\Persistence\Doctrine\OrderEntity;
    use App\Shared\Application\Transaction\TransactionManager;
    use App\Shared\Domain\Event\EventBus;
    use Doctrine\ORM\EntityManagerInterface;
    use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
    use Symfony\Component\Uid\Uuid;

    class DoctrineOrderRepositoryTest extends KernelTestCase
    {
    private EntityManagerInterface $em;

    /**
     * Initializes the test kernel and starts a database transaction.
     */
    protected function setUp(): void
    {
        self::bootKernel();

        $this->em = static::getContainer()->get(EntityManagerInterface::class);
        $this->em->getConnection()->beginTransaction();
    }

    /**
     * Verifies that checkout persists an order in the database.
     */
    public function test_it_persists_order_in_database(): void
    {
        echo "🧪 Running DB persistence test: DoctrineOrderRepository\n";

        $cartRepo  = new InMemoryCartRepository();
        $orderRepo = static::getContainer()->get(DoctrineOrderRepository::class);

        $cartId   = Uuid::v4()->toRfc4122();
        $product  = new Product(Uuid::v4()->toRfc4122());
        $currency = new Currency('EUR');

        $eventBus           = static::getContainer()->get(EventBus::class);
        $transactionManager = static::getContainer()->get(TransactionManager::class);

        $addHandler = new AddProductToCartHandler($cartRepo, $eventBus);

        ($addHandler)(new AddProductToCartCommand(
            $cartId,
            $product->value(),
            2,
            1000,
            $currency
        ));

        $handler = new CheckoutCartHandler(
            $cartRepo,
            $orderRepo,
            $eventBus,
            $transactionManager
        );

        $handler(new CheckoutCartCommand($cartId));

        $order = $this->em->getRepository(OrderEntity::class)->find($cartId);

        $this->assertNotNull($order);
        $this->assertEquals(2000, $order->total());
        $this->assertEquals('EUR', $order->currency());
        $this->assertCount(1, $order->items());
    }

    /**
     * Rolls back database changes made during the test.
     */
    protected function tearDown(): void
    {
        try {
            $connection = $this->em->getConnection();

            if ($connection->isTransactionActive()) {
                $connection->rollBack();
            }
        } finally {
            parent::tearDown();
        }
    }
}
