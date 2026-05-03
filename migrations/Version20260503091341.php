<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260503091341 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE article CHANGE status status VARCHAR(20) NOT NULL');
        $this->addSql('ALTER TABLE cancellation CHANGE reason_type reason_type VARCHAR(25) NOT NULL');
        $this->addSql('ALTER TABLE category CHANGE description description VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE comment CHANGE encrypt_ip encrypt_ip VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE conversation CHANGE status status VARCHAR(20) NOT NULL');
        // Add slug and created_at to gallery_series, drop type (handle existing rows)
        $this->addSql('ALTER TABLE gallery_series ADD slug VARCHAR(100) DEFAULT "default" NOT NULL, ADD created_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', DROP type, CHANGE name name VARCHAR(100) NOT NULL');
        $this->addSql('UPDATE gallery_series SET slug = CONCAT("gallery-", id) WHERE slug = "default"');
        $this->addSql('ALTER TABLE gallery_series MODIFY created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', MODIFY slug VARCHAR(100) NOT NULL');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_GALLERY_SLUG ON gallery_series (slug)');
        $this->addSql('ALTER TABLE invoice CHANGE status status VARCHAR(20) NOT NULL');
        $this->addSql('ALTER TABLE media CHANGE type_image type_image VARCHAR(30) NOT NULL, CHANGE caption caption VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE message DROP is_reported, DROP report_reason, CHANGE status status VARCHAR(20) NOT NULL');
        $this->addSql('ALTER TABLE `order` CHANGE status status VARCHAR(20) NOT NULL');
        $this->addSql('ALTER TABLE payment CHANGE provider provider VARCHAR(20) NOT NULL, CHANGE status status VARCHAR(25) NOT NULL');
        $this->addSql('ALTER TABLE photographer CHANGE status status VARCHAR(20) NOT NULL, CHANGE visibility visibility VARCHAR(20) NOT NULL');
        $this->addSql('ALTER TABLE service_proposal CHANGE status status VARCHAR(25) NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE article CHANGE status status VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE cancellation CHANGE reason_type reason_type VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE category CHANGE description description LONGTEXT DEFAULT NULL');
        $this->addSql('ALTER TABLE comment CHANGE encrypt_ip encrypt_ip VARCHAR(64) DEFAULT NULL');
        $this->addSql('ALTER TABLE conversation CHANGE status status LONGTEXT NOT NULL COMMENT \'(DC2Type:simple_array)\'');
        $this->addSql('DROP INDEX UNIQ_GALLERY_SLUG ON gallery_series');
        $this->addSql('ALTER TABLE gallery_series ADD type VARCHAR(255) NOT NULL, DROP slug, DROP created_at, CHANGE name name VARCHAR(125) NOT NULL');
        $this->addSql('ALTER TABLE invoice CHANGE status status VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE media CHANGE type_image type_image VARCHAR(255) NOT NULL, CHANGE caption caption LONGTEXT DEFAULT NULL');
        $this->addSql('ALTER TABLE message ADD is_reported TINYINT(1) DEFAULT NULL, ADD report_reason VARCHAR(255) DEFAULT NULL, CHANGE status status VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE `order` CHANGE status status VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE payment CHANGE provider provider VARCHAR(255) NOT NULL, CHANGE status status VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE photographer CHANGE status status LONGTEXT NOT NULL COMMENT \'(DC2Type:simple_array)\', CHANGE visibility visibility LONGTEXT NOT NULL COMMENT \'(DC2Type:simple_array)\'');
        $this->addSql('ALTER TABLE service_proposal CHANGE status status VARCHAR(255) NOT NULL');
    }
}
