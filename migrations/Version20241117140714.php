<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241117140714 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    private function generateForwardKey() {
        $length = 6;
        $characters = 'ABCDEFGHKLMNPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
    
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[random_int(0, $charactersLength - 1)];
        }
    
        return $randomString;
    }

    public function up(Schema $schema): void
    {

        foreach ($this->connection->fetchAllAssociative('SELECT id, forwarder FROM ruestzeit') as $ruestzeit) {
            $this->addSql(
                'UPDATE ruestzeit SET forwarder = :forward_key WHERE id = :ruestzeit_id',
                array(
                    'forward_key' => $this->generateForwardKey(),
                    'ruestzeit_id' => $ruestzeit['id']
                )
            );
        }

        $this->addSql('CREATE UNIQUE INDEX UNIQ_2D4F16D321507808 ON ruestzeit (forwarder)');

    }

    public function down(Schema $schema): void
    {

    }
}
