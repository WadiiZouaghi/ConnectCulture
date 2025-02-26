<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250226134919 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE discussions (id INT AUTO_INCREMENT NOT NULL, group_id INT NOT NULL, actor_id INT DEFAULT NULL, content LONGTEXT NOT NULL, created_at DATETIME NOT NULL, INDEX IDX_8B716B63FE54D947 (group_id), INDEX IDX_8B716B6310DAF24A (actor_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE discussions ADD CONSTRAINT FK_8B716B63FE54D947 FOREIGN KEY (group_id) REFERENCES user_group (id)');
        $this->addSql('ALTER TABLE discussions ADD CONSTRAINT FK_8B716B6310DAF24A FOREIGN KEY (actor_id) REFERENCES actors (actor_id)');
        $this->addSql('ALTER TABLE user_group CHANGE cover_picture cover_picture VARBINARY(255) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE discussions DROP FOREIGN KEY FK_8B716B63FE54D947');
        $this->addSql('ALTER TABLE discussions DROP FOREIGN KEY FK_8B716B6310DAF24A');
        $this->addSql('DROP TABLE discussions');
        $this->addSql('ALTER TABLE user_group CHANGE cover_picture cover_picture LONGBLOB DEFAULT NULL');
    }
}
