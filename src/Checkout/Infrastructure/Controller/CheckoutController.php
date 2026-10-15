<?php
namespace App\Checkout\Infrastructure\Controller;

use App\Cart\Application\Exception\CartNotFound;
use App\Checkout\Application\Command\CheckoutCart\CheckoutCartCommand;
use App\Checkout\Application\Command\CheckoutCart\CheckoutCartHandler;
use App\Checkout\Application\Exception\CartEmpty;
use Doctrine\DBAL\Exception\LockWaitTimeoutException;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class CheckoutController
{
    public function __construct(private readonly CheckoutCartHandler $handler)
    {}

    #[Route('/checkout/{cartId}', name: 'checkout_cart', methods: ['POST'])]
    public function __invoke(string $cartId): Response
    {
        try {
            $response = ($this->handler)(new CheckoutCartCommand($cartId));

            return new JsonResponse(
                $response->toArray(),
                Response::HTTP_CREATED
            );
        } catch (CartNotFound $e) {
            return new JsonResponse(
                ['message' => 'Cart not found during checkout'],
                Response::HTTP_NOT_FOUND
            );
        } catch (CartEmpty $e) {
            return new JsonResponse(
                ['message' => 'Cart is empty. Please add items to proceed.'],
                Response::HTTP_BAD_REQUEST
            );
        } catch (UniqueConstraintViolationException $e) {
            return new JsonResponse(
                ['message' => 'Checkout conflict. The cart may have already been processed.'],
                Response::HTTP_CONFLICT
            );
        } catch (LockWaitTimeoutException $e) {
            return new JsonResponse(
                ['message' => 'Checkout is temporarily unavailable due to a database lock. Please retry.'],
                Response::HTTP_SERVICE_UNAVAILABLE,
                ['Retry-After' => '1']
            );
        }
    }
}
