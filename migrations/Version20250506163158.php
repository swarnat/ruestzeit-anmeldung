<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250506163158 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE TABLE mail_attachment (id INT AUTO_INCREMENT NOT NULL, ruestzeit_id INT NOT NULL, uuid BINARY(16) NOT NULL COMMENT '(DC2Type:uuid)', filename VARCHAR(255) NOT NULL, s3_key VARCHAR(255) NOT NULL, mime_type VARCHAR(255) NOT NULL, created_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)', INDEX IDX_AD9C33471375A7A2 (ruestzeit_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE mail_attachment ADD CONSTRAINT FK_AD9C33471375A7A2 FOREIGN KEY (ruestzeit_id) REFERENCES ruestzeit (id)
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE mail_attachment DROP FOREIGN KEY FK_AD9C33471375A7A2
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE mail_attachment
        SQL);
    }
}
