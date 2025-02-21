<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250221173736 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE invitations (invitation_id INT AUTO_INCREMENT NOT NULL, group_id INT NOT NULL, inviter_id INT NOT NULL, invitee_id INT NOT NULL, status VARCHAR(50) NOT NULL, created_at DATETIME NOT NULL, INDEX IDX_232710AEFE54D947 (group_id), INDEX IDX_232710AEB79F4F04 (inviter_id), INDEX IDX_232710AE7A512022 (invitee_id), PRIMARY KEY(invitation_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE invitations ADD CONSTRAINT FK_232710AEFE54D947 FOREIGN KEY (group_id) REFERENCES `groups` (group_id)');
        $this->addSql('ALTER TABLE invitations ADD CONSTRAINT FK_232710AEB79F4F04 FOREIGN KEY (inviter_id) REFERENCES actors (actor_id)');
        $this->addSql('ALTER TABLE invitations ADD CONSTRAINT FK_232710AE7A512022 FOREIGN KEY (invitee_id) REFERENCES actors (actor_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE invitations DROP FOREIGN KEY FK_232710AEFE54D947');
        $this->addSql('ALTER TABLE invitations DROP FOREIGN KEY FK_232710AEB79F4F04');
        $this->addSql('ALTER TABLE invitations DROP FOREIGN KEY FK_232710AE7A512022');
        $this->addSql('DROP TABLE invitations');
    }
}
