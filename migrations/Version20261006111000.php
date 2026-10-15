<?php

declare (strict_types = 1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20261006111000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Ensure that a product can only appear once in each cart';
    }

    public function up(Schema $schema): void
    {
        $this->addSql(
            'CREATE UNIQUE INDEX UNIQ_CART_ITEM_PRODUCT ON cart_items (cart_id, product_id)'
        );
    }

    public function down(Schema $schema): void
    {
        $this->addSql(
            'DROP INDEX UNIQ_CART_ITEM_PRODUCT'
        );
    }
}
