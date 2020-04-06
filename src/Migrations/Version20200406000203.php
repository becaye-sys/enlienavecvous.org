<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200406000203 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE user ADD region_id INT DEFAULT NULL, ADD department_id INT DEFAULT NULL, ADD town_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE user ADD CONSTRAINT FK_8D93D64998260155 FOREIGN KEY (region_id) REFERENCES region (id)');
        $this->addSql('ALTER TABLE user ADD CONSTRAINT FK_8D93D649AE80F5DF FOREIGN KEY (department_id) REFERENCES department (id)');
        $this->addSql('ALTER TABLE user ADD CONSTRAINT FK_8D93D64975E23604 FOREIGN KEY (town_id) REFERENCES town (id)');
        $this->addSql('CREATE INDEX IDX_8D93D64998260155 ON user (region_id)');
        $this->addSql('CREATE INDEX IDX_8D93D649AE80F5DF ON user (department_id)');
        $this->addSql('CREATE INDEX IDX_8D93D64975E23604 ON user (town_id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE user DROP FOREIGN KEY FK_8D93D64998260155');
        $this->addSql('ALTER TABLE user DROP FOREIGN KEY FK_8D93D649AE80F5DF');
        $this->addSql('ALTER TABLE user DROP FOREIGN KEY FK_8D93D64975E23604');
        $this->addSql('DROP INDEX IDX_8D93D64998260155 ON user');
        $this->addSql('DROP INDEX IDX_8D93D649AE80F5DF ON user');
        $this->addSql('DROP INDEX IDX_8D93D64975E23604 ON user');
        $this->addSql('ALTER TABLE user DROP region_id, DROP department_id, DROP town_id');
    }
}
