<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210903182156 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE payment_package_student DROP INDEX UNIQ_3FF0261C44AC3583, ADD INDEX IDX_3FF0261C44AC3583 (operation_id)');
        $this->addSql('ALTER TABLE payment_package_student ADD amount DOUBLE PRECISION NOT NULL');
        $this->addSql('ALTER TABLE structure CHANGE options options JSON NOT NULL');
        $this->addSql('ALTER TABLE user CHANGE roles roles JSON NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE payment_package_student DROP INDEX IDX_3FF0261C44AC3583, ADD UNIQUE INDEX UNIQ_3FF0261C44AC3583 (operation_id)');
        $this->addSql('ALTER TABLE payment_package_student DROP amount');
        $this->addSql('ALTER TABLE structure CHANGE options options JSON NOT NULL COMMENT \'(DC2Type:json_array)\'');
        $this->addSql('ALTER TABLE user CHANGE roles roles JSON NOT NULL COMMENT \'(DC2Type:json_array)\'');
    }
}
