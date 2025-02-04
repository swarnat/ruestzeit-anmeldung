<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250204223154 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE custom_field (id INT AUTO_INCREMENT NOT NULL, owner_id INT NOT NULL, ruestzeit_id INT NOT NULL, title VARCHAR(255) NOT NULL, type VARCHAR(255) NOT NULL, options JSON DEFAULT NULL COMMENT \'(DC2Type:json)\', INDEX IDX_98F8BD317E3C61F9 (owner_id), INDEX IDX_98F8BD311375A7A2 (ruestzeit_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE custom_field_answer (id INT AUTO_INCREMENT NOT NULL, custom_field_id INT NOT NULL, anmeldung_id INT NOT NULL, value LONGTEXT NOT NULL, INDEX IDX_8B1ED13CA1E5E0D4 (custom_field_id), INDEX IDX_8B1ED13C42CC4BD9 (anmeldung_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE custom_field ADD CONSTRAINT FK_98F8BD317E3C61F9 FOREIGN KEY (owner_id) REFERENCES admin (id)');
        $this->addSql('ALTER TABLE custom_field ADD CONSTRAINT FK_98F8BD311375A7A2 FOREIGN KEY (ruestzeit_id) REFERENCES ruestzeit (id)');
        $this->addSql('ALTER TABLE custom_field_answer ADD CONSTRAINT FK_8B1ED13CA1E5E0D4 FOREIGN KEY (custom_field_id) REFERENCES custom_field (id)');
        $this->addSql('ALTER TABLE custom_field_answer ADD CONSTRAINT FK_8B1ED13C42CC4BD9 FOREIGN KEY (anmeldung_id) REFERENCES anmeldung (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE custom_field DROP FOREIGN KEY FK_98F8BD317E3C61F9');
        $this->addSql('ALTER TABLE custom_field DROP FOREIGN KEY FK_98F8BD311375A7A2');
        $this->addSql('ALTER TABLE custom_field_answer DROP FOREIGN KEY FK_8B1ED13CA1E5E0D4');
        $this->addSql('ALTER TABLE custom_field_answer DROP FOREIGN KEY FK_8B1ED13C42CC4BD9');
        $this->addSql('DROP TABLE custom_field');
        $this->addSql('DROP TABLE custom_field_answer');
    }
}
