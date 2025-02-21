<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250221145509 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE actors (actor_id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, last_name VARCHAR(255) NOT NULL, email VARCHAR(255) NOT NULL, password VARCHAR(255) NOT NULL, role VARCHAR(50) NOT NULL, UNIQUE INDEX UNIQ_DF2BF0E5E7927C74 (email), PRIMARY KEY(actor_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE actor_group (actor_id INT NOT NULL, group_id INT NOT NULL, INDEX IDX_4944AF5110DAF24A (actor_id), INDEX IDX_4944AF51FE54D947 (group_id), PRIMARY KEY(actor_id, group_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE group_types (id INT AUTO_INCREMENT NOT NULL, type VARCHAR(50) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE `groups` (group_id INT AUTO_INCREMENT NOT NULL, group_type_id INT NOT NULL, name VARCHAR(255) NOT NULL, INDEX IDX_F06D3970434CD89F (group_type_id), PRIMARY KEY(group_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE messenger_messages (id BIGINT AUTO_INCREMENT NOT NULL, body LONGTEXT NOT NULL, headers LONGTEXT NOT NULL, queue_name VARCHAR(190) NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', available_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', delivered_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_75EA56E0FB7336F0 (queue_name), INDEX IDX_75EA56E0E3BD61CE (available_at), INDEX IDX_75EA56E016BA31DB (delivered_at), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE actor_group ADD CONSTRAINT FK_4944AF5110DAF24A FOREIGN KEY (actor_id) REFERENCES actors (actor_id)');
        $this->addSql('ALTER TABLE actor_group ADD CONSTRAINT FK_4944AF51FE54D947 FOREIGN KEY (group_id) REFERENCES `groups` (group_id)');
        $this->addSql('ALTER TABLE `groups` ADD CONSTRAINT FK_F06D3970434CD89F FOREIGN KEY (group_type_id) REFERENCES group_types (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE actor_group DROP FOREIGN KEY FK_4944AF5110DAF24A');
        $this->addSql('ALTER TABLE actor_group DROP FOREIGN KEY FK_4944AF51FE54D947');
        $this->addSql('ALTER TABLE `groups` DROP FOREIGN KEY FK_F06D3970434CD89F');
        $this->addSql('DROP TABLE actors');
        $this->addSql('DROP TABLE actor_group');
        $this->addSql('DROP TABLE group_types');
        $this->addSql('DROP TABLE `groups`');
        $this->addSql('DROP TABLE messenger_messages');
    }
}
