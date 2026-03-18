<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220323111751 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql("UPDATE user SET roles = '' WHERE roles = 'a:0:{}'");
        $this->addSql("UPDATE user SET roles = '[\"ROLE_SUPER_ADMIN\"]' WHERE roles LIKE '%ROLE_SUPER_ADMIN%'");
    }

    public function down(Schema $schema): void
    {
    }
}
