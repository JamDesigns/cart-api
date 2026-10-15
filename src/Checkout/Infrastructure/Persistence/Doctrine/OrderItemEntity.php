<?php
namespace App\Checkout\Infrastructure\Persistence\Doctrine;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'order_items')]
class OrderItemEntity
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private int $id;

    #[ORM\ManyToOne(targetEntity: OrderEntity::class, inversedBy: 'items')]
    #[ORM\JoinColumn(nullable: false)]
    private OrderEntity $order;

    #[ORM\Column(name: 'product_id', type: 'string', length: 36)]
    private string $product;

    #[ORM\Column(type: 'integer')]
    private int $quantity;

    #[ORM\Column(type: 'integer')]
    private int $unitPrice;

    public function __construct(string $product, int $quantity, int $unitPrice)
    {
        $this->product   = $product;
        $this->quantity  = $quantity;
        $this->unitPrice = $unitPrice;
    }

    public function setOrder(OrderEntity $order): void
    {
        $this->order = $order;
    }

    public function getProduct(): string
    {
        return $this->product;
    }

    public function getQuantity(): int
    {
        return $this->quantity;
    }

    public function getUnitPrice(): int
    {
        return $this->unitPrice;
    }
}
