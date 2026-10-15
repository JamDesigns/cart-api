<?php
namespace App\Tests\Cart\Application\Command;

use App\Cart\Application\Command\AddProductToCart\AddProductToCartCommand;
use App\Cart\Application\Command\AddProductToCart\AddProductToCartHandler;
use App\Cart\Domain\Model\Currency;
use App\Cart\Infrastructure\Persistence\Doctrine\CartEntity;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Uid\Uuid;

class AddProductToCartHandlerTest extends KernelTestCase
{
    private EntityManagerInterface $em;
    private AddProductToCartHandler $handler;

    protected function setUp(): void
    {
        echo "🧪 Running application test: AddProductToCartHandler\n";

        self::bootKernel();

        $this->em      = static::getContainer()->get(EntityManagerInterface::class);
        $this->handler = static::getContainer()->get(AddProductToCartHandler::class);

        $this->em->getConnection()->beginTransaction();
    }

    public function test_it_adds_product_to_cart_and_persists_it(): void
    {
        $cartId   = Uuid::v4()->toRfc4122();
        $product  = Uuid::v4()->toRfc4122();
        $quantity = 2;
        $price    = 1500;
        $currency = new Currency('EUR');

        $command = new AddProductToCartCommand(
            $cartId,
            $product,
            $quantity,
            $price,
            $currency
        );

        ($this->handler)($command);

        $cartEntity = $this->em->getRepository(CartEntity::class)->findOneBy(['id' => $cartId]);

        $this->assertNotNull($cartEntity);
        $this->assertSame($cartId, $cartEntity->getId());
        $this->assertCount(1, $cartEntity->getItems());

        $item = $cartEntity->getItems()[0];
        $this->assertSame($product, $item->getProductId());
        $this->assertSame($quantity, $item->getQuantity());
        $this->assertSame($price, $item->getUnitPrice());
        $this->assertSame($currency->code(), $item->getCurrency());
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