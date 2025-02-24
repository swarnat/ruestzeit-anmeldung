<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250224221442 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE category ADD ruestzeit_id INT NULL');
        
        // Set default value of 1 for all existing categories
        $this->addSql('UPDATE category SET ruestzeit_id = 1 WHERE ruestzeit_id IS NULL');
        
        // Now make the column NOT NULL after setting default values
        $this->addSql('ALTER TABLE category MODIFY ruestzeit_id INT NOT NULL');
        
        $this->addSql('ALTER TABLE category ADD CONSTRAINT FK_64C19C11375A7A2 FOREIGN KEY (ruestzeit_id) REFERENCES ruestzeit (id)');
        $this->addSql('CREATE INDEX IDX_64C19C11375A7A2 ON category (ruestzeit_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE category DROP FOREIGN KEY FK_64C19C11375A7A2');
        $this->addSql('DROP INDEX IDX_64C19C11375A7A2 ON category');
        $this->addSql('ALTER TABLE category DROP ruestzeit_id');
    }
}
