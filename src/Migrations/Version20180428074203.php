<?php declare(strict_types = 1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20180428074203 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE p6_picture (id INT AUTO_INCREMENT NOT NULL, alt VARCHAR(255) NOT NULL, name VARCHAR(30) NOT NULL, ext VARCHAR(3) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE p6_user ADD picture_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE p6_user ADD CONSTRAINT FK_B5B3BAAAEE45BDBF FOREIGN KEY (picture_id) REFERENCES p6_picture (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_B5B3BAAAEE45BDBF ON p6_user (picture_id)');
    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE p6_user DROP FOREIGN KEY FK_B5B3BAAAEE45BDBF');
        $this->addSql('DROP TABLE p6_picture');
        $this->addSql('DROP INDEX UNIQ_B5B3BAAAEE45BDBF ON p6_user');
        $this->addSql('ALTER TABLE p6_user DROP picture_id');
    }
}
