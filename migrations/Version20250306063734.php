<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250306063734 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE agency ADD agency_id INT AUTO_INCREMENT NOT NULL, ADD service_id INT DEFAULT NULL, ADD name VARCHAR(255) NOT NULL, ADD email VARCHAR(255) DEFAULT NULL, CHANGE id address_id INT NOT NULL, ADD PRIMARY KEY (agency_id)');
        $this->addSql('ALTER TABLE agency ADD CONSTRAINT FK_70C0C6E6ED5CA9E6 FOREIGN KEY (service_id) REFERENCES service (id)');
        $this->addSql('ALTER TABLE agency ADD CONSTRAINT FK_70C0C6E6F5B7AF75 FOREIGN KEY (address_id) REFERENCES address (id)');
        $this->addSql('CREATE INDEX IDX_70C0C6E6ED5CA9E6 ON agency (service_id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_70C0C6E6F5B7AF75 ON agency (address_id)');
        $this->addSql('ALTER TABLE service ADD name VARCHAR(255) NOT NULL, ADD description LONGTEXT NOT NULL');
        $this->addSql('ALTER TABLE service_equipment ADD service_id INT DEFAULT NULL, ADD name VARCHAR(255) NOT NULL, ADD description LONGTEXT NOT NULL, ADD image VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE service_equipment ADD CONSTRAINT FK_8BA9EFC9ED5CA9E6 FOREIGN KEY (service_id) REFERENCES service (id)');
        $this->addSql('CREATE INDEX IDX_8BA9EFC9ED5CA9E6 ON service_equipment (service_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE agency MODIFY agency_id INT NOT NULL');
        $this->addSql('ALTER TABLE agency DROP FOREIGN KEY FK_70C0C6E6ED5CA9E6');
        $this->addSql('ALTER TABLE agency DROP FOREIGN KEY FK_70C0C6E6F5B7AF75');
        $this->addSql('DROP INDEX IDX_70C0C6E6ED5CA9E6 ON agency');
        $this->addSql('DROP INDEX UNIQ_70C0C6E6F5B7AF75 ON agency');
        $this->addSql('DROP INDEX `primary` ON agency');
        $this->addSql('ALTER TABLE agency DROP agency_id, DROP service_id, DROP name, DROP email, CHANGE address_id id INT NOT NULL');
        $this->addSql('ALTER TABLE service_equipment DROP FOREIGN KEY FK_8BA9EFC9ED5CA9E6');
        $this->addSql('DROP INDEX IDX_8BA9EFC9ED5CA9E6 ON service_equipment');
        $this->addSql('ALTER TABLE service_equipment DROP service_id, DROP name, DROP description, DROP image');
        $this->addSql('ALTER TABLE service DROP name, DROP description');
    }
}
