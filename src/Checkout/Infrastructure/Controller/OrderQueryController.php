<?php
namespace App\Checkout\Infrastructure\Controller;

use App\Checkout\Application\Query\GetOrders\GetOrdersHandler;
use App\Checkout\Application\Query\GetOrders\GetOrdersQuery;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

class OrderQueryController extends AbstractController
{
    public function __construct(
        private readonly GetOrdersHandler $handler
    ) {}

    #[Route('/orders', name: 'order_list', methods: ['GET'])]
    public function __invoke(): JsonResponse
    {
        $orders = ($this->handler)(new GetOrdersQuery());

        return $this->json(
            array_map(
                static fn($order) => $order->toArray(),
                $orders
            )
        );
    }
}
