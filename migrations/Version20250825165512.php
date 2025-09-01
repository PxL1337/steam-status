<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250825165512 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP SEQUENCE steam_user_id_seq CASCADE');
        $this->addSql('ALTER TABLE steam_user DROP CONSTRAINT steam_user_pkey');
        $this->addSql('ALTER TABLE steam_user DROP id');
        $this->addSql('ALTER TABLE steam_user ADD PRIMARY KEY (steam_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('CREATE SEQUENCE steam_user_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('DROP INDEX steam_user_pkey');
        $this->addSql('ALTER TABLE steam_user ADD id SERIAL NOT NULL');
        $this->addSql('ALTER TABLE steam_user ADD PRIMARY KEY (id)');
    }
}
