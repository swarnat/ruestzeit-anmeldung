<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Migration to populate mail_attachment_click table with existing data
 */
final class Version20250507162900 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Migrate existing clicked attachment data to the new per-mail tracking structure';
    }

    public function up(Schema $schema): void
    {
        // Find all clicked attachments
        $clickedAttachments = $this->connection->fetchAllAssociative(
            'SELECT id, clicked_at FROM mail_attachment WHERE clicked = 1'
        );

        // For each clicked attachment, find all associated mails and create a click record
        foreach ($clickedAttachments as $attachment) {
            $attachmentId = $attachment['id'];
            $clickedAt = $attachment['clicked_at'];
            
            // Get all mails associated with this attachment
            $mails = $this->connection->fetchAllAssociative(
                'SELECT mail_id FROM mail_mail_attachment WHERE mail_attachment_id = ?',
                [$attachmentId]
            );
            
            foreach ($mails as $mail) {
                $mailId = $mail['mail_id'];
                
                // Insert a click record for this mail-attachment pair
                $this->addSql(
                    'INSERT IGNORE INTO mail_attachment_click (mail_id, attachment_id, clicked, clicked_at) VALUES (?, ?, 1, ?)',
                    [$mailId, $attachmentId, $clickedAt]
                );
            }
        }
    }

    public function down(Schema $schema): void
    {
        // No down migration needed as we're just populating data
        $this->addSql('SELECT 1');
    }
}