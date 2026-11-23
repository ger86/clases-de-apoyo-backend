<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * The files a student saved, from the app or the website. One row per file and account.
 * Deleting the file or the account deletes its bookmarks too.
 */
final class Version20261123095148 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add the user_bookmark table';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE user_bookmark (id INT AUTO_INCREMENT NOT NULL, bookmarked_at DATETIME NOT NULL, user_id INT NOT NULL, file_id INT NOT NULL, INDEX IDX_3AEF761DA76ED395 (user_id), INDEX IDX_3AEF761D93CB796C (file_id), UNIQUE INDEX user_bookmark_unique_file (user_id, file_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE user_bookmark ADD CONSTRAINT FK_3AEF761DA76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE user_bookmark ADD CONSTRAINT FK_3AEF761D93CB796C FOREIGN KEY (file_id) REFERENCES file (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE user_bookmark DROP FOREIGN KEY FK_3AEF761DA76ED395');
        $this->addSql('ALTER TABLE user_bookmark DROP FOREIGN KEY FK_3AEF761D93CB796C');
        $this->addSql('DROP TABLE user_bookmark');
    }
}
