<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260511153629 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE pricing_plan (id INT AUTO_INCREMENT NOT NULL, photographer_id INT NOT NULL, price NUMERIC(8, 2) NOT NULL, description LONGTEXT NOT NULL, duration INT DEFAULT NULL, is_active TINYINT(1) DEFAULT 1 NOT NULL, what_included JSON NOT NULL, additionnal_infos JSON DEFAULT NULL, plan_type VARCHAR(255) NOT NULL, INDEX IDX_F64C51C353EC1A21 (photographer_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE pricing_plan ADD CONSTRAINT FK_F64C51C353EC1A21 FOREIGN KEY (photographer_id) REFERENCES photographer (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE pricing_plan DROP FOREIGN KEY FK_F64C51C353EC1A21');
        $this->addSql('DROP TABLE pricing_plan');
    }
}
