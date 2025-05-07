<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Remove global clicked flags from mail_attachment table
 */
final class Version20250507163200 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Remove global clicked flags from mail_attachment table as they are now tracked per mail';
    }

    public function up(Schema $schema): void
    {
        // Remove the clicked and clicked_at columns from mail_attachment
        $this->addSql('ALTER TABLE mail_attachment DROP clicked, DROP clicked_at');
    }

    public function down(Schema $schema): void
    {
        // Add back the clicked and clicked_at columns if needed
        $this->addSql('ALTER TABLE mail_attachment ADD clicked TINYINT(1) NOT NULL, ADD clicked_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\'');
        
        // Restore the clicked status based on mail_attachment_click data
        $this->addSql(<<<'SQL'
            UPDATE mail_attachment ma
            SET ma.clicked = 1,
                ma.clicked_at = (
                    SELECT MIN(mac.clicked_at)
                    FROM mail_attachment_click mac
                    WHERE mac.attachment_id = ma.id
                    AND mac.clicked = 1
                    LIMIT 1
                )
            WHERE EXISTS (
                SELECT 1
                FROM mail_attachment_click mac
                WHERE mac.attachment_id = ma.id
                AND mac.clicked = 1
            )
        SQL);
    }
}