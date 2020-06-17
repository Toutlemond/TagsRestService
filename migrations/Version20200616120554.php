<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200616120554 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE tasks ADD COLUMN ready INTEGER DEFAULT NULL');
        $this->addSql('ALTER TABLE tasks ADD COLUMN answer CLOB DEFAULT NULL');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TEMPORARY TABLE __temp__tasks AS SELECT id, token, url FROM tasks');
        $this->addSql('DROP TABLE tasks');
        $this->addSql('CREATE TABLE tasks (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, token VARCHAR(255) NOT NULL, url CLOB NOT NULL)');
        $this->addSql('INSERT INTO tasks (id, token, url) SELECT id, token, url FROM __temp__tasks');
        $this->addSql('DROP TABLE __temp__tasks');
    }
}
