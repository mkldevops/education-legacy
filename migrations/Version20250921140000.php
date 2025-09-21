<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Make person email field nullable again.
 */
final class Version20250921140000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Make person email field nullable';
    }

    public function up(Schema $schema): void
    {
        // Make person email field nullable
        $this->addSql('ALTER TABLE person CHANGE email email VARCHAR(255) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // Revert: make person email field NOT NULL
        // First set a default email for any NULL values
        $this->addSql("UPDATE person SET email = CONCAT('person_', id, '@example.com') WHERE email IS NULL");
        $this->addSql('ALTER TABLE person CHANGE email email VARCHAR(255) NOT NULL');
    }
}
