<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240513152028 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE anmeldung_category (anmeldung_id INT NOT NULL, category_id INT NOT NULL, INDEX IDX_376698C242CC4BD9 (anmeldung_id), INDEX IDX_376698C212469DE2 (category_id), PRIMARY KEY(anmeldung_id, category_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE category (id INT AUTO_INCREMENT NOT NULL, title VARCHAR(32) NOT NULL, color VARCHAR(7) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE anmeldung_category ADD CONSTRAINT FK_376698C242CC4BD9 FOREIGN KEY (anmeldung_id) REFERENCES anmeldung (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE anmeldung_category ADD CONSTRAINT FK_376698C212469DE2 FOREIGN KEY (category_id) REFERENCES category (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE anmeldung_category DROP FOREIGN KEY FK_376698C242CC4BD9');
        $this->addSql('ALTER TABLE anmeldung_category DROP FOREIGN KEY FK_376698C212469DE2');
        $this->addSql('DROP TABLE anmeldung_category');
        $this->addSql('DROP TABLE category');
    }
}
