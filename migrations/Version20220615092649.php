<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220615092649 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE pelicula ADD user_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE pelicula ADD CONSTRAINT FK_73BC7095A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('CREATE INDEX IDX_73BC7095A76ED395 ON pelicula (user_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE pelicula DROP FOREIGN KEY FK_73BC7095A76ED395');
        $this->addSql('DROP INDEX IDX_73BC7095A76ED395 ON pelicula');
        $this->addSql('ALTER TABLE pelicula DROP user_id');
    }
}
