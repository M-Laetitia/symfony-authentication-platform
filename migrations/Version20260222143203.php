<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260222143203 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE message ADD service_proposal_id INT DEFAULT NULL, CHANGE content content LONGTEXT DEFAULT NULL');
        $this->addSql('ALTER TABLE message ADD CONSTRAINT FK_B6BD307F38E306F4 FOREIGN KEY (service_proposal_id) REFERENCES service_proposal (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_B6BD307F38E306F4 ON message (service_proposal_id)');
        $this->addSql('ALTER TABLE service_proposal ADD conversation_id INT NOT NULL');
        $this->addSql('ALTER TABLE service_proposal ADD CONSTRAINT FK_4693CF4F9AC0396 FOREIGN KEY (conversation_id) REFERENCES conversation (id)');
        $this->addSql('CREATE INDEX IDX_4693CF4F9AC0396 ON service_proposal (conversation_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE message DROP FOREIGN KEY FK_B6BD307F38E306F4');
        $this->addSql('DROP INDEX UNIQ_B6BD307F38E306F4 ON message');
        $this->addSql('ALTER TABLE message DROP service_proposal_id, CHANGE content content LONGTEXT NOT NULL');
        $this->addSql('ALTER TABLE service_proposal DROP FOREIGN KEY FK_4693CF4F9AC0396');
        $this->addSql('DROP INDEX IDX_4693CF4F9AC0396 ON service_proposal');
        $this->addSql('ALTER TABLE service_proposal DROP conversation_id');
    }
}
