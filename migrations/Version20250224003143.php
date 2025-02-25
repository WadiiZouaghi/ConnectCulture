<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250224003143 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE actor_group DROP FOREIGN KEY FK_4944AF51FE54D947');
        $this->addSql('ALTER TABLE invitations DROP FOREIGN KEY FK_232710AEFE54D947');
        $this->addSql('CREATE TABLE user_group (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, description LONGTEXT DEFAULT NULL, location VARCHAR(255) DEFAULT NULL, event_date DATETIME DEFAULT NULL, max_participants INT DEFAULT NULL, visibility VARCHAR(50) NOT NULL, cover_picture VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('DROP TABLE `groups`');
        $this->addSql('DROP INDEX IDX_4944AF51FE54D947 ON actor_group');
        $this->addSql('DROP INDEX `primary` ON actor_group');
        $this->addSql('ALTER TABLE actor_group CHANGE group_id id INT NOT NULL');
        $this->addSql('ALTER TABLE actor_group ADD CONSTRAINT FK_4944AF51BF396750 FOREIGN KEY (id) REFERENCES user_group (id)');
        $this->addSql('CREATE INDEX IDX_4944AF51BF396750 ON actor_group (id)');
        $this->addSql('ALTER TABLE actor_group ADD PRIMARY KEY (actor_id, id)');
        $this->addSql('DROP INDEX IDX_232710AEFE54D947 ON invitations');
        $this->addSql('ALTER TABLE invitations CHANGE inviter_id inviter_id INT DEFAULT NULL, CHANGE group_id id INT NOT NULL');
        $this->addSql('ALTER TABLE invitations ADD CONSTRAINT FK_232710AEBF396750 FOREIGN KEY (id) REFERENCES user_group (id)');
        $this->addSql('CREATE INDEX IDX_232710AEBF396750 ON invitations (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE actor_group DROP FOREIGN KEY FK_4944AF51BF396750');
        $this->addSql('ALTER TABLE invitations DROP FOREIGN KEY FK_232710AEBF396750');
        $this->addSql('CREATE TABLE `groups` (group_id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, description LONGTEXT CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, location VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, event_date DATETIME DEFAULT NULL, max_participants INT DEFAULT NULL, visibility VARCHAR(50) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, PRIMARY KEY(group_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('DROP TABLE user_group');
        $this->addSql('DROP INDEX IDX_4944AF51BF396750 ON actor_group');
        $this->addSql('DROP INDEX `PRIMARY` ON actor_group');
        $this->addSql('ALTER TABLE actor_group CHANGE id group_id INT NOT NULL');
        $this->addSql('ALTER TABLE actor_group ADD CONSTRAINT FK_4944AF51FE54D947 FOREIGN KEY (group_id) REFERENCES `groups` (group_id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('CREATE INDEX IDX_4944AF51FE54D947 ON actor_group (group_id)');
        $this->addSql('ALTER TABLE actor_group ADD PRIMARY KEY (actor_id, group_id)');
        $this->addSql('DROP INDEX IDX_232710AEBF396750 ON invitations');
        $this->addSql('ALTER TABLE invitations CHANGE inviter_id inviter_id INT NOT NULL, CHANGE id group_id INT NOT NULL');
        $this->addSql('ALTER TABLE invitations ADD CONSTRAINT FK_232710AEFE54D947 FOREIGN KEY (group_id) REFERENCES `groups` (group_id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('CREATE INDEX IDX_232710AEFE54D947 ON invitations (group_id)');
    }
}
