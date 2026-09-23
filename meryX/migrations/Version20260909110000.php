<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260909110000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Adds candidatures and saved_opportunities tables with constraints and indexes for application tracking.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql("CREATE TABLE candidatures (id INT AUTO_INCREMENT NOT NULL, opportunity_id INT NOT NULL, student_id INT NOT NULL, status VARCHAR(50) NOT NULL DEFAULT 'applied', applied_date DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)', last_updated DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)', feedback LONGTEXT DEFAULT NULL, notes LONGTEXT DEFAULT NULL, interview_date DATETIME DEFAULT NULL COMMENT '(DC2Type:datetime_immutable)', score INT NOT NULL DEFAULT 0, created_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)', updated_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)', INDEX idx_candidatures_student_id (student_id), INDEX idx_candidatures_status (status), INDEX idx_candidatures_created_at (created_at), INDEX idx_candidatures_student_status (student_id, status), UNIQUE INDEX uniq_candidature_opportunity_student (opportunity_id, student_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB");
        $this->addSql('ALTER TABLE candidatures ADD CONSTRAINT FK_CANDIDATURE_OPPORTUNITY FOREIGN KEY (opportunity_id) REFERENCES opportunities (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE candidatures ADD CONSTRAINT FK_CANDIDATURE_STUDENT FOREIGN KEY (student_id) REFERENCES student_profile (id) ON DELETE CASCADE');

        $this->addSql("CREATE TABLE saved_opportunities (id INT AUTO_INCREMENT NOT NULL, opportunity_id INT NOT NULL, student_id INT NOT NULL, notes LONGTEXT DEFAULT NULL, saved_date DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)', created_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)', updated_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)', INDEX idx_saved_opportunities_student_id (student_id), INDEX idx_saved_opportunities_created_at (created_at), UNIQUE INDEX uniq_saved_opportunity_student (opportunity_id, student_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB");
        $this->addSql('ALTER TABLE saved_opportunities ADD CONSTRAINT FK_SAVED_OPPORTUNITY_OPPORTUNITY FOREIGN KEY (opportunity_id) REFERENCES opportunities (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE saved_opportunities ADD CONSTRAINT FK_SAVED_OPPORTUNITY_STUDENT FOREIGN KEY (student_id) REFERENCES student_profile (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE candidatures DROP FOREIGN KEY FK_CANDIDATURE_OPPORTUNITY');
        $this->addSql('ALTER TABLE candidatures DROP FOREIGN KEY FK_CANDIDATURE_STUDENT');
        $this->addSql('ALTER TABLE saved_opportunities DROP FOREIGN KEY FK_SAVED_OPPORTUNITY_OPPORTUNITY');
        $this->addSql('ALTER TABLE saved_opportunities DROP FOREIGN KEY FK_SAVED_OPPORTUNITY_STUDENT');
        $this->addSql('DROP TABLE candidatures');
        $this->addSql('DROP TABLE saved_opportunities');
    }
}
