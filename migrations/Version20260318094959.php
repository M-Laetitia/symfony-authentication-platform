<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260318094959 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE media ADD photographer_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE media ADD CONSTRAINT FK_6A2CA10C53EC1A21 FOREIGN KEY (photographer_id) REFERENCES photographer (id)');
        $this->addSql('CREATE INDEX IDX_6A2CA10C53EC1A21 ON media (photographer_id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_16337A7F989D9B62 ON photographer (slug)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE media DROP FOREIGN KEY FK_6A2CA10C53EC1A21');
        $this->addSql('DROP INDEX IDX_6A2CA10C53EC1A21 ON media');
        $this->addSql('ALTER TABLE media DROP photographer_id');
        $this->addSql('DROP INDEX UNIQ_16337A7F989D9B62 ON photographer');
    }
}
