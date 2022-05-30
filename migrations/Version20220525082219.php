<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220525082219 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE commande (id INT AUTO_INCREMENT NOT NULL, id_statut_id INT NOT NULL, id_utilisateur_id INT NOT NULL, numero_commande VARCHAR(255) NOT NULL, date_commande DATETIME NOT NULL, INDEX IDX_6EEAA67D76158423 (id_statut_id), INDEX IDX_6EEAA67DC6EE5C49 (id_utilisateur_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE conteneur (id INT AUTO_INCREMENT NOT NULL, id_reception_id INT NOT NULL, id_produit_id INT NOT NULL, id_stock_id INT NOT NULL, code_conteneur VARCHAR(20) NOT NULL, quantite INT NOT NULL, date_reception DATETIME NOT NULL, INDEX IDX_E9628FD25FD5AA20 (id_reception_id), INDEX IDX_E9628FD2AABEFE2C (id_produit_id), INDEX IDX_E9628FD25D168D85 (id_stock_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE flux (id INT AUTO_INCREMENT NOT NULL, type VARCHAR(255) NOT NULL, quantite INT NOT NULL, date_flux DATETIME NOT NULL, origine VARCHAR(255) DEFAULT NULL, adresse_stock VARCHAR(255) DEFAULT NULL, code_conteneur VARCHAR(20) DEFAULT NULL, part_number VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE ligne (id INT AUTO_INCREMENT NOT NULL, id_statut_id INT NOT NULL, id_produit_id INT NOT NULL, id_commande_id INT NOT NULL, quantite INT NOT NULL, INDEX IDX_57F0DB8376158423 (id_statut_id), INDEX IDX_57F0DB83AABEFE2C (id_produit_id), INDEX IDX_57F0DB839AF8E3A3 (id_commande_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE produit (id INT AUTO_INCREMENT NOT NULL, reception_id INT NOT NULL, part_number VARCHAR(20) NOT NULL, denomination VARCHAR(255) NOT NULL, fournisseur VARCHAR(255) DEFAULT NULL, description LONGTEXT DEFAULT NULL, prix_unitaire DOUBLE PRECISION NOT NULL, stock_min INT DEFAULT NULL, stock_max INT DEFAULT NULL, image LONGTEXT DEFAULT NULL, INDEX IDX_29A5EC277C14DF52 (reception_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE reception (id INT AUTO_INCREMENT NOT NULL, bon_de_commande INT NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE statut (id INT AUTO_INCREMENT NOT NULL, statut_denomination VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE stock (id INT AUTO_INCREMENT NOT NULL, adresse_stock VARCHAR(255) NOT NULL, multi_stockage TINYINT(1) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE utilisateur (id INT AUTO_INCREMENT NOT NULL, identifiant VARCHAR(180) NOT NULL, roles JSON NOT NULL, password VARCHAR(255) NOT NULL, nom VARCHAR(255) NOT NULL, prenom VARCHAR(255) NOT NULL, adresse LONGTEXT DEFAULT NULL, telephone VARCHAR(20) DEFAULT NULL, email LONGTEXT DEFAULT NULL, UNIQUE INDEX UNIQ_1D1C63B3C90409EC (identifiant), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE messenger_messages (id BIGINT AUTO_INCREMENT NOT NULL, body LONGTEXT NOT NULL, headers LONGTEXT NOT NULL, queue_name VARCHAR(190) NOT NULL, created_at DATETIME NOT NULL, available_at DATETIME NOT NULL, delivered_at DATETIME DEFAULT NULL, INDEX IDX_75EA56E0FB7336F0 (queue_name), INDEX IDX_75EA56E0E3BD61CE (available_at), INDEX IDX_75EA56E016BA31DB (delivered_at), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE commande ADD CONSTRAINT FK_6EEAA67D76158423 FOREIGN KEY (id_statut_id) REFERENCES statut (id)');
        $this->addSql('ALTER TABLE commande ADD CONSTRAINT FK_6EEAA67DC6EE5C49 FOREIGN KEY (id_utilisateur_id) REFERENCES utilisateur (id)');
        $this->addSql('ALTER TABLE conteneur ADD CONSTRAINT FK_E9628FD25FD5AA20 FOREIGN KEY (id_reception_id) REFERENCES reception (id)');
        $this->addSql('ALTER TABLE conteneur ADD CONSTRAINT FK_E9628FD2AABEFE2C FOREIGN KEY (id_produit_id) REFERENCES produit (id)');
        $this->addSql('ALTER TABLE conteneur ADD CONSTRAINT FK_E9628FD25D168D85 FOREIGN KEY (id_stock_id) REFERENCES stock (id)');
        $this->addSql('ALTER TABLE ligne ADD CONSTRAINT FK_57F0DB8376158423 FOREIGN KEY (id_statut_id) REFERENCES statut (id)');
        $this->addSql('ALTER TABLE ligne ADD CONSTRAINT FK_57F0DB83AABEFE2C FOREIGN KEY (id_produit_id) REFERENCES produit (id)');
        $this->addSql('ALTER TABLE ligne ADD CONSTRAINT FK_57F0DB839AF8E3A3 FOREIGN KEY (id_commande_id) REFERENCES commande (id)');
        $this->addSql('ALTER TABLE produit ADD CONSTRAINT FK_29A5EC277C14DF52 FOREIGN KEY (reception_id) REFERENCES reception (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE ligne DROP FOREIGN KEY FK_57F0DB839AF8E3A3');
        $this->addSql('ALTER TABLE conteneur DROP FOREIGN KEY FK_E9628FD2AABEFE2C');
        $this->addSql('ALTER TABLE ligne DROP FOREIGN KEY FK_57F0DB83AABEFE2C');
        $this->addSql('ALTER TABLE conteneur DROP FOREIGN KEY FK_E9628FD25FD5AA20');
        $this->addSql('ALTER TABLE produit DROP FOREIGN KEY FK_29A5EC277C14DF52');
        $this->addSql('ALTER TABLE commande DROP FOREIGN KEY FK_6EEAA67D76158423');
        $this->addSql('ALTER TABLE ligne DROP FOREIGN KEY FK_57F0DB8376158423');
        $this->addSql('ALTER TABLE conteneur DROP FOREIGN KEY FK_E9628FD25D168D85');
        $this->addSql('ALTER TABLE commande DROP FOREIGN KEY FK_6EEAA67DC6EE5C49');
        $this->addSql('DROP TABLE commande');
        $this->addSql('DROP TABLE conteneur');
        $this->addSql('DROP TABLE flux');
        $this->addSql('DROP TABLE ligne');
        $this->addSql('DROP TABLE produit');
        $this->addSql('DROP TABLE reception');
        $this->addSql('DROP TABLE statut');
        $this->addSql('DROP TABLE stock');
        $this->addSql('DROP TABLE utilisateur');
        $this->addSql('DROP TABLE messenger_messages');
    }
}
