<?php
namespace App\Tests\Cart\Infrastructure\Controller;

use App\Cart\Application\Command\AddProductToCart\AddProductToCartCommand;
use App\Cart\Application\Command\AddProductToCart\AddProductToCartHandler;
use App\Cart\Domain\Model\Currency;
use App\Cart\Infrastructure\Persistence\Doctrine\DoctrineCartRepository;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class ClearCartControllerTest extends WebTestCase
{
    public function test_it_clears_cart(): void
    {
        echo "🧪 Running functional test: DELETE /cart/{id}\n";

        $client    = static::createClient();
        $container = static::getContainer();

        /** @var AddProductToCartHandler $handler */
        $handler = $container->get(AddProductToCartHandler::class);

        $cartId    = 'test-cart-clear';
        $productId = 'aaaaaaaa-aaaa-aaaa-aaaa-aaaaaaaaaaaa';

        // Preload cart using application handler
        $handler(new AddProductToCartCommand(
            $cartId,
            $productId,
            2,
            1000,
            new Currency('EUR')
        ));

        /** @var DoctrineCartRepository $repo */
        $repo = $container->get(DoctrineCartRepository::class);

        $this->assertNotNull($repo->find($cartId), 'Cart should exist before deletion');

        // Send DELETE request
        $client->request('DELETE', "/cart/{$cartId}");

        $this->assertResponseIsSuccessful();
        $this->assertJson($client->getResponse()->getContent());

        $response = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals('Cart cleared', $response['message']);

        $this->assertNull($repo->find($cartId), 'Cart should be deleted');
    }
}
