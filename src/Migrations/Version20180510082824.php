<?php declare(strict_types = 1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20180510082824 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE p6_tricks ADD head_picture_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE p6_tricks ADD CONSTRAINT FK_D08EB7BD4CA2C340 FOREIGN KEY (head_picture_id) REFERENCES p6_picture (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_D08EB7BD4CA2C340 ON p6_tricks (head_picture_id)');
    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE p6_tricks DROP FOREIGN KEY FK_D08EB7BD4CA2C340');
        $this->addSql('DROP INDEX UNIQ_D08EB7BD4CA2C340 ON p6_tricks');
        $this->addSql('ALTER TABLE p6_tricks DROP head_picture_id');
    }
}
