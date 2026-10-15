<?php
namespace App\Cart\Infrastructure\Controller;

use App\Cart\Application\Exception\CartNotFound;
use App\Cart\Application\Query\GetCartById\GetCartByIdHandler;
use App\Cart\Application\Query\GetCartById\GetCartByIdQuery;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class CartController
{
    public function __construct(private readonly GetCartByIdHandler $handler)
    {}

    /**
     * Retrieves a cart by its identifier.
     *
     * @param string $id
     *
     * @return Response
     */
    #[Route('/cart/{id}', name: 'get_cart', methods: ['GET'])]
    public function __invoke(string $id): Response
    {
        try {
            $dto  = ($this->handler)(new GetCartByIdQuery($id));
            $cart = $dto->toArray();
        } catch (CartNotFound $e) {
            return new JsonResponse(
                ['error' => $e->getMessage()],
                Response::HTTP_NOT_FOUND
            );
        }

        return new JsonResponse([
            'id'       => $cart['cartId'],
            'items'    => array_map(
                static fn(array $item) => [
                    'product'   => $item['productId'],
                    'quantity'  => $item['quantity'],
                    'unitPrice' => $item['unitAmount'],
                    'currency'  => $cart['currency'],
                    'total'     => $item['totalAmount'],
                ],
                $cart['items']
            ),
            'total'    => $cart['totalAmount'],
            'currency' => $cart['currency'],
        ], Response::HTTP_OK);
    }
}
