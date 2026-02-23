<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260223215410 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE service_proposal DROP FOREIGN KEY FK_4693CF4FD8BBBEC7');
        $this->addSql('CREATE TABLE photographer (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, first_name VARCHAR(30) DEFAULT NULL, last_name VARCHAR(30) DEFAULT NULL, UNIQUE INDEX UNIQ_16337A7FA76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE photographer ADD CONSTRAINT FK_16337A7FA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE photograph DROP FOREIGN KEY FK_D551C733A76ED395');
        $this->addSql('DROP TABLE photograph');
        $this->addSql('DROP INDEX IDX_4693CF4FD8BBBEC7 ON service_proposal');
        $this->addSql('ALTER TABLE service_proposal CHANGE photograph_id photographer_id INT NOT NULL');
        $this->addSql('ALTER TABLE service_proposal ADD CONSTRAINT FK_4693CF4F53EC1A21 FOREIGN KEY (photographer_id) REFERENCES photographer (id)');
        $this->addSql('CREATE INDEX IDX_4693CF4F53EC1A21 ON service_proposal (photographer_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE service_proposal DROP FOREIGN KEY FK_4693CF4F53EC1A21');
        $this->addSql('CREATE TABLE photograph (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, first_name VARCHAR(30) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, last_name VARCHAR(30) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, UNIQUE INDEX UNIQ_D551C733A76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE photograph ADD CONSTRAINT FK_D551C733A76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('ALTER TABLE photographer DROP FOREIGN KEY FK_16337A7FA76ED395');
        $this->addSql('DROP TABLE photographer');
        $this->addSql('DROP INDEX IDX_4693CF4F53EC1A21 ON service_proposal');
        $this->addSql('ALTER TABLE service_proposal CHANGE photographer_id photograph_id INT NOT NULL');
        $this->addSql('ALTER TABLE service_proposal ADD CONSTRAINT FK_4693CF4FD8BBBEC7 FOREIGN KEY (photograph_id) REFERENCES photograph (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('CREATE INDEX IDX_4693CF4FD8BBBEC7 ON service_proposal (photograph_id)');
    }
}
