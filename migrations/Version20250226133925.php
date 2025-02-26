<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250226133925 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE actor_group DROP FOREIGN KEY FK_4944AF51BF396750');
        $this->addSql('ALTER TABLE invitations DROP FOREIGN KEY FK_232710AEBF396750');
        $this->addSql('ALTER TABLE posts DROP FOREIGN KEY FK_885DBAFAFE54D947');
        $this->addSql('CREATE TABLE discussions (id INT AUTO_INCREMENT NOT NULL, group_id INT NOT NULL, actor_id INT DEFAULT NULL, content LONGTEXT NOT NULL, created_at DATETIME NOT NULL, INDEX IDX_8B716B63FE54D947 (group_id), INDEX IDX_8B716B6310DAF24A (actor_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE `groups` (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, description LONGTEXT DEFAULT NULL, location VARCHAR(255) DEFAULT NULL, event_date DATETIME DEFAULT NULL, max_participants INT DEFAULT NULL, visibility VARCHAR(50) NOT NULL, cover_picture VARBINARY(255) DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE discussions ADD CONSTRAINT FK_8B716B63FE54D947 FOREIGN KEY (group_id) REFERENCES `groups` (id)');
        $this->addSql('ALTER TABLE discussions ADD CONSTRAINT FK_8B716B6310DAF24A FOREIGN KEY (actor_id) REFERENCES actors (actor_id)');
        $this->addSql('DROP TABLE user_group');
        $this->addSql('ALTER TABLE actor_group DROP FOREIGN KEY FK_4944AF51BF396750');
        $this->addSql('ALTER TABLE actor_group ADD CONSTRAINT FK_4944AF51BF396750 FOREIGN KEY (id) REFERENCES `groups` (id)');
        $this->addSql('ALTER TABLE invitations DROP FOREIGN KEY FK_232710AEBF396750');
        $this->addSql('ALTER TABLE invitations ADD CONSTRAINT FK_232710AEBF396750 FOREIGN KEY (id) REFERENCES `groups` (id)');
        $this->addSql('ALTER TABLE posts DROP FOREIGN KEY FK_885DBAFAFE54D947');
        $this->addSql('ALTER TABLE posts ADD CONSTRAINT FK_885DBAFAFE54D947 FOREIGN KEY (group_id) REFERENCES `groups` (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE actor_group DROP FOREIGN KEY FK_4944AF51BF396750');
        $this->addSql('ALTER TABLE invitations DROP FOREIGN KEY FK_232710AEBF396750');
        $this->addSql('ALTER TABLE posts DROP FOREIGN KEY FK_885DBAFAFE54D947');
        $this->addSql('CREATE TABLE user_group (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, description LONGTEXT CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, location VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, event_date DATETIME DEFAULT NULL, max_participants INT DEFAULT NULL, visibility VARCHAR(50) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, cover_picture LONGBLOB DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE discussions DROP FOREIGN KEY FK_8B716B63FE54D947');
        $this->addSql('ALTER TABLE discussions DROP FOREIGN KEY FK_8B716B6310DAF24A');
        $this->addSql('DROP TABLE discussions');
        $this->addSql('DROP TABLE `groups`');
        $this->addSql('ALTER TABLE actor_group DROP FOREIGN KEY FK_4944AF51BF396750');
        $this->addSql('ALTER TABLE actor_group ADD CONSTRAINT FK_4944AF51BF396750 FOREIGN KEY (id) REFERENCES user_group (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('ALTER TABLE invitations DROP FOREIGN KEY FK_232710AEBF396750');
        $this->addSql('ALTER TABLE invitations ADD CONSTRAINT FK_232710AEBF396750 FOREIGN KEY (id) REFERENCES user_group (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('ALTER TABLE posts DROP FOREIGN KEY FK_885DBAFAFE54D947');
        $this->addSql('ALTER TABLE posts ADD CONSTRAINT FK_885DBAFAFE54D947 FOREIGN KEY (group_id) REFERENCES user_group (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
    }
}
