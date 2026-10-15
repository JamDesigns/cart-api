<?php

declare (strict_types = 1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250705124818 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql(
            'CREATE TABLE orders (
            id VARCHAR(36) NOT NULL,
            total INTEGER NOT NULL,
            currency VARCHAR(3) NOT NULL,
            PRIMARY KEY(id)
        )'
        );

        $this->addSql(
            'CREATE TABLE order_items (
            id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
            order_id VARCHAR(36) NOT NULL,
            product_id VARCHAR(36) NOT NULL,
            quantity INTEGER NOT NULL,
            unit_price INTEGER NOT NULL,
            CONSTRAINT FK_62809DB08D9F6D38
                FOREIGN KEY (order_id)
                REFERENCES orders (id)
                NOT DEFERRABLE INITIALLY IMMEDIATE
        )'
        );

        $this->addSql(
            'CREATE INDEX IDX_62809DB08D9F6D38 ON order_items (order_id)'
        );
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE order_items');
        $this->addSql('DROP TABLE orders');
    }
}
