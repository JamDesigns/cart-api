<?php
namespace App\Cart\Infrastructure\Controller;

use App\Cart\Application\Query\GetCarts\GetCartsHandler;
use App\Cart\Application\Query\GetCarts\GetCartsQuery;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class CartQueryController
{
    public function __construct(
        private readonly GetCartsHandler $handler
    ) {}

    /**
     * Retrieves all carts.
     *
     * @return Response
     */
    #[Route('/carts', name: 'get_all_carts', methods: ['GET'])]
    public function __invoke(): Response
    {
        $carts = ($this->handler)(new GetCartsQuery());

        return new JsonResponse(
            array_map(
                static fn($cart) => $cart->toArray(),
                $carts
            ),
            Response::HTTP_OK
        );
    }
}
