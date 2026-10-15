<?php
namespace App\Cart\Infrastructure\Persistence\Doctrine;

use App\Cart\Domain\Model\Cart;
use App\Cart\Domain\Repository\CartRepository;
use Doctrine\ORM\EntityManagerInterface;

class DoctrineCartRepository implements CartRepository
{
    public function __construct(private EntityManagerInterface $em)
    {}

    public function save(Cart $cart): void
    {
        $cartEntity = $this->em->find(CartEntity::class, $cart->id());

        if (! $cartEntity) {
            $cartEntity = new CartEntity($cart->id());
            $this->em->persist($cartEntity);
        }

        // Index existing items by product identifier
        $existingItems = [];

        foreach ($cartEntity->getItems() as $itemEntity) {
            $existingItems[$itemEntity->getProductId()] = $itemEntity;
        }

        // Synchronize domain items with persistence entities
        foreach ($cart->items() as $item) {
            $productId = $item->product()->value();

            if (isset($existingItems[$productId])) {
                $existingItems[$productId]->updateQuantity(
                    $item->quantity()->value()
                );

                unset($existingItems[$productId]);

                continue;
            }

            $cartEntity->addItem(
                new CartItemEntity(
                    $cartEntity,
                    $productId,
                    $item->quantity()->value(),
                    $item->unitPrice()->amount(),
                    $item->unitPrice()->currency()->code()
                )
            );
        }

        // Remove items no longer present in the domain cart
        foreach ($existingItems as $itemEntity) {
            $cartEntity->removeItem($itemEntity);
        }

        $cartEntity->setUpdatedAt(new \DateTimeImmutable());
        $this->em->flush();
    }

    /**
     * Finds a cart by its identifier.
     *
     * @param string $id
     *
     * @return Cart|null
     */
    public function find(string $id): ?Cart
    {
        $entity = $this->em->find(CartEntity::class, $id);

        return $entity ? $this->toDomain($entity) : null;
    }

    public function delete(string $id): void
    {
        $cartEntity = $this->em->find(CartEntity::class, $id);
        if ($cartEntity) {
            $this->em->remove($cartEntity);
            $this->em->flush();
        }
    }

    /**
     * Retrieves all carts.
     *
     * @return Cart[]
     */
    public function all(): array
    {
        $entities = $this->em->getRepository(CartEntity::class)->findAll();

        return array_map(
            fn(CartEntity $entity) => $this->toDomain($entity),
            $entities
        );
    }

    /**
     * Reconstructs a domain cart from its persistence entity.
     *
     * @param CartEntity $entity
     *
     * @return Cart
     */
    private function toDomain(CartEntity $entity): Cart
    {
        $items = [];

        foreach ($entity->getItems() as $itemEntity) {
            $items[] = [
                'product'   => $itemEntity->getProductId(),
                'quantity'  => $itemEntity->getQuantity(),
                'unitPrice' => [
                    'amountInCents' => $itemEntity->getUnitPrice(),
                    'currency'      => $itemEntity->getCurrency(),
                ],
            ];
        }

        return Cart::fromPrimitives($entity->getId(), $items);
    }
}
