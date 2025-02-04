<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250204173941 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE ruestzeit_shared_admins (ruestzeit_id INT NOT NULL, admin_id INT NOT NULL, INDEX IDX_AD4CC5E71375A7A2 (ruestzeit_id), INDEX IDX_AD4CC5E7642B8210 (admin_id), PRIMARY KEY(ruestzeit_id, admin_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE ruestzeit_share_invitation (id INT AUTO_INCREMENT NOT NULL, ruestzeit_id INT NOT NULL, email VARCHAR(255) NOT NULL, token VARCHAR(255) NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', accepted_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', UNIQUE INDEX UNIQ_4E63A72C5F37A13B (token), INDEX IDX_4E63A72C1375A7A2 (ruestzeit_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE ruestzeit_shared_admins ADD CONSTRAINT FK_AD4CC5E71375A7A2 FOREIGN KEY (ruestzeit_id) REFERENCES ruestzeit (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE ruestzeit_shared_admins ADD CONSTRAINT FK_AD4CC5E7642B8210 FOREIGN KEY (admin_id) REFERENCES admin (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE ruestzeit_share_invitation ADD CONSTRAINT FK_4E63A72C1375A7A2 FOREIGN KEY (ruestzeit_id) REFERENCES ruestzeit (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE ruestzeit_shared_admins DROP FOREIGN KEY FK_AD4CC5E71375A7A2');
        $this->addSql('ALTER TABLE ruestzeit_shared_admins DROP FOREIGN KEY FK_AD4CC5E7642B8210');
        $this->addSql('ALTER TABLE ruestzeit_share_invitation DROP FOREIGN KEY FK_4E63A72C1375A7A2');
        $this->addSql('DROP TABLE ruestzeit_shared_admins');
        $this->addSql('DROP TABLE ruestzeit_share_invitation');
    }
}
