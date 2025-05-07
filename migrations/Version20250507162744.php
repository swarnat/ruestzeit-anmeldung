<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250507162744 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Creates mail_attachment_click table for tracking attachment clicks per email';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE TABLE mail_attachment_click (id INT AUTO_INCREMENT NOT NULL, mail_id INT NOT NULL, attachment_id INT NOT NULL, clicked TINYINT(1) NOT NULL, clicked_at DATETIME DEFAULT NULL COMMENT '(DC2Type:datetime_immutable)', INDEX IDX_E2244611C8776F01 (mail_id), INDEX IDX_E2244611464E68B (attachment_id), UNIQUE INDEX UNIQ_MAIL_ATTACHMENT (mail_id, attachment_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE mail_attachment_click ADD CONSTRAINT FK_E2244611C8776F01 FOREIGN KEY (mail_id) REFERENCES mail (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE mail_attachment_click ADD CONSTRAINT FK_E2244611464E68B FOREIGN KEY (attachment_id) REFERENCES mail_attachment (id)
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE mail_attachment_click DROP FOREIGN KEY FK_E2244611C8776F01
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE mail_attachment_click DROP FOREIGN KEY FK_E2244611464E68B
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE mail_attachment_click
        SQL);
    }
}
