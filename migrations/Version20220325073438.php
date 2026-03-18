<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220325073438 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE media__media CHANGE provider_metadata provider_metadata JSON DEFAULT NULL');
        $this->addSql('ALTER TABLE premium_payment CHANGE user_id user_id INT NOT NULL, CHANGE payment_id payment_id VARCHAR(512) NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE media__media CHANGE provider_metadata provider_metadata JSON DEFAULT NULL');
        $this->addSql('ALTER TABLE premium_payment CHANGE user_id user_id INT DEFAULT NULL, CHANGE payment_id payment_id VARCHAR(512) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`');
    }
}
