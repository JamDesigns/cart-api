<?php
namespace App\Cart\Infrastructure\Controller;

use App\Cart\Application\Command\AddProductToCart\AddProductToCartCommand;
use App\Cart\Application\Command\AddProductToCart\AddProductToCartHandler;
use App\Cart\Application\Command\ClearCart\ClearCartCommand;
use App\Cart\Application\Command\ClearCart\ClearCartHandler;
use App\Cart\Application\Command\RemoveProductFromCart\RemoveProductFromCartCommand;
use App\Cart\Application\Command\RemoveProductFromCart\RemoveProductFromCartHandler;
use App\Cart\Application\Command\UpdateProductQuantity\UpdateProductQuantityCommand;
use App\Cart\Application\Command\UpdateProductQuantity\UpdateProductQuantityHandler;
use App\Cart\Application\Exception\CartNotFound;
use App\Cart\Domain\Exception\ProductNotFoundInCart;
use App\Cart\Domain\Model\Currency;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class CartModificationController
{
    public function __construct(
        private readonly AddProductToCartHandler $addHandler,
        private readonly UpdateProductQuantityHandler $updateHandler,
        private readonly RemoveProductFromCartHandler $removeHandler,
        private readonly ClearCartHandler $clearHandler
    ) {}

    /**
     * Updates the quantity of a product in the cart.
     *
     * @param string  $id
     * @param string  $product
     * @param Request $request
     *
     * @return Response
     */
    #[Route('/cart/{id}/product/{product}', name: 'update_product_quantity', methods: ['PATCH'])]
    public function updateProductQuantity(string $id, string $product, Request $request): Response
    {
        $data = json_decode($request->getContent(), true);

        if (! isset($data['quantity'])) {
            return new JsonResponse(
                ['error' => 'Missing "quantity" field'],
                Response::HTTP_BAD_REQUEST
            );
        }

        if (! is_int($data['quantity']) || $data['quantity'] <= 0) {
            return new JsonResponse(
                ['error' => 'quantity must be a positive integer'],
                Response::HTTP_BAD_REQUEST
            );
        }

        try {
            $command = new UpdateProductQuantityCommand(
                $id,
                $product,
                $data['quantity']
            );

            $dto  = ($this->updateHandler)($command);
            $cart = $dto->toArray();

            return new JsonResponse([
                'message' => 'Quantity updated',
                'cart'    => [
                    'cart_id'      => $cart['cartId'],
                    'products'     => $cart['items'],
                    'total_amount' => $cart['totalAmount'],
                    'currency'     => $cart['currency'],
                ],
            ], Response::HTTP_OK);
        } catch (CartNotFound | ProductNotFoundInCart $e) {
            return new JsonResponse(
                ['error' => $e->getMessage()],
                Response::HTTP_NOT_FOUND
            );
        } catch (\InvalidArgumentException $e) {
            return new JsonResponse(
                ['error' => $e->getMessage()],
                Response::HTTP_BAD_REQUEST
            );
        }
    }

    /**
     * Removes a product from the cart.
     *
     * @param string $id
     * @param string $product
     *
     * @return Response
     */
    #[Route('/cart/{id}/product/{product}', name: 'remove_product_from_cart', methods: ['DELETE'])]
    public function removeProduct(string $id, string $product): Response
    {
        try {
            $command = new RemoveProductFromCartCommand($id, $product);

            $dto  = ($this->removeHandler)($command);
            $cart = $dto->toArray();

            return new JsonResponse([
                'message' => 'Product removed from cart',
                'cart'    => [
                    'cart_id'      => $cart['cartId'],
                    'products'     => $cart['items'],
                    'total_amount' => $cart['totalAmount'],
                    'currency'     => $cart['currency'],
                ],
            ], Response::HTTP_OK);
        } catch (CartNotFound | ProductNotFoundInCart $e) {
            return new JsonResponse(
                ['error' => $e->getMessage()],
                Response::HTTP_NOT_FOUND
            );
        } catch (\InvalidArgumentException $e) {
            return new JsonResponse(
                ['error' => $e->getMessage()],
                Response::HTTP_BAD_REQUEST
            );
        }
    }

    /**
     * Adds a product to the cart.
     *
     * @param string  $id
     * @param Request $request
     *
     * @return Response
     */
    #[Route('/cart/{id}/product', name: 'add_product_to_cart', methods: ['POST'])]
    public function addProduct(string $id, Request $request): Response
    {
        $data = json_decode($request->getContent(), true);

        if (! isset($data['product'], $data['quantity'], $data['unitPrice'], $data['currency'])) {
            return new JsonResponse(
                ['error' => 'Missing required fields'],
                Response::HTTP_BAD_REQUEST
            );
        }

        if (
            filter_var($data['unitPrice'], FILTER_VALIDATE_INT) === false
            || ! is_int($data['unitPrice'])
            || $data['unitPrice'] < 0
        ) {
            return new JsonResponse(
                ['error' => 'unitPrice must be a non-negative integer in cents'],
                Response::HTTP_BAD_REQUEST
            );
        }

        if (! is_int($data['quantity']) || $data['quantity'] <= 0) {
            return new JsonResponse(
                ['error' => 'quantity must be a positive integer'],
                Response::HTTP_BAD_REQUEST
            );
        }

        try {
            $command = new AddProductToCartCommand(
                $id,
                $data['product'],
                $data['quantity'],
                $data['unitPrice'],
                new Currency($data['currency'])
            );

            $dto  = ($this->addHandler)($command);
            $cart = $dto->toArray();

            return new JsonResponse([
                'message' => 'Product added to cart',
                'cart'    => [
                    'cart_id'      => $cart['cartId'],
                    'products'     => $cart['items'],
                    'total_amount' => $cart['totalAmount'],
                    'currency'     => $cart['currency'],
                ],
            ], Response::HTTP_CREATED);
        } catch (\InvalidArgumentException $e) {
            return new JsonResponse(
                ['error' => $e->getMessage()],
                Response::HTTP_BAD_REQUEST
            );
        }
    }

    /**
     * Clears all products from the cart.
     *
     * @param string $id
     *
     * @return Response
     */
    #[Route('/cart/{id}', name: 'clear_cart', methods: ['DELETE'])]
    public function clearCart(string $id): Response
    {
        try {
            $command = new ClearCartCommand($id);
            $dto     = ($this->clearHandler)($command);

            return new JsonResponse([
                'message' => 'Cart cleared',
                'cart'    => [
                    'cart_id'      => $dto->cartId,
                    'products'     => [],
                    'total_amount' => 0,
                ],
            ], Response::HTTP_OK);
        } catch (CartNotFound $e) {
            return new JsonResponse(
                ['error' => $e->getMessage()],
                Response::HTTP_NOT_FOUND
            );
        }
    }
}
