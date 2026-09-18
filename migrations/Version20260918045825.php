<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * The chapters and exams a student marked as done from the app. One row per mark, tied to
 * the account so the marks follow the student between phones.
 */
final class Version20260918045825 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add the user_progress table';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE user_progress (id INT AUTO_INCREMENT NOT NULL, kind VARCHAR(20) NOT NULL, target_id INT NOT NULL, done_at DATETIME NOT NULL, user_id INT NOT NULL, INDEX IDX_C28C1646A76ED395 (user_id), UNIQUE INDEX user_progress_unique_target (user_id, kind, target_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE user_progress ADD CONSTRAINT FK_C28C1646A76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE user_progress DROP FOREIGN KEY FK_C28C1646A76ED395');
        $this->addSql('DROP TABLE user_progress');
    }
}
