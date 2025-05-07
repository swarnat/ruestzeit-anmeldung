<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250506205156 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE TABLE mail (id INT AUTO_INCREMENT NOT NULL, ruestzeit_id INT NOT NULL, sender_id INT DEFAULT NULL, recipient_id INT DEFAULT NULL, uuid BINARY(16) NOT NULL COMMENT '(DC2Type:uuid)', subject VARCHAR(255) NOT NULL, content LONGTEXT NOT NULL, recipient_email VARCHAR(255) NOT NULL, recipient_name VARCHAR(255) DEFAULT NULL, sender_email VARCHAR(255) NOT NULL, sender_name VARCHAR(255) DEFAULT NULL, sent_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)', opened TINYINT(1) NOT NULL, opened_at DATETIME DEFAULT NULL COMMENT '(DC2Type:datetime_immutable)', INDEX IDX_5126AC481375A7A2 (ruestzeit_id), INDEX IDX_5126AC48F624B39D (sender_id), INDEX IDX_5126AC48E92F8F78 (recipient_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE mail_mail_attachment (mail_id INT NOT NULL, mail_attachment_id INT NOT NULL, INDEX IDX_EF270745C8776F01 (mail_id), INDEX IDX_EF2707451E46E487 (mail_attachment_id), PRIMARY KEY(mail_id, mail_attachment_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE mail ADD CONSTRAINT FK_5126AC481375A7A2 FOREIGN KEY (ruestzeit_id) REFERENCES ruestzeit (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE mail ADD CONSTRAINT FK_5126AC48F624B39D FOREIGN KEY (sender_id) REFERENCES admin (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE mail ADD CONSTRAINT FK_5126AC48E92F8F78 FOREIGN KEY (recipient_id) REFERENCES anmeldung (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE mail_mail_attachment ADD CONSTRAINT FK_EF270745C8776F01 FOREIGN KEY (mail_id) REFERENCES mail (id) ON DELETE CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE mail_mail_attachment ADD CONSTRAINT FK_EF2707451E46E487 FOREIGN KEY (mail_attachment_id) REFERENCES mail_attachment (id) ON DELETE CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE mail_attachment ADD clicked TINYINT(1) NOT NULL, ADD clicked_at DATETIME DEFAULT NULL COMMENT '(DC2Type:datetime_immutable)'
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE mail DROP FOREIGN KEY FK_5126AC481375A7A2
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE mail DROP FOREIGN KEY FK_5126AC48F624B39D
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE mail DROP FOREIGN KEY FK_5126AC48E92F8F78
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE mail_mail_attachment DROP FOREIGN KEY FK_EF270745C8776F01
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE mail_mail_attachment DROP FOREIGN KEY FK_EF2707451E46E487
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE mail
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE mail_mail_attachment
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE mail_attachment DROP clicked, DROP clicked_at
        SQL);
    }
}
