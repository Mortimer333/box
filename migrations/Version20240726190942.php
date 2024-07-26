<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20240726190942 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Generate User table';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE user (
          id INT AUTO_INCREMENT NOT NULL, 
          email VARCHAR(255) NOT NULL, 
          firstname VARCHAR(255) NOT NULL, 
          surname VARCHAR(255) NOT NULL, 
          password VARCHAR(255) NOT NULL, 
          roles JSON NOT NULL, 
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE user');
    }
}
