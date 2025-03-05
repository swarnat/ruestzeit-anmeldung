<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250305164845 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE protocol (id INT AUTO_INCREMENT NOT NULL, ruestzeit_id INT DEFAULT NULL, anmeldung_id INT DEFAULT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', request_data LONGTEXT NOT NULL, ip_address VARCHAR(45) DEFAULT NULL, user_agent VARCHAR(255) DEFAULT NULL, request_uri VARCHAR(255) DEFAULT NULL, request_method VARCHAR(10) DEFAULT NULL, is_successful TINYINT(1) DEFAULT NULL, INDEX IDX_C8C0BC4C1375A7A2 (ruestzeit_id), INDEX IDX_C8C0BC4C42CC4BD9 (anmeldung_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE protocol ADD CONSTRAINT FK_C8C0BC4C1375A7A2 FOREIGN KEY (ruestzeit_id) REFERENCES ruestzeit (id)');
        $this->addSql('ALTER TABLE protocol ADD CONSTRAINT FK_C8C0BC4C42CC4BD9 FOREIGN KEY (anmeldung_id) REFERENCES anmeldung (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE protocol DROP FOREIGN KEY FK_C8C0BC4C1375A7A2');
        $this->addSql('ALTER TABLE protocol DROP FOREIGN KEY FK_C8C0BC4C42CC4BD9');
        $this->addSql('DROP TABLE protocol');
    }
}
