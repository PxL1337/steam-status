<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250825173759 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE steam_user ADD persona_name VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE steam_user ADD avatar VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE steam_user ADD country VARCHAR(2) DEFAULT NULL');
        $this->addSql('ALTER TABLE steam_user ADD time_created INT DEFAULT NULL');
        $this->addSql('ALTER TABLE steam_user ADD vac_banned BOOLEAN DEFAULT false NOT NULL');
        $this->addSql('ALTER TABLE steam_user ADD vac_ban_count INT DEFAULT 0 NOT NULL');
        $this->addSql('ALTER TABLE steam_user ADD community_banned BOOLEAN DEFAULT false NOT NULL');
        $this->addSql('ALTER TABLE steam_user ADD economy_ban VARCHAR(32) DEFAULT NULL');
        $this->addSql('ALTER TABLE steam_user ADD updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL');
        $this->addSql('COMMENT ON COLUMN steam_user.updated_at IS \'(DC2Type:datetime_immutable)\'');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE steam_user DROP persona_name');
        $this->addSql('ALTER TABLE steam_user DROP avatar');
        $this->addSql('ALTER TABLE steam_user DROP country');
        $this->addSql('ALTER TABLE steam_user DROP time_created');
        $this->addSql('ALTER TABLE steam_user DROP vac_banned');
        $this->addSql('ALTER TABLE steam_user DROP vac_ban_count');
        $this->addSql('ALTER TABLE steam_user DROP community_banned');
        $this->addSql('ALTER TABLE steam_user DROP economy_ban');
        $this->addSql('ALTER TABLE steam_user DROP updated_at');
    }
}
