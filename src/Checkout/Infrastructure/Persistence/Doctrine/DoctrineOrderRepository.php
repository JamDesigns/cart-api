<?php
namespace App\Checkout\Infrastructure\Persistence\Doctrine;

use App\Checkout\Domain\Model\Order;
use App\Checkout\Domain\Repository\OrderRepository;
use Doctrine\ORM\EntityManagerInterface;

class DoctrineOrderRepository implements OrderRepository
{
    public function __construct(private readonly EntityManagerInterface $em)
    {}

    public function save(Order $order): void
    {
        $orderEntity = new OrderEntity(
            $order->id(),
            $order->total(),
            $order->currency()
        );

        foreach ($order->items() as $item) {
            $itemEntity = new OrderItemEntity(
                $item->product(),
                $item->quantity(),
                $item->unitPrice()
            );
            $orderEntity->addItem($itemEntity);
        }

        $this->em->persist($orderEntity);
        $this->em->flush();
    }

    /**
     * Finds an order by its identifier.
     *
     * @param string $orderId
     *
     * @return Order|null
     */
    public function find(string $orderId): ?Order
    {
        $entity = $this->em->find(OrderEntity::class, $orderId);

        return $entity ? $this->toDomain($entity) : null;
    }

    /**
     * Retrieves all orders.
     *
     * @return Order[]
     */
    public function findAll(): array
    {
        $entities = $this->em
            ->getRepository(OrderEntity::class)
            ->findAll();

        return array_map(
            fn(OrderEntity $entity) => $this->toDomain($entity),
            $entities
        );
    }

    /**
     * Reconstructs a domain order from its persistence entity.
     *
     * @param OrderEntity $entity
     *
     * @return Order
     */
    private function toDomain(OrderEntity $entity): Order
    {
        $items = [];

        foreach ($entity->items() as $itemEntity) {
            $items[] = [
                'product'   => $itemEntity->getProduct(),
                'quantity'  => $itemEntity->getQuantity(),
                'unitPrice' => $itemEntity->getUnitPrice(),
            ];
        }

        return Order::fromPrimitives(
            $entity->id(),
            $entity->currency(),
            $entity->total(),
            $items
        );
    }
}
