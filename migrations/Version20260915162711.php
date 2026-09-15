<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Audit trail for App Store Server Notifications. The unique notification uuid is what
 * makes Apple's retries safe to process more than once.
 */
final class Version20260915162711 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add apple_notification table';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE apple_notification (id INT AUTO_INCREMENT NOT NULL, notification_uuid VARCHAR(64) NOT NULL, notification_type VARCHAR(60) NOT NULL, subtype VARCHAR(60) DEFAULT NULL, original_transaction_id VARCHAR(64) DEFAULT NULL, environment VARCHAR(20) DEFAULT NULL, note LONGTEXT DEFAULT NULL, received_at DATETIME NOT NULL, UNIQUE INDEX UNIQ_319E933469431B9C (notification_uuid), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE apple_notification');
    }
}
