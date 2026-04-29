<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260429073621 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE conversation_report DROP FOREIGN KEY FK_F6E3CD4471CE806');
        $this->addSql('ALTER TABLE conversation_report DROP FOREIGN KEY FK_F6E3CD449AC0396');
        $this->addSql('DROP TABLE conversation_report');
        $this->addSql('ALTER TABLE `order` DROP INDEX IDX_F529939838E306F4, ADD UNIQUE INDEX UNIQ_F529939838E306F4 (service_proposal_id)');
        $this->addSql('ALTER TABLE service_proposal DROP FOREIGN KEY FK_4693CF4F3B5A08D7');
        $this->addSql('DROP INDEX IDX_4693CF4F3B5A08D7 ON service_proposal');
        $this->addSql('ALTER TABLE service_proposal DROP speciality_id, DROP delivery_delay, DROP edited_photo_count, DROP start_at, DROP end_at, DROP service_date, DROP location, CHANGE price_exclu_tax price_exclu_tax DOUBLE PRECISION NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE conversation_report (id INT AUTO_INCREMENT NOT NULL, reported_by_id INT NOT NULL, conversation_id INT NOT NULL, reason VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, message_reference VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', admin_notes VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, status LONGTEXT CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci` COMMENT \'(DC2Type:simple_array)\', INDEX IDX_F6E3CD449AC0396 (conversation_id), INDEX IDX_F6E3CD4471CE806 (reported_by_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE conversation_report ADD CONSTRAINT FK_F6E3CD4471CE806 FOREIGN KEY (reported_by_id) REFERENCES user (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('ALTER TABLE conversation_report ADD CONSTRAINT FK_F6E3CD449AC0396 FOREIGN KEY (conversation_id) REFERENCES conversation (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('ALTER TABLE `order` DROP INDEX UNIQ_F529939838E306F4, ADD INDEX IDX_F529939838E306F4 (service_proposal_id)');
        $this->addSql('ALTER TABLE service_proposal ADD speciality_id INT DEFAULT NULL, ADD delivery_delay INT NOT NULL, ADD edited_photo_count INT NOT NULL, ADD start_at TIME NOT NULL COMMENT \'(DC2Type:time_immutable)\', ADD end_at TIME NOT NULL COMMENT \'(DC2Type:time_immutable)\', ADD service_date DATE DEFAULT NULL COMMENT \'(DC2Type:date_immutable)\', ADD location VARCHAR(255) NOT NULL, CHANGE price_exclu_tax price_exclu_tax NUMERIC(10, 2) NOT NULL');
        $this->addSql('ALTER TABLE service_proposal ADD CONSTRAINT FK_4693CF4F3B5A08D7 FOREIGN KEY (speciality_id) REFERENCES speciality (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('CREATE INDEX IDX_4693CF4F3B5A08D7 ON service_proposal (speciality_id)');
    }
}
