<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Records the free year given to the people who bought the app when it was paid,
 * so no account can take it twice.
 */
final class Version20260916101224 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add the legacy app access columns on user';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE user ADD legacy_app_access_claimed_at DATETIME DEFAULT NULL, ADD legacy_app_transaction_id VARCHAR(64) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE user DROP legacy_app_access_claimed_at, DROP legacy_app_transaction_id');
    }
}
