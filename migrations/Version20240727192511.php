<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20240727192511 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add unique index on account number';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE UNIQUE INDEX account_number_uniq ON bank_account (account_number)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP INDEX account_number_uniq ON bank_account');
    }
}
