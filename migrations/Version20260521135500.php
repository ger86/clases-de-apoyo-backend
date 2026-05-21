<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260521135500 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add paid product and product purchase tables';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE paid_product (id INT AUTO_INCREMENT NOT NULL, code VARCHAR(128) NOT NULL, title VARCHAR(256) NOT NULL, slug VARCHAR(256) NOT NULL, description LONGTEXT DEFAULT NULL, stripe_product_id VARCHAR(256) NOT NULL, stripe_price_id VARCHAR(256) NOT NULL, price_cents INT NOT NULL, currency VARCHAR(3) NOT NULL, files JSON NOT NULL, enabled TINYINT(1) NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, UNIQUE INDEX UNIQ_2098CBC877153098 (code), UNIQUE INDEX UNIQ_2098CBC8989D9B62 (slug), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE product_purchase (id INT AUTO_INCREMENT NOT NULL, product_id INT NOT NULL, user_id INT DEFAULT NULL, email VARCHAR(256) DEFAULT NULL, download_token VARCHAR(64) NOT NULL, stripe_checkout_session_id VARCHAR(256) DEFAULT NULL, stripe_payment_intent_id VARCHAR(256) DEFAULT NULL, stripe_customer_id VARCHAR(256) DEFAULT NULL, amount_total INT NOT NULL, currency VARCHAR(3) NOT NULL, status VARCHAR(32) NOT NULL, paid_at DATETIME DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, UNIQUE INDEX UNIQ_AAA7BBAC704D2D87 (download_token), UNIQUE INDEX UNIQ_AAA7BBAC5A18FBC7 (stripe_checkout_session_id), INDEX IDX_AAA7BBAC4584665A (product_id), INDEX IDX_AAA7BBACA76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE product_purchase ADD CONSTRAINT FK_AAA7BBAC4584665A FOREIGN KEY (product_id) REFERENCES paid_product (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE product_purchase ADD CONSTRAINT FK_AAA7BBACA76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE SET NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE product_purchase DROP FOREIGN KEY FK_AAA7BBAC4584665A');
        $this->addSql('ALTER TABLE product_purchase DROP FOREIGN KEY FK_AAA7BBACA76ED395');
        $this->addSql('DROP TABLE product_purchase');
        $this->addSql('DROP TABLE paid_product');
    }
}
