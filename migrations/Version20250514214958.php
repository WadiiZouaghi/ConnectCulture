<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250514214958 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE event CHANGE description description TEXT DEFAULT NULL, CHANGE user_id user_id INT NOT NULL DEFAULT 1, CHANGE nbplaces nbplaces INT DEFAULT 0');
        $this->addSql('ALTER TABLE user CHANGE ban_reason ban_reason TEXT DEFAULT NULL, CHANGE created_at created_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE user CHANGE ban_reason ban_reason TEXT DEFAULT NULL, CHANGE created_at created_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL');
        $this->addSql('ALTER TABLE event CHANGE description description TEXT DEFAULT NULL, CHANGE user_id user_id INT DEFAULT 1 NOT NULL, CHANGE nbplaces nbplaces INT DEFAULT 0');
    }
}
