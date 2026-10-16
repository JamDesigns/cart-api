<?php
namespace App\Tests\Cart\Infrastructure\Controller;

use App\Cart\Application\Command\AddProductToCart\AddProductToCartCommand;
use App\Cart\Application\Command\AddProductToCart\AddProductToCartHandler;
use App\Cart\Domain\Model\Currency;
use App\Cart\Infrastructure\Persistence\Doctrine\DoctrineCartRepository;
use App\Shared\Domain\Event\EventBus;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Uid\Uuid;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class CartQueryControllerTest extends WebTestCase
{
    private DoctrineCartRepository $repository;
    private AddProductToCartHandler $addHandler;
    private EntityManagerInterface $em;
    private \Symfony\Bundle\FrameworkBundle\KernelBrowser $client;
    private array $cartIds = [];

    protected function setUp(): void
    {
        echo "🧪 Running functional test: GET /carts (list all carts)\n";

        $this->client = static::createClient();

        $this->em = static::getContainer()->get(EntityManagerInterface::class);

        $this->repository = new DoctrineCartRepository($this->em);

        $eventBus = new EventBus(
            $this->createStub(EventDispatcherInterface::class)
        );

        $this->addHandler = new AddProductToCartHandler($this->repository, $eventBus);
    }

    /**
     * Verifies that the carts endpoint returns the carts created by the test.
     */
    public function test_it_returns_all_carts(): void
    {
        $cart1Id = Uuid::v4()->toRfc4122();
        $cart2Id = Uuid::v4()->toRfc4122();

        $this->cartIds = [$cart1Id, $cart2Id];

        $currency = new Currency('EUR');

        ($this->addHandler)(new AddProductToCartCommand(
            $cart1Id,
            Uuid::v4()->toRfc4122(),
            2,
            1000,
            $currency
        ));

        ($this->addHandler)(new AddProductToCartCommand(
            $cart2Id,
            Uuid::v4()->toRfc4122(),
            1,
            2500,
            $currency
        ));

        $this->client->request('GET', '/carts');

        $this->assertResponseIsSuccessful();
        $this->assertJson($this->client->getResponse()->getContent());

        $data = json_decode($this->client->getResponse()->getContent(), true);

        $returnedCartIds = array_column($data, 'id');

        $this->assertContains($cart1Id, $returnedCartIds);
        $this->assertContains($cart2Id, $returnedCartIds);
    }

    /**
     * Removes only the carts and cart items created by this test.
     */
    protected function tearDown(): void
    {
        try {
            $connection = $this->em->getConnection();

            foreach ($this->cartIds as $cartId) {
                $connection->executeStatement(
                    'DELETE FROM cart_items WHERE cart_id = :cartId',
                    ['cartId' => $cartId]
                );

                $connection->executeStatement(
                    'DELETE FROM carts WHERE id = :cartId',
                    ['cartId' => $cartId]
                );
            }
        } finally {
            parent::tearDown();
        }
    }
}
