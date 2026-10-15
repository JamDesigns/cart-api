<?php
namespace App\Tests\Cart\Infrastructure\Persistence\Doctrine;

use App\Cart\Application\Command\AddProductToCart\AddProductToCartCommand;
use App\Cart\Application\Command\AddProductToCart\AddProductToCartHandler;
use App\Cart\Domain\Model\Cart;
use App\Cart\Domain\Model\Currency;
use App\Cart\Domain\Model\Money;
use App\Cart\Domain\Model\Product;
use App\Cart\Domain\Model\Quantity;
use App\Cart\Infrastructure\Persistence\Doctrine\DoctrineCartRepository;
use App\Shared\Domain\Event\EventBus;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Uid\Uuid;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class DoctrineCartRepositoryTest extends KernelTestCase
{
    private DoctrineCartRepository $repository;
    private EntityManagerInterface $em;
    private AddProductToCartHandler $addHandler;

    protected function setUp(): void
    {
        echo "🧪 Running DB persistence test: DoctrineCartRepository\n";

        self::bootKernel();
        $this->em = static::getContainer()->get(EntityManagerInterface::class);

        $this->em->getConnection()->beginTransaction();

        $this->repository = new DoctrineCartRepository($this->em);

        $eventBus = new EventBus(
            $this->createMock(EventDispatcherInterface::class)
        );

        $this->addHandler = new AddProductToCartHandler($this->repository, $eventBus);
    }

    public function testCartIsPersistedAndRestored(): void
    {
        $cartId   = Uuid::v4()->toRfc4122();
        $product  = new Product(Uuid::v4()->toRfc4122());
        $quantity = new Quantity(2);
        $money    = new Money(1200, new Currency('EUR'));

        ($this->addHandler)(new AddProductToCartCommand(
            $cartId,
            $product->value(),
            $quantity->value(),
            $money->amount(),
            $money->currency()
        ));

        $restored = $this->repository->find($cartId);

        $this->assertNotNull($restored, 'Cart should be found after being saved.');
        $this->assertSame($cartId, $restored->id());
        $this->assertCount(1, $restored->items());

        $item = $restored->items()[0];
        $this->assertSame($product->toPrimitives(), $item->product()->toPrimitives());
        $this->assertSame($quantity->toPrimitives(), $item->quantity()->toPrimitives());
        $this->assertSame($money->amount(), $item->unitPrice()->amount());
        $this->assertSame($money->currency()->code(), $item->unitPrice()->currency()->code());
    }

    public function testFindReturnsCartWhenItExists(): void
    {
        $cartId  = Uuid::v4()->toRfc4122();
        $product = new Product(Uuid::v4()->toRfc4122());

        ($this->addHandler)(new AddProductToCartCommand(
            $cartId,
            $product->value(),
            1,
            500,
            new Currency('EUR')
        ));

        $result = $this->repository->find($cartId);

        $this->assertInstanceOf(Cart::class, $result);
        $this->assertSame($cartId, $result->id());
    }

    public function testFindReturnsNullWhenCartDoesNotExist(): void
    {
        $cartId = Uuid::v4()->toRfc4122();
        $result = $this->repository->find($cartId);

        $this->assertNull($result, 'Expected null when cart is not found.');
    }

    public function testCartIsDeleted(): void
    {
        $cartId  = Uuid::v4()->toRfc4122();
        $product = new Product(Uuid::v4()->toRfc4122());

        ($this->addHandler)(new AddProductToCartCommand(
            $cartId,
            $product->value(),
            1,
            500,
            new Currency('EUR')
        ));

        $this->assertNotNull($this->repository->find($cartId));

        $this->repository->delete($cartId);

        $this->assertNull($this->repository->find($cartId), 'Cart should be null after deletion.');
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