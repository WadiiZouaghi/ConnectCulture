<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250301215258 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE actor (id INT AUTO_INCREMENT NOT NULL, email VARCHAR(180) NOT NULL, roles JSON NOT NULL, password VARCHAR(255) NOT NULL, name VARCHAR(255) DEFAULT NULL, location VARCHAR(255) DEFAULT NULL, UNIQUE INDEX UNIQ_447556F9E7927C74 (email), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE actor_group (actor_id INT NOT NULL, group_id INT NOT NULL, INDEX IDX_4944AF5110DAF24A (actor_id), INDEX IDX_4944AF51FE54D947 (group_id), PRIMARY KEY(actor_id, group_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE discussions (id INT AUTO_INCREMENT NOT NULL, group_id INT NOT NULL, actor_id INT DEFAULT NULL, content LONGTEXT NOT NULL, created_at DATETIME NOT NULL, INDEX IDX_8B716B63FE54D947 (group_id), INDEX IDX_8B716B6310DAF24A (actor_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE event_group (id INT AUTO_INCREMENT NOT NULL, group_type_id INT DEFAULT NULL, name VARCHAR(255) NOT NULL, description LONGTEXT DEFAULT NULL, location VARCHAR(255) DEFAULT NULL, event_date DATETIME DEFAULT NULL, max_participants INT DEFAULT NULL, visibility VARCHAR(50) NOT NULL, cover_picture LONGBLOB DEFAULT NULL, latitude DOUBLE PRECISION DEFAULT NULL, longitude DOUBLE PRECISION DEFAULT NULL, INDEX IDX_2CDBF5E9434CD89F (group_type_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE group_types (id INT AUTO_INCREMENT NOT NULL, type VARCHAR(50) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE invitations (invitation_id INT AUTO_INCREMENT NOT NULL, group_id INT NOT NULL, inviter_id INT DEFAULT NULL, invitee_id INT NOT NULL, status VARCHAR(50) NOT NULL, created_at DATETIME NOT NULL, INDEX IDX_232710AEFE54D947 (group_id), INDEX IDX_232710AEB79F4F04 (inviter_id), INDEX IDX_232710AE7A512022 (invitee_id), PRIMARY KEY(invitation_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE posts (id INT AUTO_INCREMENT NOT NULL, group_id INT NOT NULL, actor_id INT NOT NULL, content LONGTEXT NOT NULL, created_at DATETIME NOT NULL, INDEX IDX_885DBAFAFE54D947 (group_id), INDEX IDX_885DBAFA10DAF24A (actor_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE messenger_messages (id BIGINT AUTO_INCREMENT NOT NULL, body LONGTEXT NOT NULL, headers LONGTEXT NOT NULL, queue_name VARCHAR(190) NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', available_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', delivered_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_75EA56E0FB7336F0 (queue_name), INDEX IDX_75EA56E0E3BD61CE (available_at), INDEX IDX_75EA56E016BA31DB (delivered_at), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE actor_group ADD CONSTRAINT FK_4944AF5110DAF24A FOREIGN KEY (actor_id) REFERENCES actor (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE actor_group ADD CONSTRAINT FK_4944AF51FE54D947 FOREIGN KEY (group_id) REFERENCES event_group (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE discussions ADD CONSTRAINT FK_8B716B63FE54D947 FOREIGN KEY (group_id) REFERENCES event_group (id)');
        $this->addSql('ALTER TABLE discussions ADD CONSTRAINT FK_8B716B6310DAF24A FOREIGN KEY (actor_id) REFERENCES actor (id)');
        $this->addSql('ALTER TABLE event_group ADD CONSTRAINT FK_2CDBF5E9434CD89F FOREIGN KEY (group_type_id) REFERENCES group_types (id)');
        $this->addSql('ALTER TABLE invitations ADD CONSTRAINT FK_232710AEFE54D947 FOREIGN KEY (group_id) REFERENCES event_group (id)');
        $this->addSql('ALTER TABLE invitations ADD CONSTRAINT FK_232710AEB79F4F04 FOREIGN KEY (inviter_id) REFERENCES actor (id)');
        $this->addSql('ALTER TABLE invitations ADD CONSTRAINT FK_232710AE7A512022 FOREIGN KEY (invitee_id) REFERENCES actor (id)');
        $this->addSql('ALTER TABLE posts ADD CONSTRAINT FK_885DBAFAFE54D947 FOREIGN KEY (group_id) REFERENCES event_group (id)');
        $this->addSql('ALTER TABLE posts ADD CONSTRAINT FK_885DBAFA10DAF24A FOREIGN KEY (actor_id) REFERENCES actor (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE actor_group DROP FOREIGN KEY FK_4944AF5110DAF24A');
        $this->addSql('ALTER TABLE actor_group DROP FOREIGN KEY FK_4944AF51FE54D947');
        $this->addSql('ALTER TABLE discussions DROP FOREIGN KEY FK_8B716B63FE54D947');
        $this->addSql('ALTER TABLE discussions DROP FOREIGN KEY FK_8B716B6310DAF24A');
        $this->addSql('ALTER TABLE event_group DROP FOREIGN KEY FK_2CDBF5E9434CD89F');
        $this->addSql('ALTER TABLE invitations DROP FOREIGN KEY FK_232710AEFE54D947');
        $this->addSql('ALTER TABLE invitations DROP FOREIGN KEY FK_232710AEB79F4F04');
        $this->addSql('ALTER TABLE invitations DROP FOREIGN KEY FK_232710AE7A512022');
        $this->addSql('ALTER TABLE posts DROP FOREIGN KEY FK_885DBAFAFE54D947');
        $this->addSql('ALTER TABLE posts DROP FOREIGN KEY FK_885DBAFA10DAF24A');
        $this->addSql('DROP TABLE actor');
        $this->addSql('DROP TABLE actor_group');
        $this->addSql('DROP TABLE discussions');
        $this->addSql('DROP TABLE event_group');
        $this->addSql('DROP TABLE group_types');
        $this->addSql('DROP TABLE invitations');
        $this->addSql('DROP TABLE posts');
        $this->addSql('DROP TABLE messenger_messages');
    }
}
