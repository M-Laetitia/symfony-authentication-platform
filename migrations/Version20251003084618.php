<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251003084618 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE TABLE article (id INT AUTO_INCREMENT NOT NULL, author_id INT NOT NULL, category_id INT DEFAULT NULL, title VARCHAR(150) NOT NULL, created_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)', slug VARCHAR(150) NOT NULL, status VARCHAR(20) NOT NULL, content JSON DEFAULT NULL, excerpt LONGTEXT DEFAULT NULL, meta_title VARCHAR(255) NOT NULL, meta_description LONGTEXT NOT NULL, UNIQUE INDEX UNIQ_23A0E662B36786B (title), UNIQUE INDEX UNIQ_23A0E66989D9B62 (slug), INDEX IDX_23A0E66F675F31B (author_id), INDEX IDX_23A0E6612469DE2 (category_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE article_edit_history (id INT AUTO_INCREMENT NOT NULL, article_id INT NOT NULL, last_edit_by_id INT NOT NULL, edited_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)', old_content JSON NOT NULL, new_content JSON NOT NULL, INDEX IDX_4E08BC607294869C (article_id), INDEX IDX_4E08BC607A213F93 (last_edit_by_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE category (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(50) NOT NULL, slug VARCHAR(50) NOT NULL, description LONGTEXT DEFAULT NULL, UNIQUE INDEX UNIQ_64C19C15E237E06 (name), UNIQUE INDEX UNIQ_64C19C1989D9B62 (slug), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE comment (id INT AUTO_INCREMENT NOT NULL, author_id INT DEFAULT NULL, article_id INT NOT NULL, parent_comment_id INT DEFAULT NULL, last_edit_by_id INT DEFAULT NULL, content LONGTEXT NOT NULL, created_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)', edited_at DATETIME DEFAULT NULL COMMENT '(DC2Type:datetime_immutable)', author_name VARCHAR(80) DEFAULT NULL, is_approved TINYINT(1) DEFAULT NULL, ip_hash VARCHAR(64) DEFAULT NULL, INDEX IDX_9474526CF675F31B (author_id), INDEX IDX_9474526C7294869C (article_id), INDEX IDX_9474526CBF2AF943 (parent_comment_id), INDEX IDX_9474526C7A213F93 (last_edit_by_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE tag (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(30) NOT NULL, slug VARCHAR(30) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE tag_article (tag_id INT NOT NULL, article_id INT NOT NULL, INDEX IDX_300B23CCBAD26311 (tag_id), INDEX IDX_300B23CC7294869C (article_id), PRIMARY KEY(tag_id, article_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE article ADD CONSTRAINT FK_23A0E66F675F31B FOREIGN KEY (author_id) REFERENCES user (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE article ADD CONSTRAINT FK_23A0E6612469DE2 FOREIGN KEY (category_id) REFERENCES category (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE article_edit_history ADD CONSTRAINT FK_4E08BC607294869C FOREIGN KEY (article_id) REFERENCES article (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE article_edit_history ADD CONSTRAINT FK_4E08BC607A213F93 FOREIGN KEY (last_edit_by_id) REFERENCES user (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE comment ADD CONSTRAINT FK_9474526CF675F31B FOREIGN KEY (author_id) REFERENCES user (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE comment ADD CONSTRAINT FK_9474526C7294869C FOREIGN KEY (article_id) REFERENCES article (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE comment ADD CONSTRAINT FK_9474526CBF2AF943 FOREIGN KEY (parent_comment_id) REFERENCES comment (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE comment ADD CONSTRAINT FK_9474526C7A213F93 FOREIGN KEY (last_edit_by_id) REFERENCES user (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE tag_article ADD CONSTRAINT FK_300B23CCBAD26311 FOREIGN KEY (tag_id) REFERENCES tag (id) ON DELETE CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE tag_article ADD CONSTRAINT FK_300B23CC7294869C FOREIGN KEY (article_id) REFERENCES article (id) ON DELETE CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE user ADD is_verified TINYINT(1) NOT NULL, ADD first_name VARCHAR(255) DEFAULT NULL, ADD last_name VARCHAR(255) DEFAULT NULL, ADD google_id VARCHAR(255) DEFAULT NULL, ADD username VARCHAR(30) DEFAULT NULL
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE article DROP FOREIGN KEY FK_23A0E66F675F31B
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE article DROP FOREIGN KEY FK_23A0E6612469DE2
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE article_edit_history DROP FOREIGN KEY FK_4E08BC607294869C
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE article_edit_history DROP FOREIGN KEY FK_4E08BC607A213F93
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE comment DROP FOREIGN KEY FK_9474526CF675F31B
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE comment DROP FOREIGN KEY FK_9474526C7294869C
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE comment DROP FOREIGN KEY FK_9474526CBF2AF943
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE comment DROP FOREIGN KEY FK_9474526C7A213F93
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE tag_article DROP FOREIGN KEY FK_300B23CCBAD26311
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE tag_article DROP FOREIGN KEY FK_300B23CC7294869C
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE article
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE article_edit_history
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE category
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE comment
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE tag
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE tag_article
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE user DROP is_verified, DROP first_name, DROP last_name, DROP google_id, DROP username
        SQL);
    }
}
