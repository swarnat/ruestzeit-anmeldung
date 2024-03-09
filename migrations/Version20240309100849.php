<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240309100849 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE anmeldung ADD postalcode VARCHAR(10) NOT NULL, ADD city VARCHAR(100) NOT NULL, ADD address VARCHAR(255) NOT NULL, ADD phone VARCHAR(100) DEFAULT NULL, ADD notes LONGTEXT DEFAULT NULL, ADD prepayment_done TINYINT(1) NOT NULL, ADD payment_done TINYINT(1) NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE anmeldung DROP postalcode, DROP city, DROP address, DROP phone, DROP notes, DROP prepayment_done, DROP payment_done');
    }
}
