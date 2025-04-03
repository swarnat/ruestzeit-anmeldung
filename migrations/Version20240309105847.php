<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240309105847 extends AbstractMigration
{
    
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE admin (id INT AUTO_INCREMENT NOT NULL, username VARCHAR(180) NOT NULL, roles JSON NOT NULL COMMENT \'(DC2Type:json)\', password VARCHAR(255) NOT NULL, UNIQUE INDEX UNIQ_IDENTIFIER_USERNAME (username), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_2D4F16D3989D9B62 ON ruestzeit (slug)');

    }

    public function postUp(Schema $schema): void
    {
        # ruestzeit-admin
        $password = '$2y$10$dnnaqeM0UzZ3wQ/bvJMJkelC5bm4o67XOW5yQWSn0rrQMvGVWuN1G';

        $this->connection->insert("admin", [
            "username" => "ruestzeit-admin",
            "password" => $password,
            "roles" => json_encode(["ROLE_ADMIN"])
        ]);

    }    



    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE admin');
        $this->addSql('DROP INDEX UNIQ_2D4F16D3989D9B62 ON ruestzeit');
    }
  
}
