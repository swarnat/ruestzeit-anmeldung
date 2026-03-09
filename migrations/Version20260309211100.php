<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260309211100 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Adds options_element_css_class to custom_field';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE custom_field ADD options_element_css_class VARCHAR(255) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE custom_field DROP options_element_css_class');
    }
}

