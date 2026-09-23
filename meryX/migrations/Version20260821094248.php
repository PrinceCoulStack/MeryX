<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260821094248 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE academic_record (id INT AUTO_INCREMENT NOT NULL, institution_name VARCHAR(255) NOT NULL, degree VARCHAR(255) NOT NULL, field_of_study VARCHAR(255) DEFAULT NULL, start_date DATE NOT NULL, end_date DATE DEFAULT NULL, gpa VARCHAR(16) DEFAULT NULL, description LONGTEXT DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, student_profile_id_id INT DEFAULT NULL, INDEX IDX_70CEE1C3DD0CF91B (student_profile_id_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE student_project (id INT AUTO_INCREMENT NOT NULL, title VARCHAR(255) NOT NULL, description LONGTEXT DEFAULT NULL, role VARCHAR(255) DEFAULT NULL, technologies JSON NOT NULL, project_url VARCHAR(255) DEFAULT NULL, started_at DATE DEFAULT NULL, ended_at DATE DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, student_profile_id_id INT DEFAULT NULL, INDEX IDX_C2856516DD0CF91B (student_profile_id_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('ALTER TABLE academic_record ADD CONSTRAINT FK_70CEE1C3DD0CF91B FOREIGN KEY (student_profile_id_id) REFERENCES student_profile (id)');
        $this->addSql('ALTER TABLE student_project ADD CONSTRAINT FK_C2856516DD0CF91B FOREIGN KEY (student_profile_id_id) REFERENCES student_profile (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE academic_record DROP FOREIGN KEY FK_70CEE1C3DD0CF91B');
        $this->addSql('ALTER TABLE student_project DROP FOREIGN KEY FK_C2856516DD0CF91B');
        $this->addSql('DROP TABLE academic_record');
        $this->addSql('DROP TABLE student_project');
    }
}
