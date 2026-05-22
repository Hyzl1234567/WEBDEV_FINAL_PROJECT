<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260522000000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create sessions table for PDO session handler';
    }

    public function up(Schema $schema): void
    {
        $this->addSql("CREATE TABLE IF NOT EXISTS sessions (
            sess_id VARCHAR(128) NOT NULL PRIMARY KEY,
            sess_data MEDIUMBLOB NOT NULL,
            sess_time INTEGER UNSIGNED NOT NULL,
            sess_lifetime INTEGER UNSIGNED NOT NULL
        ) COLLATE utf8mb4_bin ENGINE = InnoDB");
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE IF EXISTS sessions');
    }
}
