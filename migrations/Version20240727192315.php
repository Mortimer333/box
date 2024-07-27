<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20240727192315 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create BankAccount and Transaction';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE bank_account (
          id INT AUTO_INCREMENT NOT NULL, 
          owner_id INT NOT NULL, 
          version INT DEFAULT 1 NOT NULL, 
          currency VARCHAR(255) NOT NULL, 
          credit DOUBLE PRECISION NOT NULL, 
          account_number VARCHAR(255) NOT NULL, 
          reserved DOUBLE PRECISION DEFAULT NULL, 
          INDEX IDX_53A23E0A7E3C61F9 (owner_id), 
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE transaction (
          id INT AUTO_INCREMENT NOT NULL, 
          sender_id INT NOT NULL, 
          amount DOUBLE PRECISION NOT NULL, 
          receiver_account_number VARCHAR(255) NOT NULL, 
          title VARCHAR(255) NOT NULL, 
          receiver VARCHAR(255) NOT NULL, 
          address VARCHAR(255) DEFAULT NULL, 
          type VARCHAR(255) NOT NULL, 
          created DATETIME NOT NULL, 
          status VARCHAR(255) NOT NULL, 
          retries INT NOT NULL, 
          INDEX IDX_723705D1F624B39D (sender_id), 
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE bank_account ADD CONSTRAINT FK_53A23E0A7E3C61F9 
            FOREIGN KEY (owner_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE transaction ADD CONSTRAINT FK_723705D1F624B39D 
            FOREIGN KEY (sender_id) REFERENCES bank_account (id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE bank_account DROP FOREIGN KEY FK_53A23E0A7E3C61F9');
        $this->addSql('ALTER TABLE transaction DROP FOREIGN KEY FK_723705D1F624B39D');
        $this->addSql('DROP TABLE bank_account');
        $this->addSql('DROP TABLE transaction');
    }
}
