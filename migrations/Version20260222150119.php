<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260222150119 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE tax (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(30) NOT NULL, active TINYINT(1) NOT NULL, rate DOUBLE PRECISION NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE service_proposal ADD tax_id INT NOT NULL, ADD price_exclu_tax DOUBLE PRECISION NOT NULL');
        $this->addSql('ALTER TABLE service_proposal ADD CONSTRAINT FK_4693CF4FB2A824D8 FOREIGN KEY (tax_id) REFERENCES tax (id)');
        $this->addSql('CREATE INDEX IDX_4693CF4FB2A824D8 ON service_proposal (tax_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE service_proposal DROP FOREIGN KEY FK_4693CF4FB2A824D8');
        $this->addSql('DROP TABLE tax');
        $this->addSql('DROP INDEX IDX_4693CF4FB2A824D8 ON service_proposal');
        $this->addSql('ALTER TABLE service_proposal DROP tax_id, DROP price_exclu_tax');
    }
}
