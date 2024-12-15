<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241213165536 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE language_overwrite (id INT AUTO_INCREMENT NOT NULL, ruestzeit_id INT NOT NULL, term VARCHAR(255) NOT NULL, value VARCHAR(255) NOT NULL, INDEX IDX_EDA1FBEC1375A7A2 (ruestzeit_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE language_overwrite ADD CONSTRAINT FK_EDA1FBEC1375A7A2 FOREIGN KEY (ruestzeit_id) REFERENCES ruestzeit (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE language_overwrite DROP FOREIGN KEY FK_EDA1FBEC1375A7A2');
        $this->addSql('DROP TABLE language_overwrite');
    }
}
