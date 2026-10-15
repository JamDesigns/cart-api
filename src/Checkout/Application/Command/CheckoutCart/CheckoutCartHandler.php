<?php
namespace App\Checkout\Application\Command\CheckoutCart;

use App\Cart\Application\Exception\CartNotFound;
use App\Cart\Domain\Repository\CartRepository;
use App\Checkout\Application\Command\CheckoutCart\CheckoutCartResponseDto;
use App\Checkout\Application\Exception\CartEmpty;
use App\Checkout\Domain\Model\Order;
use App\Checkout\Domain\Repository\OrderRepository;
use App\Shared\Application\Transaction\TransactionManager;
use App\Shared\Domain\Event\EventBus;

class CheckoutCartHandler
{
    /**
     * Creates the checkout cart command handler.
     *
     * @param CartRepository  $cartRepository
     * @param OrderRepository $orderRepository
     * @param EventBus        $eventBus
     * @param TransactionManager $transactionManager
     */
    public function __construct(
        private readonly CartRepository $cartRepository,
        private readonly OrderRepository $orderRepository,
        private readonly EventBus $eventBus,
        private readonly TransactionManager $transactionManager
    ) {}

    /**
     * Handles the checkout cart command.
     *
     * @param CheckoutCartCommand $command
     *
     * @return CheckoutCartResponseDto
     *
     * @throws CartNotFound
     * @throws CartEmpty
     */
    public function __invoke(CheckoutCartCommand $command): CheckoutCartResponseDto
    {
        $existingOrder = $this->orderRepository->find($command->cartId);

        if ($existingOrder !== null) {
            return CheckoutCartResponseDto::fromOrder($existingOrder);
        }

        $cart = $this->cartRepository->find($command->cartId);

        if (! $cart) {
            throw new CartNotFound($command->cartId);
        }

        if (count($cart->items()) === 0) {
            throw new CartEmpty($command->cartId);
        }

        $order = Order::fromCart($command->cartId, $cart);

        $this->transactionManager->execute(function () use ($order, $command): void {
            $this->orderRepository->save($order);
            $this->cartRepository->delete($command->cartId);
        });

        foreach ($order->pullDomainEvents() as $event) {
            $this->eventBus->recordEvent($event);
        }

        $this->eventBus->dispatchEvents();

        return CheckoutCartResponseDto::fromOrder($order);
    }
}
