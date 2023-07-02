<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230701223919 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE UNIQUE INDEX UNIQ_A5E6215BE7927C74 ON family (email)');
        $this->addSql('ALTER TABLE grade CHANGE enable enable TINYINT(1) DEFAULT 1 NOT NULL');
        $this->addSql('ALTER TABLE meet CHANGE enable enable TINYINT(1) DEFAULT 1 NOT NULL');
        $this->addSql('ALTER TABLE operation CHANGE enable enable TINYINT(1) DEFAULT 1 NOT NULL');
        $this->addSql('ALTER TABLE operation_gender CHANGE enable enable TINYINT(1) DEFAULT 1 NOT NULL');
        $this->addSql('ALTER TABLE package CHANGE enable enable TINYINT(1) DEFAULT 1 NOT NULL');
        $this->addSql('ALTER TABLE payment_package_student CHANGE enable enable TINYINT(1) DEFAULT 1 NOT NULL');
        $this->addSql('ALTER TABLE period CHANGE enable enable TINYINT(1) DEFAULT 1 NOT NULL, CHANGE created_at created_at DATETIME NOT NULL, CHANGE updated_at updated_at DATETIME NOT NULL');
        $this->addSql('ALTER TABLE person CHANGE email email VARCHAR(255) NOT NULL, CHANGE enable enable TINYINT(1) DEFAULT 1 NOT NULL');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_34DCD176E7927C74 ON person (email)');
        $this->addSql('ALTER TABLE person_member CHANGE enable enable TINYINT(1) DEFAULT 1 NOT NULL, CHANGE created_at created_at DATETIME NOT NULL, CHANGE updated_at updated_at DATETIME NOT NULL');
        $this->addSql('ALTER TABLE school CHANGE enable enable TINYINT(1) DEFAULT 1 NOT NULL, CHANGE created_at created_at DATETIME NOT NULL, CHANGE updated_at updated_at DATETIME NOT NULL');
        $this->addSql('ALTER TABLE structure CHANGE enable enable TINYINT(1) DEFAULT 1 NOT NULL');
        $this->addSql('ALTER TABLE student CHANGE enable enable TINYINT(1) DEFAULT 1 NOT NULL');
        $this->addSql('ALTER TABLE student_comment CHANGE enable enable TINYINT(1) DEFAULT 1 NOT NULL');
        $this->addSql('ALTER TABLE teacher CHANGE enable enable TINYINT(1) DEFAULT 1 NOT NULL');
        $this->addSql('ALTER TABLE type_operation CHANGE enable enable TINYINT(1) DEFAULT 1 NOT NULL');
        $this->addSql('ALTER TABLE user CHANGE enable enable TINYINT(1) DEFAULT 1 NOT NULL');
        $this->addSql('ALTER TABLE validate CHANGE created_at created_at DATETIME NOT NULL, CHANGE updated_at updated_at DATETIME NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP INDEX UNIQ_A5E6215BE7927C74 ON family');
        $this->addSql('ALTER TABLE grade CHANGE enable enable TINYINT(1) NOT NULL');
        $this->addSql('ALTER TABLE meet CHANGE enable enable TINYINT(1) NOT NULL');
        $this->addSql('ALTER TABLE operation CHANGE enable enable TINYINT(1) NOT NULL');
        $this->addSql('ALTER TABLE operation_gender CHANGE enable enable TINYINT(1) NOT NULL');
        $this->addSql('ALTER TABLE package CHANGE enable enable TINYINT(1) NOT NULL');
        $this->addSql('ALTER TABLE payment_package_student CHANGE enable enable TINYINT(1) NOT NULL');
        $this->addSql('ALTER TABLE period CHANGE enable enable TINYINT(1) NOT NULL, CHANGE created_at created_at DATETIME DEFAULT NULL, CHANGE updated_at updated_at DATETIME DEFAULT NULL');
        $this->addSql('DROP INDEX UNIQ_34DCD176E7927C74 ON person');
        $this->addSql('ALTER TABLE person CHANGE email email VARCHAR(255) DEFAULT NULL, CHANGE enable enable TINYINT(1) NOT NULL');
        $this->addSql('ALTER TABLE person_member CHANGE enable enable TINYINT(1) NOT NULL, CHANGE created_at created_at DATETIME DEFAULT NULL, CHANGE updated_at updated_at DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE school CHANGE enable enable TINYINT(1) NOT NULL, CHANGE created_at created_at DATETIME DEFAULT NULL, CHANGE updated_at updated_at DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE structure CHANGE enable enable TINYINT(1) NOT NULL');
        $this->addSql('ALTER TABLE student CHANGE enable enable TINYINT(1) NOT NULL');
        $this->addSql('ALTER TABLE student_comment CHANGE enable enable TINYINT(1) NOT NULL');
        $this->addSql('ALTER TABLE teacher CHANGE enable enable TINYINT(1) NOT NULL');
        $this->addSql('ALTER TABLE type_operation CHANGE enable enable TINYINT(1) NOT NULL');
        $this->addSql('ALTER TABLE user CHANGE enable enable TINYINT(1) NOT NULL');
        $this->addSql('ALTER TABLE validate CHANGE created_at created_at DATETIME DEFAULT NULL, CHANGE updated_at updated_at DATETIME DEFAULT NULL');
    }
}
