<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260527000001 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add FCM token fields to User entity for push notifications';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE `user` ADD fcm_token VARCHAR(500) DEFAULT NULL AFTER display_name');
        $this->addSql('ALTER TABLE `user` ADD fcm_token_updated_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\'');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE `user` DROP COLUMN fcm_token');
        $this->addSql('ALTER TABLE `user` DROP COLUMN fcm_token_updated_at');
    }
}
