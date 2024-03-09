<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240309155729 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE ruestzeit ADD admin_id INT NOT NULL');
        $this->addSql('UPDATE ruestzeit SET admin_id = 1');
        $this->addSql('ALTER TABLE ruestzeit ADD CONSTRAINT FK_2D4F16D3642B8210 FOREIGN KEY (admin_id) REFERENCES admin (id)');
        $this->addSql('CREATE INDEX IDX_2D4F16D3642B8210 ON ruestzeit (admin_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE ruestzeit DROP FOREIGN KEY FK_2D4F16D3642B8210');
        $this->addSql('DROP INDEX IDX_2D4F16D3642B8210 ON ruestzeit');
        $this->addSql('ALTER TABLE ruestzeit DROP admin_id');
    }
}
