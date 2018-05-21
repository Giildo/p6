<?php declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20180521165152 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE p6_tricks DROP FOREIGN KEY FK_D08EB7BD4CA2C340');
        $this->addSql('ALTER TABLE p6_tricks ADD CONSTRAINT FK_D08EB7BD4CA2C340 FOREIGN KEY (head_picture_id) REFERENCES p6_picture (id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE p6_tricks DROP FOREIGN KEY FK_D08EB7BD4CA2C340');
        $this->addSql('ALTER TABLE p6_tricks ADD CONSTRAINT FK_D08EB7BD4CA2C340 FOREIGN KEY (head_picture_id) REFERENCES p6_picture (id) ON DELETE CASCADE');
    }
}
