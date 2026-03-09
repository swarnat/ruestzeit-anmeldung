<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260309210300 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Adds description, full_width and sequence fields to custom_field';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE custom_field ADD description LONGTEXT DEFAULT NULL, ADD full_width TINYINT(1) NOT NULL, ADD sequence INT DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE custom_field DROP description, DROP full_width, DROP sequence');
    }
}

