<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240315165423 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE location (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, title VARCHAR(255) NOT NULL, street VARCHAR(120) NOT NULL, postalcode VARCHAR(10) NOT NULL, city VARCHAR(64) NOT NULL, INDEX IDX_5E9E89CBA76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE location ADD CONSTRAINT FK_5E9E89CBA76ED395 FOREIGN KEY (user_id) REFERENCES admin (id)');
        $this->addSql('ALTER TABLE ruestzeit ADD location_id INT DEFAULT "1" NOT NULL');

        $this->addSql('SET foreign_key_checks = 0');

        $this->addSql('UPDATE ruestzeit SET location_id = 1');
        $this->addSql('ALTER TABLE ruestzeit ADD CONSTRAINT FK_2D4F16D364D218E FOREIGN KEY (location_id) REFERENCES location (id)');
        $this->addSql('CREATE INDEX IDX_2D4F16D364D218E ON ruestzeit (location_id)');

        
    }

    public function postUp(Schema $schema): void
    {

        $this->connection->insert("location", [
            "id" => 1,
            "user_id" => 1,
            "title" => "Dummy Location",
            "street" => "Beispielstrasse 1",
            "postalcode" => "09394",
            "city" => "Hohndorf"
        ]);

        $this->connection->executeQuery("SET foreign_key_checks = 1");


    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE ruestzeit DROP FOREIGN KEY FK_2D4F16D364D218E');
        $this->addSql('ALTER TABLE location DROP FOREIGN KEY FK_5E9E89CBA76ED395');
        $this->addSql('DROP TABLE location');
        $this->addSql('DROP INDEX IDX_2D4F16D364D218E ON ruestzeit');
        $this->addSql('ALTER TABLE ruestzeit DROP location_id');
    }
}
