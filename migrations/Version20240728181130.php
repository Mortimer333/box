<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20240728181130 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Remove retry column and add commission fee in Transaction';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE transaction ADD commission_fee DOUBLE PRECISION NOT NULL, DROP retries');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE transaction ADD retries INT NOT NULL, DROP commission_fee');
    }
}
