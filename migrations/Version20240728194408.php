<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20240728194408 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add transaction currency';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE transaction ADD currency VARCHAR(255) NOT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE transaction DROP currency');
    }
}
