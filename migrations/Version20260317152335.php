<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260317152335 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE article (id INT AUTO_INCREMENT NOT NULL, author_id INT NOT NULL, category_id INT DEFAULT NULL, title VARCHAR(150) NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', slug VARCHAR(150) NOT NULL, status VARCHAR(20) NOT NULL, content JSON DEFAULT NULL, excerpt LONGTEXT DEFAULT NULL, meta_title VARCHAR(255) NOT NULL, meta_description LONGTEXT NOT NULL, UNIQUE INDEX UNIQ_23A0E662B36786B (title), UNIQUE INDEX UNIQ_23A0E66989D9B62 (slug), INDEX IDX_23A0E66F675F31B (author_id), INDEX IDX_23A0E6612469DE2 (category_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE article_edit_history (id INT AUTO_INCREMENT NOT NULL, article_id INT NOT NULL, last_edit_by_id INT NOT NULL, edited_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', old_content JSON NOT NULL, new_content JSON NOT NULL, INDEX IDX_4E08BC607294869C (article_id), INDEX IDX_4E08BC607A213F93 (last_edit_by_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE cancellation (id INT AUTO_INCREMENT NOT NULL, order_proposal_id INT DEFAULT NULL, invoice_id INT NOT NULL, requested_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', reason LONGTEXT DEFAULT NULL, reason_type VARCHAR(255) NOT NULL, cancellation_number VARCHAR(30) NOT NULL, UNIQUE INDEX UNIQ_FBCE5D0CEFCB5FDE (order_proposal_id), UNIQUE INDEX UNIQ_FBCE5D0C2989F1FD (invoice_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE category (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(50) NOT NULL, slug VARCHAR(50) NOT NULL, description LONGTEXT DEFAULT NULL, UNIQUE INDEX UNIQ_64C19C15E237E06 (name), UNIQUE INDEX UNIQ_64C19C1989D9B62 (slug), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE comment (id INT AUTO_INCREMENT NOT NULL, author_id INT DEFAULT NULL, article_id INT NOT NULL, parent_comment_id INT DEFAULT NULL, last_edit_by_id INT DEFAULT NULL, content LONGTEXT NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', edited_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', author_name VARCHAR(80) DEFAULT NULL, is_approved TINYINT(1) DEFAULT NULL, ip_hash VARCHAR(64) DEFAULT NULL, INDEX IDX_9474526CF675F31B (author_id), INDEX IDX_9474526C7294869C (article_id), INDEX IDX_9474526CBF2AF943 (parent_comment_id), INDEX IDX_9474526C7A213F93 (last_edit_by_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE conversation (id INT AUTO_INCREMENT NOT NULL, client_id INT DEFAULT NULL, photographer_id INT DEFAULT NULL, creation_date DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', status LONGTEXT NOT NULL COMMENT \'(DC2Type:simple_array)\', is_frozen TINYINT(1) NOT NULL, INDEX IDX_8A8E26E919EB6921 (client_id), INDEX IDX_8A8E26E953EC1A21 (photographer_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE invoice (id INT AUTO_INCREMENT NOT NULL, order_proposal_id INT NOT NULL, issued_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', status VARCHAR(255) NOT NULL, seller_snapshot JSON NOT NULL, buyer_snapshot JSON NOT NULL, total_amount INT NOT NULL, pdf_path VARCHAR(255) NOT NULL, is_archived TINYINT(1) DEFAULT NULL, invoice_number VARCHAR(30) NOT NULL, order_snapshot JSON NOT NULL, payment_snapshot JSON NOT NULL, UNIQUE INDEX UNIQ_906517442DA68207 (invoice_number), UNIQUE INDEX UNIQ_90651744EFCB5FDE (order_proposal_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE media (id INT AUTO_INCREMENT NOT NULL, article_id INT DEFAULT NULL, path VARCHAR(255) NOT NULL, alt_text VARCHAR(255) DEFAULT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', type_image VARCHAR(255) NOT NULL, caption LONGTEXT DEFAULT NULL, width VARCHAR(5) DEFAULT NULL, height VARCHAR(5) DEFAULT NULL, INDEX IDX_6A2CA10C7294869C (article_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE message (id INT AUTO_INCREMENT NOT NULL, conversation_id INT NOT NULL, sender_id INT NOT NULL, service_proposal_id INT DEFAULT NULL, content LONGTEXT DEFAULT NULL, creation_date DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', is_reported TINYINT(1) DEFAULT NULL, report_reason VARCHAR(255) DEFAULT NULL, status VARCHAR(255) NOT NULL, INDEX IDX_B6BD307F9AC0396 (conversation_id), INDEX IDX_B6BD307FF624B39D (sender_id), UNIQUE INDEX UNIQ_B6BD307F38E306F4 (service_proposal_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE `order` (id INT AUTO_INCREMENT NOT NULL, service_proposal_id INT NOT NULL, client_id INT NOT NULL, order_number VARCHAR(30) NOT NULL, status VARCHAR(255) NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', service_snapshot JSON NOT NULL, terms_accepted_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', note LONGTEXT DEFAULT NULL, total_amount INT NOT NULL, UNIQUE INDEX UNIQ_F5299398551F0F81 (order_number), UNIQUE INDEX UNIQ_F529939838E306F4 (service_proposal_id), INDEX IDX_F529939819EB6921 (client_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE payment (id INT AUTO_INCREMENT NOT NULL, order_proposal_id INT NOT NULL, amount INT NOT NULL, paid_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', provider VARCHAR(255) NOT NULL, currency VARCHAR(10) DEFAULT NULL, status VARCHAR(255) NOT NULL, transaction_id VARCHAR(255) DEFAULT NULL, billing_address JSON NOT NULL, UNIQUE INDEX UNIQ_6D28840DEFCB5FDE (order_proposal_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE photographer (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, first_name VARCHAR(30) DEFAULT NULL, last_name VARCHAR(30) DEFAULT NULL, slug VARCHAR(150) NOT NULL, email VARCHAR(255) DEFAULT NULL, created_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', profile JSON DEFAULT NULL, status LONGTEXT NOT NULL COMMENT \'(DC2Type:simple_array)\', visibility LONGTEXT NOT NULL COMMENT \'(DC2Type:simple_array)\', UNIQUE INDEX UNIQ_16337A7FA76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE reset_password_request (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, selector VARCHAR(20) NOT NULL, hashed_token VARCHAR(100) NOT NULL, requested_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', expires_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_7CE748AA76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE service_proposal (id INT AUTO_INCREMENT NOT NULL, photographer_id INT NOT NULL, client_id INT NOT NULL, conversation_id INT NOT NULL, tax_id INT NOT NULL, title VARCHAR(255) NOT NULL, message LONGTEXT NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', expiration_date DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', status VARCHAR(255) NOT NULL, price_exclu_tax DOUBLE PRECISION NOT NULL, INDEX IDX_4693CF4F53EC1A21 (photographer_id), INDEX IDX_4693CF4F19EB6921 (client_id), INDEX IDX_4693CF4F9AC0396 (conversation_id), INDEX IDX_4693CF4FB2A824D8 (tax_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE tag (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(30) NOT NULL, slug VARCHAR(30) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE tag_article (tag_id INT NOT NULL, article_id INT NOT NULL, INDEX IDX_300B23CCBAD26311 (tag_id), INDEX IDX_300B23CC7294869C (article_id), PRIMARY KEY(tag_id, article_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE tax (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(30) NOT NULL, active TINYINT(1) NOT NULL, rate DOUBLE PRECISION NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE user (id INT AUTO_INCREMENT NOT NULL, avatar_id INT DEFAULT NULL, email VARCHAR(180) NOT NULL, roles JSON NOT NULL, password VARCHAR(255) NOT NULL, is_verified TINYINT(1) NOT NULL, first_name VARCHAR(255) DEFAULT NULL, last_name VARCHAR(255) DEFAULT NULL, google_id VARCHAR(255) DEFAULT NULL, username VARCHAR(30) NOT NULL, UNIQUE INDEX UNIQ_8D93D649F85E0677 (username), UNIQUE INDEX UNIQ_8D93D64986383B10 (avatar_id), UNIQUE INDEX UNIQ_IDENTIFIER_EMAIL (email), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE messenger_messages (id BIGINT AUTO_INCREMENT NOT NULL, body LONGTEXT NOT NULL, headers LONGTEXT NOT NULL, queue_name VARCHAR(190) NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', available_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', delivered_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_75EA56E0FB7336F0 (queue_name), INDEX IDX_75EA56E0E3BD61CE (available_at), INDEX IDX_75EA56E016BA31DB (delivered_at), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE article ADD CONSTRAINT FK_23A0E66F675F31B FOREIGN KEY (author_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE article ADD CONSTRAINT FK_23A0E6612469DE2 FOREIGN KEY (category_id) REFERENCES category (id)');
        $this->addSql('ALTER TABLE article_edit_history ADD CONSTRAINT FK_4E08BC607294869C FOREIGN KEY (article_id) REFERENCES article (id)');
        $this->addSql('ALTER TABLE article_edit_history ADD CONSTRAINT FK_4E08BC607A213F93 FOREIGN KEY (last_edit_by_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE cancellation ADD CONSTRAINT FK_FBCE5D0CEFCB5FDE FOREIGN KEY (order_proposal_id) REFERENCES `order` (id)');
        $this->addSql('ALTER TABLE cancellation ADD CONSTRAINT FK_FBCE5D0C2989F1FD FOREIGN KEY (invoice_id) REFERENCES invoice (id)');
        $this->addSql('ALTER TABLE comment ADD CONSTRAINT FK_9474526CF675F31B FOREIGN KEY (author_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE comment ADD CONSTRAINT FK_9474526C7294869C FOREIGN KEY (article_id) REFERENCES article (id)');
        $this->addSql('ALTER TABLE comment ADD CONSTRAINT FK_9474526CBF2AF943 FOREIGN KEY (parent_comment_id) REFERENCES comment (id)');
        $this->addSql('ALTER TABLE comment ADD CONSTRAINT FK_9474526C7A213F93 FOREIGN KEY (last_edit_by_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE conversation ADD CONSTRAINT FK_8A8E26E919EB6921 FOREIGN KEY (client_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE conversation ADD CONSTRAINT FK_8A8E26E953EC1A21 FOREIGN KEY (photographer_id) REFERENCES photographer (id)');
        $this->addSql('ALTER TABLE invoice ADD CONSTRAINT FK_90651744EFCB5FDE FOREIGN KEY (order_proposal_id) REFERENCES `order` (id)');
        $this->addSql('ALTER TABLE media ADD CONSTRAINT FK_6A2CA10C7294869C FOREIGN KEY (article_id) REFERENCES article (id)');
        $this->addSql('ALTER TABLE message ADD CONSTRAINT FK_B6BD307F9AC0396 FOREIGN KEY (conversation_id) REFERENCES conversation (id)');
        $this->addSql('ALTER TABLE message ADD CONSTRAINT FK_B6BD307FF624B39D FOREIGN KEY (sender_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE message ADD CONSTRAINT FK_B6BD307F38E306F4 FOREIGN KEY (service_proposal_id) REFERENCES service_proposal (id)');
        $this->addSql('ALTER TABLE `order` ADD CONSTRAINT FK_F529939838E306F4 FOREIGN KEY (service_proposal_id) REFERENCES service_proposal (id)');
        $this->addSql('ALTER TABLE `order` ADD CONSTRAINT FK_F529939819EB6921 FOREIGN KEY (client_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE payment ADD CONSTRAINT FK_6D28840DEFCB5FDE FOREIGN KEY (order_proposal_id) REFERENCES `order` (id)');
        $this->addSql('ALTER TABLE photographer ADD CONSTRAINT FK_16337A7FA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE reset_password_request ADD CONSTRAINT FK_7CE748AA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE service_proposal ADD CONSTRAINT FK_4693CF4F53EC1A21 FOREIGN KEY (photographer_id) REFERENCES photographer (id)');
        $this->addSql('ALTER TABLE service_proposal ADD CONSTRAINT FK_4693CF4F19EB6921 FOREIGN KEY (client_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE service_proposal ADD CONSTRAINT FK_4693CF4F9AC0396 FOREIGN KEY (conversation_id) REFERENCES conversation (id)');
        $this->addSql('ALTER TABLE service_proposal ADD CONSTRAINT FK_4693CF4FB2A824D8 FOREIGN KEY (tax_id) REFERENCES tax (id)');
        $this->addSql('ALTER TABLE tag_article ADD CONSTRAINT FK_300B23CCBAD26311 FOREIGN KEY (tag_id) REFERENCES tag (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE tag_article ADD CONSTRAINT FK_300B23CC7294869C FOREIGN KEY (article_id) REFERENCES article (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE user ADD CONSTRAINT FK_8D93D64986383B10 FOREIGN KEY (avatar_id) REFERENCES media (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE article DROP FOREIGN KEY FK_23A0E66F675F31B');
        $this->addSql('ALTER TABLE article DROP FOREIGN KEY FK_23A0E6612469DE2');
        $this->addSql('ALTER TABLE article_edit_history DROP FOREIGN KEY FK_4E08BC607294869C');
        $this->addSql('ALTER TABLE article_edit_history DROP FOREIGN KEY FK_4E08BC607A213F93');
        $this->addSql('ALTER TABLE cancellation DROP FOREIGN KEY FK_FBCE5D0CEFCB5FDE');
        $this->addSql('ALTER TABLE cancellation DROP FOREIGN KEY FK_FBCE5D0C2989F1FD');
        $this->addSql('ALTER TABLE comment DROP FOREIGN KEY FK_9474526CF675F31B');
        $this->addSql('ALTER TABLE comment DROP FOREIGN KEY FK_9474526C7294869C');
        $this->addSql('ALTER TABLE comment DROP FOREIGN KEY FK_9474526CBF2AF943');
        $this->addSql('ALTER TABLE comment DROP FOREIGN KEY FK_9474526C7A213F93');
        $this->addSql('ALTER TABLE conversation DROP FOREIGN KEY FK_8A8E26E919EB6921');
        $this->addSql('ALTER TABLE conversation DROP FOREIGN KEY FK_8A8E26E953EC1A21');
        $this->addSql('ALTER TABLE invoice DROP FOREIGN KEY FK_90651744EFCB5FDE');
        $this->addSql('ALTER TABLE media DROP FOREIGN KEY FK_6A2CA10C7294869C');
        $this->addSql('ALTER TABLE message DROP FOREIGN KEY FK_B6BD307F9AC0396');
        $this->addSql('ALTER TABLE message DROP FOREIGN KEY FK_B6BD307FF624B39D');
        $this->addSql('ALTER TABLE message DROP FOREIGN KEY FK_B6BD307F38E306F4');
        $this->addSql('ALTER TABLE `order` DROP FOREIGN KEY FK_F529939838E306F4');
        $this->addSql('ALTER TABLE `order` DROP FOREIGN KEY FK_F529939819EB6921');
        $this->addSql('ALTER TABLE payment DROP FOREIGN KEY FK_6D28840DEFCB5FDE');
        $this->addSql('ALTER TABLE photographer DROP FOREIGN KEY FK_16337A7FA76ED395');
        $this->addSql('ALTER TABLE reset_password_request DROP FOREIGN KEY FK_7CE748AA76ED395');
        $this->addSql('ALTER TABLE service_proposal DROP FOREIGN KEY FK_4693CF4F53EC1A21');
        $this->addSql('ALTER TABLE service_proposal DROP FOREIGN KEY FK_4693CF4F19EB6921');
        $this->addSql('ALTER TABLE service_proposal DROP FOREIGN KEY FK_4693CF4F9AC0396');
        $this->addSql('ALTER TABLE service_proposal DROP FOREIGN KEY FK_4693CF4FB2A824D8');
        $this->addSql('ALTER TABLE tag_article DROP FOREIGN KEY FK_300B23CCBAD26311');
        $this->addSql('ALTER TABLE tag_article DROP FOREIGN KEY FK_300B23CC7294869C');
        $this->addSql('ALTER TABLE user DROP FOREIGN KEY FK_8D93D64986383B10');
        $this->addSql('DROP TABLE article');
        $this->addSql('DROP TABLE article_edit_history');
        $this->addSql('DROP TABLE cancellation');
        $this->addSql('DROP TABLE category');
        $this->addSql('DROP TABLE comment');
        $this->addSql('DROP TABLE conversation');
        $this->addSql('DROP TABLE invoice');
        $this->addSql('DROP TABLE media');
        $this->addSql('DROP TABLE message');
        $this->addSql('DROP TABLE `order`');
        $this->addSql('DROP TABLE payment');
        $this->addSql('DROP TABLE photographer');
        $this->addSql('DROP TABLE reset_password_request');
        $this->addSql('DROP TABLE service_proposal');
        $this->addSql('DROP TABLE tag');
        $this->addSql('DROP TABLE tag_article');
        $this->addSql('DROP TABLE tax');
        $this->addSql('DROP TABLE user');
        $this->addSql('DROP TABLE messenger_messages');
    }
}
