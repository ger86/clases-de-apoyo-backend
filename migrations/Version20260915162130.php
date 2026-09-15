<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * API tokens for the mobile app, plus the columns that record which payment provider
 * granted premium access (Stripe on the web, Apple in the app).
 */
final class Version20260915162130 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add api_token table and Apple / premium provider columns on user';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE api_token (id INT AUTO_INCREMENT NOT NULL, token_hash VARCHAR(64) NOT NULL, device_name VARCHAR(120) DEFAULT NULL, created_at DATETIME NOT NULL, last_used_at DATETIME NOT NULL, expires_at DATETIME NOT NULL, user_id INT NOT NULL, UNIQUE INDEX UNIQ_7BA2F5EBB3BC57DA (token_hash), INDEX IDX_7BA2F5EBA76ED395 (user_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE api_token ADD CONSTRAINT FK_7BA2F5EBA76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE user ADD premium_provider VARCHAR(20) DEFAULT NULL, ADD apple_original_transaction_id VARCHAR(64) DEFAULT NULL, ADD apple_app_account_token VARCHAR(36) DEFAULT NULL, ADD apple_subscription_status VARCHAR(40) DEFAULT NULL');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_8D93D649B5FD72D2 ON user (apple_original_transaction_id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_8D93D649DC58BC83 ON user (apple_app_account_token)');

        // Every premium account that exists today was paid through Stripe. Marking them keeps
        // an Apple purchase from shortening access that Stripe already granted.
        $this->addSql("UPDATE user SET premium_provider = 'stripe' WHERE premium_until IS NOT NULL");
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE api_token DROP FOREIGN KEY FK_7BA2F5EBA76ED395');
        $this->addSql('DROP TABLE api_token');
        $this->addSql('DROP INDEX UNIQ_8D93D649B5FD72D2 ON user');
        $this->addSql('DROP INDEX UNIQ_8D93D649DC58BC83 ON user');
        $this->addSql('ALTER TABLE user DROP premium_provider, DROP apple_original_transaction_id, DROP apple_app_account_token, DROP apple_subscription_status');
    }
}
