<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260906103000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Adds missing student_profile columns mapped by StudentProfile entity (program, faculty, internship_cycle, academic_year).';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE student_profile ADD program VARCHAR(255) DEFAULT NULL, ADD faculty VARCHAR(255) DEFAULT NULL, ADD internship_cycle VARCHAR(255) DEFAULT NULL, ADD academic_year VARCHAR(255) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE student_profile DROP program, DROP faculty, DROP internship_cycle, DROP academic_year');
    }
}
