<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220321150510 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE media__gallery_media DROP FOREIGN KEY FK_80D4C5414E7AF8F');
        $this->addSql('ALTER TABLE article DROP FOREIGN KEY FK_23A0E6619EB6921');
        $this->addSql('CREATE TABLE refresh_tokens (id INT AUTO_INCREMENT NOT NULL, refresh_token VARCHAR(128) NOT NULL, username VARCHAR(255) NOT NULL, valid DATETIME NOT NULL, UNIQUE INDEX UNIQ_9BACE7E1C74F2195 (refresh_token), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('DROP TABLE media__gallery');
        $this->addSql('DROP TABLE media__gallery_media');
        $this->addSql('DROP TABLE metatag');
        $this->addSql('DROP INDEX UNIQ_23A0E6619EB6921 ON article');
        $this->addSql('ALTER TABLE article CHANGE created created_at DATETIME NOT NULL, CHANGE updated updated_at DATETIME NOT NULL, DROP client_id, DROP text_raw');
        $this->addSql('ALTER TABLE book CHANGE format_type format_type VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE chapter CHANGE created created_at DATETIME NOT NULL, CHANGE updated updated_at DATETIME NOT NULL, CHANGE slug slug VARCHAR(256) NOT NULL, CHANGE description_format_type description_format_type VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE chapter_block CHANGE created created_at DATETIME NOT NULL, CHANGE updated updated_at DATETIME NOT NULL');
        $this->addSql('ALTER TABLE community CHANGE slug slug VARCHAR(256) NOT NULL');
        $this->addSql('ALTER TABLE community_test CHANGE created created_at DATETIME NOT NULL, CHANGE updated updated_at DATETIME NOT NULL, CHANGE description_format_type description_format_type VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE community_test_course_subject CHANGE created created_at DATETIME NOT NULL, CHANGE updated updated_at DATETIME NOT NULL, CHANGE description_format_type description_format_type VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE course CHANGE created created_at DATETIME NOT NULL, CHANGE updated updated_at DATETIME NOT NULL, CHANGE slug slug VARCHAR(256) NOT NULL, CHANGE description_format_type description_format_type VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE course_subject CHANGE created created_at DATETIME NOT NULL, CHANGE updated updated_at DATETIME NOT NULL, CHANGE description_format_type description_format_type VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE discount_code ADD stripe_plan_id VARCHAR(256) NOT NULL, CHANGE price price VARCHAR(256) NOT NULL');
        $this->addSql('ALTER TABLE exam CHANGE created created_at DATETIME NOT NULL, CHANGE updated updated_at DATETIME NOT NULL, CHANGE slug slug VARCHAR(256) NOT NULL, CHANGE weight weight INT DEFAULT NULL, CHANGE description_format_type description_format_type VARCHAR(255) DEFAULT NULL');
        $this->addSql('CREATE INDEX UNIQ_38BBA6C6989D9B62 ON exam (slug)');
        $this->addSql('ALTER TABLE file CHANGE name name VARCHAR(256) DEFAULT NULL');
        $this->addSql('ALTER TABLE knowledge_test CHANGE created created_at DATETIME NOT NULL, CHANGE updated updated_at DATETIME NOT NULL, CHANGE slug slug VARCHAR(256) NOT NULL, CHANGE description_format_type description_format_type VARCHAR(255) DEFAULT NULL');
        $this->addSql('UPDATE media__media SET cdn_is_flushable = false WHERE cdn_is_flushable IS NULL');
        $this->addSql('ALTER TABLE media__media CHANGE provider_metadata provider_metadata LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:json)\', CHANGE cdn_is_flushable cdn_is_flushable TINYINT(1) NOT NULL');
        $this->addSql('ALTER TABLE premium_payment CHANGE created created_at DATETIME NOT NULL');
        $this->addSql('ALTER TABLE subject CHANGE slug slug VARCHAR(256) NOT NULL');
        $this->addSql('ALTER TABLE test_year CHANGE created created_at DATETIME NOT NULL, CHANGE updated updated_at DATETIME NOT NULL');
        $this->addSql('DROP INDEX UNIQ_8D93D64992FC23A8 ON user');
        $this->addSql('DROP INDEX UNIQ_8D93D649A0D96FBF ON user');
        $this->addSql('DROP INDEX UNIQ_8D93D649C05FB297 ON user');
        $this->addSql(
            'ALTER TABLE user 
            ADD subscription_id VARCHAR(255) DEFAULT NULL, 
            CHANGE created created_at DATETIME NOT NULL, 
            CHANGE updated updated_at DATETIME NOT NULL,
            DROP username, 
            DROP username_canonical, 
            DROP email_canonical, 
            DROP last_login, 
            DROP confirmation_token, 
            DROP password_requested_at, 
            CHANGE password password VARCHAR(255) DEFAULT NULL, 
            CHANGE roles roles LONGTEXT NOT NULL COMMENT \'(DC2Type:json)\', 
            CHANGE salt customer_id VARCHAR(255) DEFAULT NULL, 
            CHANGE enabled is_verified TINYINT(1) NOT NULL'
        );
        $this->addSql('CREATE UNIQUE INDEX UNIQ_8D93D649E7927C74 ON user (email)');
        $this->addSql('UPDATE youtube_video SET created = updated WHERE created IS NULL');
        $this->addSql('ALTER TABLE youtube_video CHANGE created created_at DATETIME NOT NULL, CHANGE updated updated_at DATETIME NOT NULL, CHANGE slug slug VARCHAR(256) NOT NULL, CHANGE excerpt excerpt LONGTEXT DEFAULT NULL, CHANGE description_format_type description_format_type VARCHAR(255) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE media__gallery (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, context VARCHAR(64) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, default_format VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, enabled TINYINT(1) NOT NULL, updated_at DATETIME NOT NULL, created_at DATETIME NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE media__gallery_media (id INT AUTO_INCREMENT NOT NULL, gallery_id INT DEFAULT NULL, media_id INT DEFAULT NULL, position INT NOT NULL, enabled TINYINT(1) NOT NULL, updated_at DATETIME NOT NULL, created_at DATETIME NOT NULL, INDEX IDX_80D4C5414E7AF8F (gallery_id), INDEX IDX_80D4C541EA9FDD75 (media_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE metatag (id INT AUTO_INCREMENT NOT NULL, meta_title VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, meta_description LONGTEXT CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, meta_image VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE media__gallery_media ADD CONSTRAINT FK_80D4C5414E7AF8F FOREIGN KEY (gallery_id) REFERENCES media__gallery (id) ON UPDATE NO ACTION ON DELETE CASCADE');
        $this->addSql('ALTER TABLE media__gallery_media ADD CONSTRAINT FK_80D4C541EA9FDD75 FOREIGN KEY (media_id) REFERENCES media__media (id) ON UPDATE NO ACTION ON DELETE CASCADE');
        $this->addSql('DROP TABLE refresh_tokens');
        $this->addSql('ALTER TABLE article ADD client_id INT DEFAULT NULL, ADD text_raw LONGTEXT CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, ADD created DATETIME NOT NULL, ADD updated DATETIME NOT NULL, DROP created_at, DROP updated_at');
        $this->addSql('ALTER TABLE article ADD CONSTRAINT FK_23A0E6619EB6921 FOREIGN KEY (client_id) REFERENCES metatag (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_23A0E6619EB6921 ON article (client_id)');
        $this->addSql('ALTER TABLE book CHANGE format_type format_type LONGTEXT CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('ALTER TABLE chapter ADD created DATETIME DEFAULT NULL, ADD updated DATETIME DEFAULT NULL, DROP created_at, DROP updated_at, CHANGE description_format_type description_format_type LONGTEXT CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, CHANGE slug slug VARCHAR(128) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('ALTER TABLE chapter_block ADD created DATETIME DEFAULT NULL, ADD updated DATETIME DEFAULT NULL, DROP created_at, DROP updated_at');
        $this->addSql('ALTER TABLE community CHANGE slug slug VARCHAR(128) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('ALTER TABLE community_test ADD created DATETIME DEFAULT NULL, ADD updated DATETIME DEFAULT NULL, DROP created_at, DROP updated_at, CHANGE description_format_type description_format_type LONGTEXT CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('ALTER TABLE community_test_course_subject ADD created DATETIME DEFAULT NULL, ADD updated DATETIME DEFAULT NULL, DROP created_at, DROP updated_at, CHANGE description_format_type description_format_type LONGTEXT CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('ALTER TABLE course ADD created DATETIME DEFAULT NULL, ADD updated DATETIME DEFAULT NULL, DROP created_at, DROP updated_at, CHANGE description_format_type description_format_type LONGTEXT CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, CHANGE slug slug VARCHAR(128) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('ALTER TABLE course_subject ADD created DATETIME DEFAULT NULL, ADD updated DATETIME DEFAULT NULL, DROP created_at, DROP updated_at, CHANGE description_format_type description_format_type LONGTEXT CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('ALTER TABLE discount_code DROP stripe_plan_id, CHANGE price price DOUBLE PRECISION NOT NULL');
        $this->addSql('DROP INDEX UNIQ_38BBA6C6989D9B62 ON exam');
        $this->addSql('ALTER TABLE exam ADD created DATETIME DEFAULT NULL, ADD updated DATETIME DEFAULT NULL, DROP created_at, DROP updated_at, CHANGE description_format_type description_format_type LONGTEXT CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, CHANGE slug slug VARCHAR(1024) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, CHANGE weight weight INT NOT NULL');
        $this->addSql('ALTER TABLE file CHANGE name name VARCHAR(512) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('ALTER TABLE knowledge_test ADD created DATETIME DEFAULT NULL, ADD updated DATETIME DEFAULT NULL, DROP created_at, DROP updated_at, CHANGE slug slug VARCHAR(128) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, CHANGE description_format_type description_format_type LONGTEXT CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('ALTER TABLE media__media CHANGE provider_metadata provider_metadata LONGTEXT CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci` COMMENT \'(DC2Type:json)\', CHANGE cdn_is_flushable cdn_is_flushable TINYINT(1) DEFAULT NULL');
        $this->addSql('ALTER TABLE premium_payment ADD created DATETIME DEFAULT NULL, DROP created_at');
        $this->addSql('ALTER TABLE subject CHANGE slug slug VARCHAR(128) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('ALTER TABLE test_year ADD created DATETIME DEFAULT NULL, ADD updated DATETIME DEFAULT NULL, DROP created_at, DROP updated_at');
        $this->addSql('DROP INDEX UNIQ_8D93D649E7927C74 ON user');
        $this->addSql('ALTER TABLE user ADD username VARCHAR(180) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, ADD username_canonical VARCHAR(180) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, ADD email_canonical VARCHAR(180) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, ADD salt VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, ADD last_login DATETIME DEFAULT NULL, ADD confirmation_token VARCHAR(180) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, ADD password_requested_at DATETIME DEFAULT NULL, ADD created DATETIME DEFAULT NULL, ADD updated DATETIME DEFAULT NULL, DROP customer_id, DROP subscription_id, DROP created_at, DROP updated_at, CHANGE roles roles LONGTEXT CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci` COMMENT \'(DC2Type:array)\', CHANGE password password VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, CHANGE is_verified enabled TINYINT(1) NOT NULL');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_8D93D64992FC23A8 ON user (username_canonical)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_8D93D649A0D96FBF ON user (email_canonical)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_8D93D649C05FB297 ON user (confirmation_token)');
        $this->addSql('ALTER TABLE youtube_video ADD created DATETIME DEFAULT NULL, ADD updated DATETIME DEFAULT NULL, DROP created_at, DROP updated_at, CHANGE slug slug VARCHAR(128) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, CHANGE excerpt excerpt TEXT CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, CHANGE description_format_type description_format_type LONGTEXT CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('ALTER TABLE youtube_video_chapter DROP PRIMARY KEY');
        $this->addSql('ALTER TABLE youtube_video_chapter ADD youtube_video_id INT NOT NULL');
        $this->addSql('ALTER TABLE youtube_video_chapter ADD CONSTRAINT FK_393BCE41579F4768 FOREIGN KEY (chapter_id) REFERENCES chapter (id) ON UPDATE NO ACTION ON DELETE CASCADE');
        $this->addSql('ALTER TABLE youtube_video_chapter ADD CONSTRAINT FK_393BCE418E06FC7F FOREIGN KEY (youtube_video_id) REFERENCES youtube_video (id) ON UPDATE NO ACTION ON DELETE CASCADE');
        $this->addSql('CREATE INDEX IDX_393BCE41579F4768 ON youtube_video_chapter (chapter_id)');
        $this->addSql('CREATE INDEX IDX_393BCE418E06FC7F ON youtube_video_chapter (youtube_video_id)');
        $this->addSql('ALTER TABLE youtube_video_chapter ADD PRIMARY KEY (youtube_video_id, chapter_id)');
    }
}
