<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220525095202 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE produit DROP FOREIGN KEY FK_29A5EC277C14DF52');
        $this->addSql('DROP INDEX IDX_29A5EC277C14DF52 ON produit');
        $this->addSql('ALTER TABLE produit DROP reception_id');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE produit ADD reception_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE produit ADD CONSTRAINT FK_29A5EC277C14DF52 FOREIGN KEY (reception_id) REFERENCES reception (id)');
        $this->addSql('CREATE INDEX IDX_29A5EC277C14DF52 ON produit (reception_id)');
    }
}
