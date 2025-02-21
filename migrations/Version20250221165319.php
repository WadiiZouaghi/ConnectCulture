<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250221165319 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE `groups` DROP FOREIGN KEY FK_F06D3970434CD89F');
        $this->addSql('DROP INDEX IDX_F06D3970434CD89F ON `groups`');
        $this->addSql('ALTER TABLE `groups` DROP group_type_id');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE `groups` ADD group_type_id INT NOT NULL');
        $this->addSql('ALTER TABLE `groups` ADD CONSTRAINT FK_F06D3970434CD89F FOREIGN KEY (group_type_id) REFERENCES group_types (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('CREATE INDEX IDX_F06D3970434CD89F ON `groups` (group_type_id)');
    }
}
