<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240411173017 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE anmeldung CHANGE personen_typ personen_typ VARCHAR(255) NOT NULL');
        $this->addSql('UPDATE anmeldung SET personen_typ = "TEILNEHMER"');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE anmeldung CHANGE personen_typ personen_typ VARCHAR(32) NOT NULL');
    }
}
