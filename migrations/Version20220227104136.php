<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220227104136 extends AbstractMigration
{
    public function isTransactional(): bool
    {
        return false;
    }

    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE account (id INT AUTO_INCREMENT NOT NULL, structure_id INT DEFAULT NULL, principal TINYINT(1) NOT NULL, is_bank TINYINT(1) NOT NULL, enable_account_statement TINYINT(1) NOT NULL, bank_name VARCHAR(255) DEFAULT NULL, bank_address VARCHAR(255) DEFAULT NULL, bank_iban VARCHAR(255) DEFAULT NULL, bank_bic VARCHAR(255) DEFAULT NULL, interval_operations_account_statement INT DEFAULT 5 NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, enable TINYINT(1) NOT NULL, name VARCHAR(255) DEFAULT NULL, INDEX IDX_7D3656A42534008B (structure_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE account_slip (id INT AUTO_INCREMENT NOT NULL, operation_credit_id INT DEFAULT NULL, operation_debit_id INT DEFAULT NULL, structure_id INT DEFAULT NULL, author_id INT DEFAULT NULL, date DATETIME NOT NULL, gender VARCHAR(20) NOT NULL, reference VARCHAR(40) NOT NULL, unique_id VARCHAR(100) DEFAULT NULL, name VARCHAR(255) DEFAULT NULL, enable TINYINT(1) NOT NULL, created_at DATETIME DEFAULT NULL, updated_at DATETIME DEFAULT NULL, deleted_at DATETIME DEFAULT NULL, comment LONGTEXT DEFAULT NULL, amount DOUBLE PRECISION NOT NULL, UNIQUE INDEX UNIQ_71AAC452E3C68343 (unique_id), UNIQUE INDEX UNIQ_71AAC4525DEAA61B (operation_credit_id), UNIQUE INDEX UNIQ_71AAC452A138FD63 (operation_debit_id), INDEX IDX_71AAC4522534008B (structure_id), INDEX IDX_71AAC452F675F31B (author_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE account_slip_document (account_slip_id INT NOT NULL, document_id INT NOT NULL, INDEX IDX_7744BD6F90EC6723 (account_slip_id), INDEX IDX_7744BD6FC33F7837 (document_id), PRIMARY KEY(account_slip_id, document_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE account_statement (id INT AUTO_INCREMENT NOT NULL, account_id INT NOT NULL, author_id INT DEFAULT NULL, title VARCHAR(255) DEFAULT NULL, begin DATETIME NOT NULL, end DATETIME NOT NULL, month DATE DEFAULT NULL, amount_credit DOUBLE PRECISION DEFAULT NULL, amount_debit DOUBLE PRECISION DEFAULT NULL, new_balance DOUBLE PRECISION DEFAULT NULL, number_operations INT DEFAULT NULL, reference VARCHAR(255) DEFAULT NULL, enable TINYINT(1) NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, INDEX IDX_E2A1892D9B6B5FBA (account_id), INDEX IDX_E2A1892DF675F31B (author_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE account_statement_document (account_statement_id INT NOT NULL, document_id INT NOT NULL, INDEX IDX_733356401D44A01D (account_statement_id), INDEX IDX_73335640C33F7837 (document_id), PRIMARY KEY(account_statement_id, document_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE appeal_course (id INT AUTO_INCREMENT NOT NULL, course_id INT DEFAULT NULL, student_id INT DEFAULT NULL, status INT NOT NULL, comment LONGTEXT DEFAULT NULL, enable TINYINT(1) NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, INDEX IDX_8F2A2E3D591CC992 (course_id), INDEX IDX_8F2A2E3DCB944F1A (student_id), UNIQUE INDEX UNIQ_8F2A2E3DCB944F1A591CC992 (student_id, course_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE class_period (id INT AUTO_INCREMENT NOT NULL, class_school_id INT NOT NULL, period_id INT NOT NULL, author_id INT DEFAULT NULL, comment LONGTEXT DEFAULT NULL, enable TINYINT(1) NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, INDEX IDX_AE45C76A8F5D8D1 (class_school_id), INDEX IDX_AE45C76EC8B7ADE (period_id), INDEX IDX_AE45C76F675F31B (author_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE class_period_student (class_period_id INT NOT NULL, student_id INT NOT NULL, author_id INT DEFAULT NULL, begin DATETIME NOT NULL, end DATETIME NOT NULL, comment LONGTEXT DEFAULT NULL, enable TINYINT(1) NOT NULL, created_at DATETIME DEFAULT NULL, updated_at DATETIME DEFAULT NULL, INDEX IDX_8A4414AF8754E5E1 (class_period_id), INDEX IDX_8A4414AFCB944F1A (student_id), INDEX IDX_8A4414AFF675F31B (author_id), PRIMARY KEY(class_period_id, student_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE class_school (id INT AUTO_INCREMENT NOT NULL, author_id INT DEFAULT NULL, school_id INT DEFAULT NULL, age_minimum INT NOT NULL, age_maximum INT NOT NULL, name VARCHAR(255) DEFAULT NULL, enable TINYINT(1) NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, deleted_at DATETIME DEFAULT NULL, description LONGTEXT DEFAULT NULL, INDEX IDX_36C29803F675F31B (author_id), INDEX IDX_36C29803C32A47EE (school_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE course (id INT AUTO_INCREMENT NOT NULL, class_period_id INT DEFAULT NULL, author_id INT DEFAULT NULL, id_event VARCHAR(255) DEFAULT NULL, date DATE NOT NULL, text LONGTEXT DEFAULT NULL, hour_begin TIME NOT NULL, hour_end TIME NOT NULL, comment LONGTEXT DEFAULT NULL, enable TINYINT(1) NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, UNIQUE INDEX UNIQ_169E6FB9D52B4B97 (id_event), INDEX IDX_169E6FB98754E5E1 (class_period_id), INDEX IDX_169E6FB9F675F31B (author_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE diploma (id INT AUTO_INCREMENT NOT NULL, document_id INT DEFAULT NULL, author_id INT DEFAULT NULL, name VARCHAR(255) DEFAULT NULL, enable TINYINT(1) NOT NULL, created_at DATETIME DEFAULT NULL, updated_at DATETIME DEFAULT NULL, deleted_at DATETIME DEFAULT NULL, image_name VARCHAR(255) DEFAULT NULL, image_original_name VARCHAR(255) DEFAULT NULL, image_mime_type VARCHAR(255) DEFAULT NULL, image_size INT DEFAULT NULL, image_dimensions LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:simple_array)\', INDEX IDX_EC218957C33F7837 (document_id), INDEX IDX_EC218957F675F31B (author_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE document (id INT AUTO_INCREMENT NOT NULL, author_id INT DEFAULT NULL, school_id INT DEFAULT NULL, mime VARCHAR(30) DEFAULT NULL, path VARCHAR(255) DEFAULT NULL, extension VARCHAR(10) NOT NULL, size INT DEFAULT NULL, name VARCHAR(255) DEFAULT NULL, enable TINYINT(1) NOT NULL, created_at DATETIME DEFAULT NULL, updated_at DATETIME DEFAULT NULL, deleted_at DATETIME DEFAULT NULL, INDEX IDX_D8698A76F675F31B (author_id), INDEX IDX_D8698A76C32A47EE (school_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE family (id INT AUTO_INCREMENT NOT NULL, father_id INT DEFAULT NULL, mother_id INT DEFAULT NULL, legal_guardian_id INT DEFAULT NULL, author_id INT DEFAULT NULL, name VARCHAR(255) DEFAULT NULL, language VARCHAR(255) DEFAULT NULL, number_children INT NOT NULL, person_authorized VARCHAR(255) DEFAULT NULL, person_emergency VARCHAR(255) DEFAULT NULL, enable TINYINT(1) NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, deleted_at DATETIME DEFAULT NULL, email VARCHAR(255) DEFAULT NULL, address VARCHAR(255) DEFAULT NULL, city VARCHAR(100) DEFAULT NULL, zip VARCHAR(10) DEFAULT NULL, UNIQUE INDEX UNIQ_A5E6215B2055B9A2 (father_id), UNIQUE INDEX UNIQ_A5E6215BB78A354D (mother_id), UNIQUE INDEX UNIQ_A5E6215B40A26EB (legal_guardian_id), INDEX IDX_A5E6215BF675F31B (author_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE grade (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) DEFAULT NULL, enable TINYINT(1) NOT NULL, description LONGTEXT DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE meet (id INT AUTO_INCREMENT NOT NULL, author_id INT DEFAULT NULL, school_id INT DEFAULT NULL, publisher_id INT DEFAULT NULL, title VARCHAR(255) NOT NULL, date DATETIME NOT NULL, subject VARCHAR(255) NOT NULL, text LONGTEXT NOT NULL, enable TINYINT(1) NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, INDEX IDX_E9F6D3CEF675F31B (author_id), INDEX IDX_E9F6D3CEC32A47EE (school_id), INDEX IDX_E9F6D3CE40C86FCE (publisher_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE operation (id INT AUTO_INCREMENT NOT NULL, account_id INT NOT NULL, account_statement_id INT DEFAULT NULL, type_operation_id INT DEFAULT NULL, validate_id INT DEFAULT NULL, operation_gender_id INT DEFAULT NULL, author_id INT DEFAULT NULL, publisher_id INT DEFAULT NULL, reference VARCHAR(255) DEFAULT NULL, unique_id VARCHAR(255) DEFAULT NULL, date DATETIME DEFAULT NULL, date_planned DATETIME NOT NULL, name VARCHAR(255) DEFAULT NULL, enable TINYINT(1) NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, deleted_at DATETIME DEFAULT NULL, comment LONGTEXT DEFAULT NULL, amount DOUBLE PRECISION NOT NULL, UNIQUE INDEX UNIQ_1981A66DE3C68343 (unique_id), INDEX IDX_1981A66D1D44A01D (account_statement_id), INDEX IDX_1981A66DC3EF8F86 (type_operation_id), INDEX IDX_1981A66D133D6627 (validate_id), INDEX IDX_1981A66D94E42902 (operation_gender_id), INDEX IDX_1981A66DF675F31B (author_id), INDEX IDX_1981A66D40C86FCE (publisher_id), INDEX IDX_1981A66D9B6B5FBA (account_id), INDEX IDX_1981A66DAA9E377A5862C5CD (date, date_planned), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE operation_document (operation_id INT NOT NULL, document_id INT NOT NULL, INDEX IDX_3D1FF5FB44AC3583 (operation_id), INDEX IDX_3D1FF5FBC33F7837 (document_id), PRIMARY KEY(operation_id, document_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE operation_gender (id INT AUTO_INCREMENT NOT NULL, code VARCHAR(30) NOT NULL, name VARCHAR(255) DEFAULT NULL, enable TINYINT(1) NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, UNIQUE INDEX UNIQ_BAC7B32077153098 (code), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE package (id INT AUTO_INCREMENT NOT NULL, school_id INT DEFAULT NULL, price DOUBLE PRECISION NOT NULL, name VARCHAR(255) DEFAULT NULL, description LONGTEXT DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, enable TINYINT(1) NOT NULL, INDEX IDX_DE686795C32A47EE (school_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE package_student_period (id INT AUTO_INCREMENT NOT NULL, package_id INT NOT NULL, period_id INT NOT NULL, author_id INT DEFAULT NULL, student_id INT DEFAULT NULL, date_expire DATETIME NOT NULL, discount DOUBLE PRECISION NOT NULL, paid TINYINT(1) NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, comment LONGTEXT DEFAULT NULL, amount DOUBLE PRECISION NOT NULL, INDEX IDX_A6F88096F44CABFF (package_id), INDEX IDX_A6F88096EC8B7ADE (period_id), INDEX IDX_A6F88096F675F31B (author_id), INDEX IDX_A6F88096CB944F1A (student_id), UNIQUE INDEX UNIQ_A6F88096F44CABFFEC8B7ADECB944F1A (package_id, period_id, student_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE payment_package_student (id INT AUTO_INCREMENT NOT NULL, package_student_period_id INT NOT NULL, operation_id INT NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, comment LONGTEXT DEFAULT NULL, enable TINYINT(1) NOT NULL, amount DOUBLE PRECISION NOT NULL, INDEX IDX_3FF0261C173AE3B1 (package_student_period_id), INDEX IDX_3FF0261C44AC3583 (operation_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE period (id INT AUTO_INCREMENT NOT NULL, diploma_id INT DEFAULT NULL, author_id INT DEFAULT NULL, begin DATETIME NOT NULL, end DATETIME NOT NULL, name VARCHAR(255) DEFAULT NULL, enable TINYINT(1) NOT NULL, created_at DATETIME DEFAULT NULL, updated_at DATETIME DEFAULT NULL, deleted_at DATETIME DEFAULT NULL, comment LONGTEXT DEFAULT NULL, INDEX IDX_C5B81ECEA99ACEB5 (diploma_id), INDEX IDX_C5B81ECEF675F31B (author_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE person (id INT AUTO_INCREMENT NOT NULL, user_id INT DEFAULT NULL, image_id INT DEFAULT NULL, family_id INT DEFAULT NULL, author_id INT DEFAULT NULL, forname VARCHAR(255) NOT NULL, phone VARCHAR(255) DEFAULT NULL, birthday DATETIME DEFAULT NULL, birthplace VARCHAR(255) DEFAULT NULL, gender VARCHAR(10) NOT NULL, name VARCHAR(255) DEFAULT NULL, email VARCHAR(255) DEFAULT NULL, address VARCHAR(255) DEFAULT NULL, zip VARCHAR(10) DEFAULT NULL, city VARCHAR(100) DEFAULT NULL, enable TINYINT(1) NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, deleted_at DATETIME DEFAULT NULL, UNIQUE INDEX UNIQ_34DCD176A76ED395 (user_id), INDEX IDX_34DCD1763DA5256D (image_id), INDEX IDX_34DCD176C35E566A (family_id), INDEX IDX_34DCD176F675F31B (author_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE person_school (person_id INT NOT NULL, school_id INT NOT NULL, INDEX IDX_653BEA0D217BBB47 (person_id), INDEX IDX_653BEA0DC32A47EE (school_id), PRIMARY KEY(person_id, school_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE person_member (id INT AUTO_INCREMENT NOT NULL, person_id INT DEFAULT NULL, structure_id INT DEFAULT NULL, author_id INT DEFAULT NULL, position_name VARCHAR(255) NOT NULL, name VARCHAR(255) DEFAULT NULL, enable TINYINT(1) NOT NULL, created_at DATETIME DEFAULT NULL, updated_at DATETIME DEFAULT NULL, deleted_at DATETIME DEFAULT NULL, UNIQUE INDEX UNIQ_EC41CACE217BBB47 (person_id), INDEX IDX_EC41CACE2534008B (structure_id), INDEX IDX_EC41CACEF675F31B (author_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE school (id INT AUTO_INCREMENT NOT NULL, director_id INT DEFAULT NULL, structure_id INT DEFAULT NULL, author_id INT DEFAULT NULL, principal TINYINT(1) DEFAULT \'0\' NOT NULL, name VARCHAR(255) DEFAULT NULL, enable TINYINT(1) NOT NULL, created_at DATETIME DEFAULT NULL, updated_at DATETIME DEFAULT NULL, deleted_at DATETIME DEFAULT NULL, comment LONGTEXT DEFAULT NULL, zip VARCHAR(10) DEFAULT NULL, city VARCHAR(100) DEFAULT NULL, address VARCHAR(255) DEFAULT NULL, INDEX IDX_F99EDABB899FB366 (director_id), INDEX IDX_F99EDABB2534008B (structure_id), INDEX IDX_F99EDABBF675F31B (author_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE structure (id INT AUTO_INCREMENT NOT NULL, president_id INT DEFAULT NULL, treasurer_id INT DEFAULT NULL, secretary_id INT DEFAULT NULL, author_id INT DEFAULT NULL, logo VARCHAR(255) DEFAULT NULL, options JSON NOT NULL, name VARCHAR(255) DEFAULT NULL, enable TINYINT(1) NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, deleted_at DATETIME DEFAULT NULL, city VARCHAR(100) DEFAULT NULL, zip VARCHAR(10) DEFAULT NULL, address VARCHAR(255) DEFAULT NULL, INDEX IDX_6F0137EAB40A33C7 (president_id), INDEX IDX_6F0137EA55808438 (treasurer_id), INDEX IDX_6F0137EAA2A63DB2 (secretary_id), INDEX IDX_6F0137EAF675F31B (author_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE student (id INT AUTO_INCREMENT NOT NULL, person_id INT DEFAULT NULL, grade_id INT DEFAULT NULL, author_id INT DEFAULT NULL, school_id INT DEFAULT NULL, last_school VARCHAR(255) DEFAULT NULL, person_authorized VARCHAR(255) DEFAULT NULL, remarks_health LONGTEXT DEFAULT NULL, let_alone TINYINT(1) NOT NULL, date_registration DATETIME NOT NULL, date_desactivated DATETIME DEFAULT NULL, enable TINYINT(1) NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, deleted_at DATETIME DEFAULT NULL, UNIQUE INDEX UNIQ_B723AF33217BBB47 (person_id), INDEX IDX_B723AF33FE19A1A8 (grade_id), INDEX IDX_B723AF33F675F31B (author_id), INDEX IDX_B723AF33C32A47EE (school_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE student_comment (id INT AUTO_INCREMENT NOT NULL, student_id INT DEFAULT NULL, author_id INT DEFAULT NULL, title VARCHAR(255) NOT NULL, text LONGTEXT NOT NULL, type VARCHAR(20) NOT NULL, enable TINYINT(1) NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, INDEX IDX_7942E794CB944F1A (student_id), INDEX IDX_7942E794F675F31B (author_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE teacher (id INT AUTO_INCREMENT NOT NULL, person_id INT DEFAULT NULL, grade_id INT DEFAULT NULL, author_id INT DEFAULT NULL, name VARCHAR(255) DEFAULT NULL, enable TINYINT(1) NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, deleted_at DATETIME DEFAULT NULL, UNIQUE INDEX UNIQ_B0F6A6D5217BBB47 (person_id), INDEX IDX_B0F6A6D5FE19A1A8 (grade_id), INDEX IDX_B0F6A6D5F675F31B (author_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE teacher_course (teacher_id INT NOT NULL, course_id INT NOT NULL, INDEX IDX_315BD4C41807E1D (teacher_id), INDEX IDX_315BD4C591CC992 (course_id), PRIMARY KEY(teacher_id, course_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE teacher_class_period (teacher_id INT NOT NULL, class_period_id INT NOT NULL, INDEX IDX_9C3BA84441807E1D (teacher_id), INDEX IDX_9C3BA8448754E5E1 (class_period_id), PRIMARY KEY(teacher_id, class_period_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE type_operation (id INT AUTO_INCREMENT NOT NULL, parent_id INT DEFAULT NULL, author_id INT DEFAULT NULL, short_name VARCHAR(255) NOT NULL, code VARCHAR(10) DEFAULT NULL, type_amount VARCHAR(10) DEFAULT \'mixte\' NOT NULL, is_internal_transfert TINYINT(1) NOT NULL, name VARCHAR(255) DEFAULT NULL, description LONGTEXT DEFAULT NULL, enable TINYINT(1) NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, deleted_at DATETIME DEFAULT NULL, UNIQUE INDEX UNIQ_AD47E77D77153098 (code), INDEX IDX_AD47E77D727ACA70 (parent_id), INDEX IDX_AD47E77DF675F31B (author_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE user (id INT AUTO_INCREMENT NOT NULL, author_id INT DEFAULT NULL, username VARCHAR(180) NOT NULL, roles JSON NOT NULL, password VARCHAR(255) NOT NULL, surname VARCHAR(255) DEFAULT NULL, email VARCHAR(255) DEFAULT NULL, last_login DATETIME DEFAULT NULL, name VARCHAR(255) DEFAULT NULL, enable TINYINT(1) NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, deleted_at DATETIME DEFAULT NULL, UNIQUE INDEX UNIQ_8D93D649F85E0677 (username), UNIQUE INDEX UNIQ_8D93D649E7927C74 (email), INDEX IDX_8D93D649F675F31B (author_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE user_school (user_id INT NOT NULL, school_id INT NOT NULL, INDEX IDX_9CCCC186A76ED395 (user_id), INDEX IDX_9CCCC186C32A47EE (school_id), PRIMARY KEY(user_id, school_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE validate (id INT AUTO_INCREMENT NOT NULL, author_id INT DEFAULT NULL, message VARCHAR(255) NOT NULL, type VARCHAR(10) NOT NULL, created_at DATETIME DEFAULT NULL, updated_at DATETIME DEFAULT NULL, INDEX IDX_42123254F675F31B (author_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE account ADD CONSTRAINT FK_7D3656A42534008B FOREIGN KEY (structure_id) REFERENCES structure (id)');
        $this->addSql('ALTER TABLE account_slip ADD CONSTRAINT FK_71AAC4525DEAA61B FOREIGN KEY (operation_credit_id) REFERENCES operation (id)');
        $this->addSql('ALTER TABLE account_slip ADD CONSTRAINT FK_71AAC452A138FD63 FOREIGN KEY (operation_debit_id) REFERENCES operation (id)');
        $this->addSql('ALTER TABLE account_slip ADD CONSTRAINT FK_71AAC4522534008B FOREIGN KEY (structure_id) REFERENCES structure (id)');
        $this->addSql('ALTER TABLE account_slip ADD CONSTRAINT FK_71AAC452F675F31B FOREIGN KEY (author_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE account_slip_document ADD CONSTRAINT FK_7744BD6F90EC6723 FOREIGN KEY (account_slip_id) REFERENCES account_slip (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE account_slip_document ADD CONSTRAINT FK_7744BD6FC33F7837 FOREIGN KEY (document_id) REFERENCES document (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE account_statement ADD CONSTRAINT FK_E2A1892D9B6B5FBA FOREIGN KEY (account_id) REFERENCES account (id)');
        $this->addSql('ALTER TABLE account_statement ADD CONSTRAINT FK_E2A1892DF675F31B FOREIGN KEY (author_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE account_statement_document ADD CONSTRAINT FK_733356401D44A01D FOREIGN KEY (account_statement_id) REFERENCES account_statement (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE account_statement_document ADD CONSTRAINT FK_73335640C33F7837 FOREIGN KEY (document_id) REFERENCES document (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE appeal_course ADD CONSTRAINT FK_8F2A2E3D591CC992 FOREIGN KEY (course_id) REFERENCES course (id)');
        $this->addSql('ALTER TABLE appeal_course ADD CONSTRAINT FK_8F2A2E3DCB944F1A FOREIGN KEY (student_id) REFERENCES student (id)');
        $this->addSql('ALTER TABLE class_period ADD CONSTRAINT FK_AE45C76A8F5D8D1 FOREIGN KEY (class_school_id) REFERENCES class_school (id)');
        $this->addSql('ALTER TABLE class_period ADD CONSTRAINT FK_AE45C76EC8B7ADE FOREIGN KEY (period_id) REFERENCES period (id)');
        $this->addSql('ALTER TABLE class_period ADD CONSTRAINT FK_AE45C76F675F31B FOREIGN KEY (author_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE class_period_student ADD CONSTRAINT FK_8A4414AF8754E5E1 FOREIGN KEY (class_period_id) REFERENCES class_period (id)');
        $this->addSql('ALTER TABLE class_period_student ADD CONSTRAINT FK_8A4414AFCB944F1A FOREIGN KEY (student_id) REFERENCES student (id)');
        $this->addSql('ALTER TABLE class_period_student ADD CONSTRAINT FK_8A4414AFF675F31B FOREIGN KEY (author_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE class_school ADD CONSTRAINT FK_36C29803F675F31B FOREIGN KEY (author_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE class_school ADD CONSTRAINT FK_36C29803C32A47EE FOREIGN KEY (school_id) REFERENCES school (id)');
        $this->addSql('ALTER TABLE course ADD CONSTRAINT FK_169E6FB98754E5E1 FOREIGN KEY (class_period_id) REFERENCES class_period (id)');
        $this->addSql('ALTER TABLE course ADD CONSTRAINT FK_169E6FB9F675F31B FOREIGN KEY (author_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE diploma ADD CONSTRAINT FK_EC218957C33F7837 FOREIGN KEY (document_id) REFERENCES document (id)');
        $this->addSql('ALTER TABLE diploma ADD CONSTRAINT FK_EC218957F675F31B FOREIGN KEY (author_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE document ADD CONSTRAINT FK_D8698A76F675F31B FOREIGN KEY (author_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE document ADD CONSTRAINT FK_D8698A76C32A47EE FOREIGN KEY (school_id) REFERENCES school (id)');
        $this->addSql('ALTER TABLE family ADD CONSTRAINT FK_A5E6215B2055B9A2 FOREIGN KEY (father_id) REFERENCES person (id)');
        $this->addSql('ALTER TABLE family ADD CONSTRAINT FK_A5E6215BB78A354D FOREIGN KEY (mother_id) REFERENCES person (id)');
        $this->addSql('ALTER TABLE family ADD CONSTRAINT FK_A5E6215B40A26EB FOREIGN KEY (legal_guardian_id) REFERENCES person (id)');
        $this->addSql('ALTER TABLE family ADD CONSTRAINT FK_A5E6215BF675F31B FOREIGN KEY (author_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE meet ADD CONSTRAINT FK_E9F6D3CEF675F31B FOREIGN KEY (author_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE meet ADD CONSTRAINT FK_E9F6D3CEC32A47EE FOREIGN KEY (school_id) REFERENCES school (id)');
        $this->addSql('ALTER TABLE meet ADD CONSTRAINT FK_E9F6D3CE40C86FCE FOREIGN KEY (publisher_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE operation ADD CONSTRAINT FK_1981A66D9B6B5FBA FOREIGN KEY (account_id) REFERENCES account (id)');
        $this->addSql('ALTER TABLE operation ADD CONSTRAINT FK_1981A66D1D44A01D FOREIGN KEY (account_statement_id) REFERENCES account_statement (id)');
        $this->addSql('ALTER TABLE operation ADD CONSTRAINT FK_1981A66DC3EF8F86 FOREIGN KEY (type_operation_id) REFERENCES type_operation (id)');
        $this->addSql('ALTER TABLE operation ADD CONSTRAINT FK_1981A66D133D6627 FOREIGN KEY (validate_id) REFERENCES validate (id)');
        $this->addSql('ALTER TABLE operation ADD CONSTRAINT FK_1981A66D94E42902 FOREIGN KEY (operation_gender_id) REFERENCES operation_gender (id)');
        $this->addSql('ALTER TABLE operation ADD CONSTRAINT FK_1981A66DF675F31B FOREIGN KEY (author_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE operation ADD CONSTRAINT FK_1981A66D40C86FCE FOREIGN KEY (publisher_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE operation_document ADD CONSTRAINT FK_3D1FF5FB44AC3583 FOREIGN KEY (operation_id) REFERENCES operation (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE operation_document ADD CONSTRAINT FK_3D1FF5FBC33F7837 FOREIGN KEY (document_id) REFERENCES document (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE package ADD CONSTRAINT FK_DE686795C32A47EE FOREIGN KEY (school_id) REFERENCES school (id)');
        $this->addSql('ALTER TABLE package_student_period ADD CONSTRAINT FK_A6F88096F44CABFF FOREIGN KEY (package_id) REFERENCES package (id)');
        $this->addSql('ALTER TABLE package_student_period ADD CONSTRAINT FK_A6F88096EC8B7ADE FOREIGN KEY (period_id) REFERENCES period (id)');
        $this->addSql('ALTER TABLE package_student_period ADD CONSTRAINT FK_A6F88096F675F31B FOREIGN KEY (author_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE package_student_period ADD CONSTRAINT FK_A6F88096CB944F1A FOREIGN KEY (student_id) REFERENCES student (id)');
        $this->addSql('ALTER TABLE payment_package_student ADD CONSTRAINT FK_3FF0261C173AE3B1 FOREIGN KEY (package_student_period_id) REFERENCES package_student_period (id)');
        $this->addSql('ALTER TABLE payment_package_student ADD CONSTRAINT FK_3FF0261C44AC3583 FOREIGN KEY (operation_id) REFERENCES operation (id)');
        $this->addSql('ALTER TABLE period ADD CONSTRAINT FK_C5B81ECEA99ACEB5 FOREIGN KEY (diploma_id) REFERENCES diploma (id)');
        $this->addSql('ALTER TABLE period ADD CONSTRAINT FK_C5B81ECEF675F31B FOREIGN KEY (author_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE person ADD CONSTRAINT FK_34DCD176A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE person ADD CONSTRAINT FK_34DCD1763DA5256D FOREIGN KEY (image_id) REFERENCES document (id)');
        $this->addSql('ALTER TABLE person ADD CONSTRAINT FK_34DCD176C35E566A FOREIGN KEY (family_id) REFERENCES family (id)');
        $this->addSql('ALTER TABLE person ADD CONSTRAINT FK_34DCD176F675F31B FOREIGN KEY (author_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE person_school ADD CONSTRAINT FK_653BEA0D217BBB47 FOREIGN KEY (person_id) REFERENCES person (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE person_school ADD CONSTRAINT FK_653BEA0DC32A47EE FOREIGN KEY (school_id) REFERENCES school (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE person_member ADD CONSTRAINT FK_EC41CACE217BBB47 FOREIGN KEY (person_id) REFERENCES person (id)');
        $this->addSql('ALTER TABLE person_member ADD CONSTRAINT FK_EC41CACE2534008B FOREIGN KEY (structure_id) REFERENCES structure (id)');
        $this->addSql('ALTER TABLE person_member ADD CONSTRAINT FK_EC41CACEF675F31B FOREIGN KEY (author_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE school ADD CONSTRAINT FK_F99EDABB899FB366 FOREIGN KEY (director_id) REFERENCES person_member (id)');
        $this->addSql('ALTER TABLE school ADD CONSTRAINT FK_F99EDABB2534008B FOREIGN KEY (structure_id) REFERENCES structure (id)');
        $this->addSql('ALTER TABLE school ADD CONSTRAINT FK_F99EDABBF675F31B FOREIGN KEY (author_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE structure ADD CONSTRAINT FK_6F0137EAB40A33C7 FOREIGN KEY (president_id) REFERENCES person_member (id)');
        $this->addSql('ALTER TABLE structure ADD CONSTRAINT FK_6F0137EA55808438 FOREIGN KEY (treasurer_id) REFERENCES person_member (id)');
        $this->addSql('ALTER TABLE structure ADD CONSTRAINT FK_6F0137EAA2A63DB2 FOREIGN KEY (secretary_id) REFERENCES person_member (id)');
        $this->addSql('ALTER TABLE structure ADD CONSTRAINT FK_6F0137EAF675F31B FOREIGN KEY (author_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE student ADD CONSTRAINT FK_B723AF33217BBB47 FOREIGN KEY (person_id) REFERENCES person (id)');
        $this->addSql('ALTER TABLE student ADD CONSTRAINT FK_B723AF33FE19A1A8 FOREIGN KEY (grade_id) REFERENCES grade (id)');
        $this->addSql('ALTER TABLE student ADD CONSTRAINT FK_B723AF33F675F31B FOREIGN KEY (author_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE student ADD CONSTRAINT FK_B723AF33C32A47EE FOREIGN KEY (school_id) REFERENCES school (id)');
        $this->addSql('ALTER TABLE student_comment ADD CONSTRAINT FK_7942E794CB944F1A FOREIGN KEY (student_id) REFERENCES student (id)');
        $this->addSql('ALTER TABLE student_comment ADD CONSTRAINT FK_7942E794F675F31B FOREIGN KEY (author_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE teacher ADD CONSTRAINT FK_B0F6A6D5217BBB47 FOREIGN KEY (person_id) REFERENCES person (id)');
        $this->addSql('ALTER TABLE teacher ADD CONSTRAINT FK_B0F6A6D5FE19A1A8 FOREIGN KEY (grade_id) REFERENCES grade (id)');
        $this->addSql('ALTER TABLE teacher ADD CONSTRAINT FK_B0F6A6D5F675F31B FOREIGN KEY (author_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE teacher_course ADD CONSTRAINT FK_315BD4C41807E1D FOREIGN KEY (teacher_id) REFERENCES teacher (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE teacher_course ADD CONSTRAINT FK_315BD4C591CC992 FOREIGN KEY (course_id) REFERENCES course (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE teacher_class_period ADD CONSTRAINT FK_9C3BA84441807E1D FOREIGN KEY (teacher_id) REFERENCES teacher (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE teacher_class_period ADD CONSTRAINT FK_9C3BA8448754E5E1 FOREIGN KEY (class_period_id) REFERENCES class_period (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE type_operation ADD CONSTRAINT FK_AD47E77D727ACA70 FOREIGN KEY (parent_id) REFERENCES type_operation (id)');
        $this->addSql('ALTER TABLE type_operation ADD CONSTRAINT FK_AD47E77DF675F31B FOREIGN KEY (author_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE user ADD CONSTRAINT FK_8D93D649F675F31B FOREIGN KEY (author_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE user_school ADD CONSTRAINT FK_9CCCC186A76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE user_school ADD CONSTRAINT FK_9CCCC186C32A47EE FOREIGN KEY (school_id) REFERENCES school (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE validate ADD CONSTRAINT FK_42123254F675F31B FOREIGN KEY (author_id) REFERENCES user (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE account_statement DROP FOREIGN KEY FK_E2A1892D9B6B5FBA');
        $this->addSql('ALTER TABLE operation DROP FOREIGN KEY FK_1981A66D9B6B5FBA');
        $this->addSql('ALTER TABLE account_slip_document DROP FOREIGN KEY FK_7744BD6F90EC6723');
        $this->addSql('ALTER TABLE account_statement_document DROP FOREIGN KEY FK_733356401D44A01D');
        $this->addSql('ALTER TABLE operation DROP FOREIGN KEY FK_1981A66D1D44A01D');
        $this->addSql('ALTER TABLE class_period_student DROP FOREIGN KEY FK_8A4414AF8754E5E1');
        $this->addSql('ALTER TABLE course DROP FOREIGN KEY FK_169E6FB98754E5E1');
        $this->addSql('ALTER TABLE teacher_class_period DROP FOREIGN KEY FK_9C3BA8448754E5E1');
        $this->addSql('ALTER TABLE class_period DROP FOREIGN KEY FK_AE45C76A8F5D8D1');
        $this->addSql('ALTER TABLE appeal_course DROP FOREIGN KEY FK_8F2A2E3D591CC992');
        $this->addSql('ALTER TABLE teacher_course DROP FOREIGN KEY FK_315BD4C591CC992');
        $this->addSql('ALTER TABLE period DROP FOREIGN KEY FK_C5B81ECEA99ACEB5');
        $this->addSql('ALTER TABLE account_slip_document DROP FOREIGN KEY FK_7744BD6FC33F7837');
        $this->addSql('ALTER TABLE account_statement_document DROP FOREIGN KEY FK_73335640C33F7837');
        $this->addSql('ALTER TABLE diploma DROP FOREIGN KEY FK_EC218957C33F7837');
        $this->addSql('ALTER TABLE operation_document DROP FOREIGN KEY FK_3D1FF5FBC33F7837');
        $this->addSql('ALTER TABLE person DROP FOREIGN KEY FK_34DCD1763DA5256D');
        $this->addSql('ALTER TABLE person DROP FOREIGN KEY FK_34DCD176C35E566A');
        $this->addSql('ALTER TABLE student DROP FOREIGN KEY FK_B723AF33FE19A1A8');
        $this->addSql('ALTER TABLE teacher DROP FOREIGN KEY FK_B0F6A6D5FE19A1A8');
        $this->addSql('ALTER TABLE account_slip DROP FOREIGN KEY FK_71AAC4525DEAA61B');
        $this->addSql('ALTER TABLE account_slip DROP FOREIGN KEY FK_71AAC452A138FD63');
        $this->addSql('ALTER TABLE operation_document DROP FOREIGN KEY FK_3D1FF5FB44AC3583');
        $this->addSql('ALTER TABLE payment_package_student DROP FOREIGN KEY FK_3FF0261C44AC3583');
        $this->addSql('ALTER TABLE operation DROP FOREIGN KEY FK_1981A66D94E42902');
        $this->addSql('ALTER TABLE package_student_period DROP FOREIGN KEY FK_A6F88096F44CABFF');
        $this->addSql('ALTER TABLE payment_package_student DROP FOREIGN KEY FK_3FF0261C173AE3B1');
        $this->addSql('ALTER TABLE class_period DROP FOREIGN KEY FK_AE45C76EC8B7ADE');
        $this->addSql('ALTER TABLE package_student_period DROP FOREIGN KEY FK_A6F88096EC8B7ADE');
        $this->addSql('ALTER TABLE family DROP FOREIGN KEY FK_A5E6215B2055B9A2');
        $this->addSql('ALTER TABLE family DROP FOREIGN KEY FK_A5E6215BB78A354D');
        $this->addSql('ALTER TABLE family DROP FOREIGN KEY FK_A5E6215B40A26EB');
        $this->addSql('ALTER TABLE person_school DROP FOREIGN KEY FK_653BEA0D217BBB47');
        $this->addSql('ALTER TABLE person_member DROP FOREIGN KEY FK_EC41CACE217BBB47');
        $this->addSql('ALTER TABLE student DROP FOREIGN KEY FK_B723AF33217BBB47');
        $this->addSql('ALTER TABLE teacher DROP FOREIGN KEY FK_B0F6A6D5217BBB47');
        $this->addSql('ALTER TABLE school DROP FOREIGN KEY FK_F99EDABB899FB366');
        $this->addSql('ALTER TABLE structure DROP FOREIGN KEY FK_6F0137EAB40A33C7');
        $this->addSql('ALTER TABLE structure DROP FOREIGN KEY FK_6F0137EA55808438');
        $this->addSql('ALTER TABLE structure DROP FOREIGN KEY FK_6F0137EAA2A63DB2');
        $this->addSql('ALTER TABLE class_school DROP FOREIGN KEY FK_36C29803C32A47EE');
        $this->addSql('ALTER TABLE document DROP FOREIGN KEY FK_D8698A76C32A47EE');
        $this->addSql('ALTER TABLE meet DROP FOREIGN KEY FK_E9F6D3CEC32A47EE');
        $this->addSql('ALTER TABLE package DROP FOREIGN KEY FK_DE686795C32A47EE');
        $this->addSql('ALTER TABLE person_school DROP FOREIGN KEY FK_653BEA0DC32A47EE');
        $this->addSql('ALTER TABLE student DROP FOREIGN KEY FK_B723AF33C32A47EE');
        $this->addSql('ALTER TABLE user_school DROP FOREIGN KEY FK_9CCCC186C32A47EE');
        $this->addSql('ALTER TABLE account DROP FOREIGN KEY FK_7D3656A42534008B');
        $this->addSql('ALTER TABLE account_slip DROP FOREIGN KEY FK_71AAC4522534008B');
        $this->addSql('ALTER TABLE person_member DROP FOREIGN KEY FK_EC41CACE2534008B');
        $this->addSql('ALTER TABLE school DROP FOREIGN KEY FK_F99EDABB2534008B');
        $this->addSql('ALTER TABLE appeal_course DROP FOREIGN KEY FK_8F2A2E3DCB944F1A');
        $this->addSql('ALTER TABLE class_period_student DROP FOREIGN KEY FK_8A4414AFCB944F1A');
        $this->addSql('ALTER TABLE package_student_period DROP FOREIGN KEY FK_A6F88096CB944F1A');
        $this->addSql('ALTER TABLE student_comment DROP FOREIGN KEY FK_7942E794CB944F1A');
        $this->addSql('ALTER TABLE teacher_course DROP FOREIGN KEY FK_315BD4C41807E1D');
        $this->addSql('ALTER TABLE teacher_class_period DROP FOREIGN KEY FK_9C3BA84441807E1D');
        $this->addSql('ALTER TABLE operation DROP FOREIGN KEY FK_1981A66DC3EF8F86');
        $this->addSql('ALTER TABLE type_operation DROP FOREIGN KEY FK_AD47E77D727ACA70');
        $this->addSql('ALTER TABLE account_slip DROP FOREIGN KEY FK_71AAC452F675F31B');
        $this->addSql('ALTER TABLE account_statement DROP FOREIGN KEY FK_E2A1892DF675F31B');
        $this->addSql('ALTER TABLE class_period DROP FOREIGN KEY FK_AE45C76F675F31B');
        $this->addSql('ALTER TABLE class_period_student DROP FOREIGN KEY FK_8A4414AFF675F31B');
        $this->addSql('ALTER TABLE class_school DROP FOREIGN KEY FK_36C29803F675F31B');
        $this->addSql('ALTER TABLE course DROP FOREIGN KEY FK_169E6FB9F675F31B');
        $this->addSql('ALTER TABLE diploma DROP FOREIGN KEY FK_EC218957F675F31B');
        $this->addSql('ALTER TABLE document DROP FOREIGN KEY FK_D8698A76F675F31B');
        $this->addSql('ALTER TABLE family DROP FOREIGN KEY FK_A5E6215BF675F31B');
        $this->addSql('ALTER TABLE meet DROP FOREIGN KEY FK_E9F6D3CEF675F31B');
        $this->addSql('ALTER TABLE meet DROP FOREIGN KEY FK_E9F6D3CE40C86FCE');
        $this->addSql('ALTER TABLE operation DROP FOREIGN KEY FK_1981A66DF675F31B');
        $this->addSql('ALTER TABLE operation DROP FOREIGN KEY FK_1981A66D40C86FCE');
        $this->addSql('ALTER TABLE package_student_period DROP FOREIGN KEY FK_A6F88096F675F31B');
        $this->addSql('ALTER TABLE period DROP FOREIGN KEY FK_C5B81ECEF675F31B');
        $this->addSql('ALTER TABLE person DROP FOREIGN KEY FK_34DCD176A76ED395');
        $this->addSql('ALTER TABLE person DROP FOREIGN KEY FK_34DCD176F675F31B');
        $this->addSql('ALTER TABLE person_member DROP FOREIGN KEY FK_EC41CACEF675F31B');
        $this->addSql('ALTER TABLE school DROP FOREIGN KEY FK_F99EDABBF675F31B');
        $this->addSql('ALTER TABLE structure DROP FOREIGN KEY FK_6F0137EAF675F31B');
        $this->addSql('ALTER TABLE student DROP FOREIGN KEY FK_B723AF33F675F31B');
        $this->addSql('ALTER TABLE student_comment DROP FOREIGN KEY FK_7942E794F675F31B');
        $this->addSql('ALTER TABLE teacher DROP FOREIGN KEY FK_B0F6A6D5F675F31B');
        $this->addSql('ALTER TABLE type_operation DROP FOREIGN KEY FK_AD47E77DF675F31B');
        $this->addSql('ALTER TABLE user DROP FOREIGN KEY FK_8D93D649F675F31B');
        $this->addSql('ALTER TABLE user_school DROP FOREIGN KEY FK_9CCCC186A76ED395');
        $this->addSql('ALTER TABLE validate DROP FOREIGN KEY FK_42123254F675F31B');
        $this->addSql('ALTER TABLE operation DROP FOREIGN KEY FK_1981A66D133D6627');
        $this->addSql('DROP TABLE account');
        $this->addSql('DROP TABLE account_slip');
        $this->addSql('DROP TABLE account_slip_document');
        $this->addSql('DROP TABLE account_statement');
        $this->addSql('DROP TABLE account_statement_document');
        $this->addSql('DROP TABLE appeal_course');
        $this->addSql('DROP TABLE class_period');
        $this->addSql('DROP TABLE class_period_student');
        $this->addSql('DROP TABLE class_school');
        $this->addSql('DROP TABLE course');
        $this->addSql('DROP TABLE diploma');
        $this->addSql('DROP TABLE document');
        $this->addSql('DROP TABLE family');
        $this->addSql('DROP TABLE grade');
        $this->addSql('DROP TABLE meet');
        $this->addSql('DROP TABLE operation');
        $this->addSql('DROP TABLE operation_document');
        $this->addSql('DROP TABLE operation_gender');
        $this->addSql('DROP TABLE package');
        $this->addSql('DROP TABLE package_student_period');
        $this->addSql('DROP TABLE payment_package_student');
        $this->addSql('DROP TABLE period');
        $this->addSql('DROP TABLE person');
        $this->addSql('DROP TABLE person_school');
        $this->addSql('DROP TABLE person_member');
        $this->addSql('DROP TABLE school');
        $this->addSql('DROP TABLE structure');
        $this->addSql('DROP TABLE student');
        $this->addSql('DROP TABLE student_comment');
        $this->addSql('DROP TABLE teacher');
        $this->addSql('DROP TABLE teacher_course');
        $this->addSql('DROP TABLE teacher_class_period');
        $this->addSql('DROP TABLE type_operation');
        $this->addSql('DROP TABLE user');
        $this->addSql('DROP TABLE user_school');
        $this->addSql('DROP TABLE validate');
    }
}
