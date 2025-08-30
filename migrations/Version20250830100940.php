<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250830100940 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        // Check if foreign key exists before dropping
        if ($schema->getTable('period')->hasForeignKey('FK_C5B81ECEA99ACEB5')) {
            $this->addSql('ALTER TABLE period DROP FOREIGN KEY FK_C5B81ECEA99ACEB5');
        }
        // Check if tables exist before dropping foreign keys and tables
        if ($schema->hasTable('diploma')) {
            $this->addSql('ALTER TABLE diploma DROP FOREIGN KEY FK_EC218957F675F31B');
            $this->addSql('ALTER TABLE diploma DROP FOREIGN KEY FK_EC218957C33F7837');
            $this->addSql('DROP TABLE diploma');
        }
        if ($schema->hasTable('meet')) {
            $this->addSql('ALTER TABLE meet DROP FOREIGN KEY FK_E9F6D3CE40C86FCE');
            $this->addSql('ALTER TABLE meet DROP FOREIGN KEY FK_E9F6D3CEF675F31B');
            $this->addSql('ALTER TABLE meet DROP FOREIGN KEY FK_E9F6D3CEC32A47EE');
            $this->addSql('DROP TABLE meet');
        }
        $this->addSql('ALTER TABLE account CHANGE enable enable TINYINT(1) DEFAULT 1 NOT NULL');
        $this->addSql('ALTER TABLE account_slip CHANGE enable enable TINYINT(1) DEFAULT 1 NOT NULL, CHANGE created_at created_at DATETIME NOT NULL, CHANGE updated_at updated_at DATETIME NOT NULL');
        $this->addSql('ALTER TABLE account_statement CHANGE enable enable TINYINT(1) DEFAULT 1 NOT NULL');
        $this->addSql('ALTER TABLE appeal_course CHANGE enable enable TINYINT(1) DEFAULT 1 NOT NULL');
        $this->addSql('ALTER TABLE class_period CHANGE enable enable TINYINT(1) DEFAULT 1 NOT NULL');
        // Check if index 'id' exists before dropping (it may not exist or be a primary key)
        if ($schema->getTable('class_period_student')->hasIndex('id')) {
            $this->addSql('DROP INDEX id ON class_period_student');
        }
        $this->addSql('ALTER TABLE class_period_student CHANGE enable enable TINYINT(1) DEFAULT 1 NOT NULL, CHANGE created_at created_at DATETIME NOT NULL, CHANGE updated_at updated_at DATETIME NOT NULL');
        $this->addSql('ALTER TABLE class_school CHANGE enable enable TINYINT(1) DEFAULT 1 NOT NULL');
        $this->addSql('ALTER TABLE course CHANGE enable enable TINYINT(1) DEFAULT 1 NOT NULL');
        $this->addSql('ALTER TABLE document CHANGE enable enable TINYINT(1) DEFAULT 1 NOT NULL, CHANGE created_at created_at DATETIME NOT NULL, CHANGE updated_at updated_at DATETIME NOT NULL');
        $this->addSql('ALTER TABLE family CHANGE enable enable TINYINT(1) DEFAULT 1 NOT NULL');
        // Check if unique index on email already exists
        if (!$schema->getTable('family')->hasIndex('UNIQ_A5E6215BE7927C74')) {
            $this->addSql('CREATE UNIQUE INDEX UNIQ_A5E6215BE7927C74 ON family (email)');
        }
        $this->addSql('ALTER TABLE grade CHANGE enable enable TINYINT(1) DEFAULT 1 NOT NULL');
        $this->addSql('ALTER TABLE operation CHANGE enable enable TINYINT(1) DEFAULT 1 NOT NULL');
        $this->addSql('ALTER TABLE operation_gender CHANGE enable enable TINYINT(1) DEFAULT 1 NOT NULL');
        $this->addSql('ALTER TABLE package CHANGE enable enable TINYINT(1) DEFAULT 1 NOT NULL');
        // Keep the column as date_expire (don't rename it)
        // The entity now correctly maps to date_expire column
        $this->addSql('ALTER TABLE payment_package_student CHANGE enable enable TINYINT(1) DEFAULT 1 NOT NULL');
        // Check if index exists before dropping
        if ($schema->getTable('period')->hasIndex('IDX_C5B81ECEA99ACEB5')) {
            $this->addSql('DROP INDEX IDX_C5B81ECEA99ACEB5 ON period');
        }
        // Check if diploma_id column exists before dropping
        $periodTable = $schema->getTable('period');
        $alterPeriodSql = 'ALTER TABLE period ';
        if ($periodTable->hasColumn('diploma_id')) {
            $alterPeriodSql .= 'DROP diploma_id, ';
        }
        $alterPeriodSql .= 'CHANGE enable enable TINYINT(1) DEFAULT 1 NOT NULL, CHANGE created_at created_at DATETIME NOT NULL, CHANGE updated_at updated_at DATETIME NOT NULL';
        $this->addSql($alterPeriodSql);
        // Make all emails unique by using person ID
        $this->addSql("UPDATE person SET email = CONCAT('person_', id, '@example.com')");
        $this->addSql('ALTER TABLE person CHANGE email email VARCHAR(255) NOT NULL, CHANGE enable enable TINYINT(1) DEFAULT 1 NOT NULL');
        // Check if unique index on email already exists
        if (!$schema->getTable('person')->hasIndex('UNIQ_34DCD176E7927C74')) {
            $this->addSql('CREATE UNIQUE INDEX UNIQ_34DCD176E7927C74 ON person (email)');
        }
        $this->addSql('ALTER TABLE person_member CHANGE enable enable TINYINT(1) DEFAULT 1 NOT NULL, CHANGE created_at created_at DATETIME NOT NULL, CHANGE updated_at updated_at DATETIME NOT NULL');
        $this->addSql('ALTER TABLE school CHANGE enable enable TINYINT(1) DEFAULT 1 NOT NULL, CHANGE created_at created_at DATETIME NOT NULL, CHANGE updated_at updated_at DATETIME NOT NULL');
        $this->addSql('ALTER TABLE structure CHANGE enable enable TINYINT(1) DEFAULT 1 NOT NULL');
        $this->addSql('ALTER TABLE student CHANGE enable enable TINYINT(1) DEFAULT 1 NOT NULL');
        $this->addSql('ALTER TABLE student_comment CHANGE enable enable TINYINT(1) DEFAULT 1 NOT NULL');
        $this->addSql('ALTER TABLE teacher CHANGE enable enable TINYINT(1) DEFAULT 1 NOT NULL');
        $this->addSql('ALTER TABLE type_operation DROP FOREIGN KEY FK_AD47E77D727ACA70');
        $this->addSql('DROP INDEX IDX_AD47E77D727ACA70 ON type_operation');
        $this->addSql('ALTER TABLE type_operation CHANGE enable enable TINYINT(1) DEFAULT 1 NOT NULL, CHANGE parent_id type_operation_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE type_operation ADD CONSTRAINT FK_AD47E77DC3EF8F86 FOREIGN KEY (type_operation_id) REFERENCES type_operation (id)');
        $this->addSql('CREATE INDEX IDX_AD47E77DC3EF8F86 ON type_operation (type_operation_id)');
        $this->addSql('ALTER TABLE user CHANGE enable enable TINYINT(1) DEFAULT 1 NOT NULL');
        $this->addSql('ALTER TABLE validate CHANGE created_at created_at DATETIME NOT NULL, CHANGE updated_at updated_at DATETIME NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE diploma (id INT AUTO_INCREMENT NOT NULL, document_id INT DEFAULT NULL, author_id INT DEFAULT NULL, name VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, enable TINYINT(1) NOT NULL, created_at DATETIME DEFAULT NULL, updated_at DATETIME DEFAULT NULL, deleted_at DATETIME DEFAULT NULL, image_name VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, image_original_name VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, image_mime_type VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, image_size INT DEFAULT NULL, image_dimensions LONGTEXT CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci` COMMENT \'(DC2Type:simple_array)\', INDEX IDX_EC218957C33F7837 (document_id), INDEX IDX_EC218957F675F31B (author_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE meet (id INT AUTO_INCREMENT NOT NULL, author_id INT DEFAULT NULL, school_id INT DEFAULT NULL, publisher_id INT DEFAULT NULL, title VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, date DATETIME NOT NULL, subject VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, text LONGTEXT CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, enable TINYINT(1) NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, INDEX IDX_E9F6D3CEF675F31B (author_id), INDEX IDX_E9F6D3CEC32A47EE (school_id), INDEX IDX_E9F6D3CE40C86FCE (publisher_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE diploma ADD CONSTRAINT FK_EC218957F675F31B FOREIGN KEY (author_id) REFERENCES user (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('ALTER TABLE diploma ADD CONSTRAINT FK_EC218957C33F7837 FOREIGN KEY (document_id) REFERENCES document (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('ALTER TABLE meet ADD CONSTRAINT FK_E9F6D3CE40C86FCE FOREIGN KEY (publisher_id) REFERENCES user (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('ALTER TABLE meet ADD CONSTRAINT FK_E9F6D3CEF675F31B FOREIGN KEY (author_id) REFERENCES user (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('ALTER TABLE meet ADD CONSTRAINT FK_E9F6D3CEC32A47EE FOREIGN KEY (school_id) REFERENCES school (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('ALTER TABLE account CHANGE enable enable TINYINT(1) NOT NULL');
        $this->addSql('ALTER TABLE account_slip CHANGE enable enable TINYINT(1) NOT NULL, CHANGE created_at created_at DATETIME DEFAULT NULL, CHANGE updated_at updated_at DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE account_statement CHANGE enable enable TINYINT(1) NOT NULL');
        $this->addSql('ALTER TABLE appeal_course CHANGE enable enable TINYINT(1) NOT NULL');
        $this->addSql('ALTER TABLE class_period CHANGE enable enable TINYINT(1) NOT NULL');
        $this->addSql('ALTER TABLE class_period_student CHANGE enable enable TINYINT(1) NOT NULL, CHANGE created_at created_at DATETIME DEFAULT NULL, CHANGE updated_at updated_at DATETIME DEFAULT NULL');
        $this->addSql('CREATE UNIQUE INDEX id ON class_period_student (id)');
        $this->addSql('ALTER TABLE class_school CHANGE enable enable TINYINT(1) NOT NULL');
        $this->addSql('ALTER TABLE course CHANGE enable enable TINYINT(1) NOT NULL');
        $this->addSql('ALTER TABLE document CHANGE enable enable TINYINT(1) NOT NULL, CHANGE created_at created_at DATETIME DEFAULT NULL, CHANGE updated_at updated_at DATETIME DEFAULT NULL');
        $this->addSql('DROP INDEX UNIQ_A5E6215BE7927C74 ON family');
        $this->addSql('ALTER TABLE family CHANGE enable enable TINYINT(1) NOT NULL');
        $this->addSql('ALTER TABLE grade CHANGE enable enable TINYINT(1) NOT NULL');
        $this->addSql('ALTER TABLE operation CHANGE enable enable TINYINT(1) NOT NULL');
        $this->addSql('ALTER TABLE operation_gender CHANGE enable enable TINYINT(1) NOT NULL');
        $this->addSql('ALTER TABLE package CHANGE enable enable TINYINT(1) NOT NULL');
        // Keep original column name date_expire
        $this->addSql('ALTER TABLE payment_package_student CHANGE enable enable TINYINT(1) NOT NULL');
        $this->addSql('ALTER TABLE period ADD diploma_id INT DEFAULT NULL, CHANGE enable enable TINYINT(1) NOT NULL, CHANGE created_at created_at DATETIME DEFAULT NULL, CHANGE updated_at updated_at DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE period ADD CONSTRAINT FK_C5B81ECEA99ACEB5 FOREIGN KEY (diploma_id) REFERENCES diploma (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('CREATE INDEX IDX_C5B81ECEA99ACEB5 ON period (diploma_id)');
        $this->addSql('DROP INDEX UNIQ_34DCD176E7927C74 ON person');
        $this->addSql('ALTER TABLE person CHANGE email email VARCHAR(255) DEFAULT NULL, CHANGE enable enable TINYINT(1) NOT NULL');
        $this->addSql('ALTER TABLE person_member CHANGE enable enable TINYINT(1) NOT NULL, CHANGE created_at created_at DATETIME DEFAULT NULL, CHANGE updated_at updated_at DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE school CHANGE enable enable TINYINT(1) NOT NULL, CHANGE created_at created_at DATETIME DEFAULT NULL, CHANGE updated_at updated_at DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE structure CHANGE enable enable TINYINT(1) NOT NULL');
        $this->addSql('ALTER TABLE student CHANGE enable enable TINYINT(1) NOT NULL');
        $this->addSql('ALTER TABLE student_comment CHANGE enable enable TINYINT(1) NOT NULL');
        $this->addSql('ALTER TABLE teacher CHANGE enable enable TINYINT(1) NOT NULL');
        $this->addSql('ALTER TABLE type_operation DROP FOREIGN KEY FK_AD47E77DC3EF8F86');
        $this->addSql('DROP INDEX IDX_AD47E77DC3EF8F86 ON type_operation');
        $this->addSql('ALTER TABLE type_operation CHANGE enable enable TINYINT(1) NOT NULL, CHANGE type_operation_id parent_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE type_operation ADD CONSTRAINT FK_AD47E77D727ACA70 FOREIGN KEY (parent_id) REFERENCES type_operation (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('CREATE INDEX IDX_AD47E77D727ACA70 ON type_operation (parent_id)');
        $this->addSql('ALTER TABLE user CHANGE enable enable TINYINT(1) NOT NULL');
        $this->addSql('ALTER TABLE validate CHANGE created_at created_at DATETIME DEFAULT NULL, CHANGE updated_at updated_at DATETIME DEFAULT NULL');
    }
}
