<?php declare(strict_types = 1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20180423223655 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE p6_status (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(30) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE p6_category (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(30) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE p6_tricks (id INT AUTO_INCREMENT NOT NULL, category_id INT NOT NULL, user_id INT NOT NULL, name VARCHAR(30) NOT NULL, slug VARCHAR(30) NOT NULL, description LONGTEXT NOT NULL, published TINYINT(1) NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, UNIQUE INDEX UNIQ_D08EB7BD5E237E06 (name), UNIQUE INDEX UNIQ_D08EB7BD989D9B62 (slug), INDEX IDX_D08EB7BD12469DE2 (category_id), INDEX IDX_D08EB7BDA76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE p6_user (id INT AUTO_INCREMENT NOT NULL, status_id INT NOT NULL, pseudo VARCHAR(30) NOT NULL, password VARCHAR(128) NOT NULL, first_name VARCHAR(40) NOT NULL, last_name VARCHAR(40) NOT NULL, mail VARCHAR(40) NOT NULL, phone VARCHAR(10) DEFAULT NULL, valid TINYINT(1) NOT NULL, INDEX IDX_B5B3BAAA6BF700BD (status_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE p6_comment (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, trick_id INT NOT NULL, comment LONGTEXT NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, INDEX IDX_CDF638CEA76ED395 (user_id), INDEX IDX_CDF638CEB281BE2E (trick_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE p6_tricks ADD CONSTRAINT FK_D08EB7BD12469DE2 FOREIGN KEY (category_id) REFERENCES p6_category (id)');
        $this->addSql('ALTER TABLE p6_tricks ADD CONSTRAINT FK_D08EB7BDA76ED395 FOREIGN KEY (user_id) REFERENCES p6_user (id)');
        $this->addSql('ALTER TABLE p6_user ADD CONSTRAINT FK_B5B3BAAA6BF700BD FOREIGN KEY (status_id) REFERENCES p6_status (id)');
        $this->addSql('ALTER TABLE p6_comment ADD CONSTRAINT FK_CDF638CEA76ED395 FOREIGN KEY (user_id) REFERENCES p6_user (id)');
        $this->addSql('ALTER TABLE p6_comment ADD CONSTRAINT FK_CDF638CEB281BE2E FOREIGN KEY (trick_id) REFERENCES p6_tricks (id)');
    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE p6_user DROP FOREIGN KEY FK_B5B3BAAA6BF700BD');
        $this->addSql('ALTER TABLE p6_tricks DROP FOREIGN KEY FK_D08EB7BD12469DE2');
        $this->addSql('ALTER TABLE p6_comment DROP FOREIGN KEY FK_CDF638CEB281BE2E');
        $this->addSql('ALTER TABLE p6_tricks DROP FOREIGN KEY FK_D08EB7BDA76ED395');
        $this->addSql('ALTER TABLE p6_comment DROP FOREIGN KEY FK_CDF638CEA76ED395');
        $this->addSql('DROP TABLE p6_status');
        $this->addSql('DROP TABLE p6_category');
        $this->addSql('DROP TABLE p6_tricks');
        $this->addSql('DROP TABLE p6_user');
        $this->addSql('DROP TABLE p6_comment');
    }
}
