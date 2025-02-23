<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250221211354 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE user_column_config (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, ruestzeit_id INT NOT NULL, columns JSON NOT NULL COMMENT \'(DC2Type:json)\', created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', updated_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_9A3327E3A76ED395 (user_id), INDEX IDX_9A3327E31375A7A2 (ruestzeit_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE user_column_config ADD CONSTRAINT FK_9A3327E3A76ED395 FOREIGN KEY (user_id) REFERENCES admin (id)');
        $this->addSql('ALTER TABLE user_column_config ADD CONSTRAINT FK_9A3327E31375A7A2 FOREIGN KEY (ruestzeit_id) REFERENCES ruestzeit (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE user_column_config DROP FOREIGN KEY FK_9A3327E3A76ED395');
        $this->addSql('ALTER TABLE user_column_config DROP FOREIGN KEY FK_9A3327E31375A7A2');
        $this->addSql('DROP TABLE user_column_config');
    }
}
