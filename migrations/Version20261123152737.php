<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * The free PAU exam pack: one row per email left in the signup form.
 */
final class Version20261123152737 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add lead_subscriber for the free PAU exam pack (email signup with double opt-in).';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE lead_subscriber (id INT AUTO_INCREMENT NOT NULL, email VARCHAR(180) NOT NULL, confirmation_token VARCHAR(64) NOT NULL, unsubscribe_token VARCHAR(64) NOT NULL, source_pack_code VARCHAR(128) DEFAULT NULL, consent_at DATETIME NOT NULL, consent_ip VARCHAR(45) DEFAULT NULL, confirmation_sent_at DATETIME DEFAULT NULL, confirmed_at DATETIME DEFAULT NULL, unsubscribed_at DATETIME DEFAULT NULL, follow_up_step SMALLINT DEFAULT 0 NOT NULL, next_follow_up_at DATETIME DEFAULT NULL, last_follow_up_at DATETIME DEFAULT NULL, created_at DATETIME NOT NULL, UNIQUE INDEX UNIQ_859B40F5E7927C74 (email), UNIQUE INDEX UNIQ_859B40F5C05FB297 (confirmation_token), UNIQUE INDEX UNIQ_859B40F5E0674361 (unsubscribe_token), INDEX idx_lead_subscriber_next_follow_up (next_follow_up_at), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE lead_subscriber');
    }
}
