<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260814213249 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE application (id INT AUTO_INCREMENT NOT NULL, status VARCHAR(255) NOT NULL, cover_letter LONGTEXT NOT NULL, review_note VARCHAR(255) NOT NULL, applied_at DATETIME NOT NULL, reviewed_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, withdrawn_at DATETIME NOT NULL, student_profile_id INT DEFAULT NULL, opportunity_id_id INT DEFAULT NULL, INDEX IDX_A45BDDC12125FF59 (student_profile_id), INDEX IDX_A45BDDC127D0903F (opportunity_id_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE application_status_history (id INT AUTO_INCREMENT NOT NULL, old_status VARCHAR(255) NOT NULL, new_status VARCHAR(255) NOT NULL, note VARCHAR(255) NOT NULL, created_at DATETIME NOT NULL, changed_by_id_id INT DEFAULT NULL, application_id_id INT DEFAULT NULL, INDEX IDX_48A559FE336C37F3 (changed_by_id_id), INDEX IDX_48A559FE9CD0792D (application_id_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE audit_log (id INT AUTO_INCREMENT NOT NULL, action VARCHAR(255) NOT NULL, entity_type VARCHAR(255) NOT NULL, entity_id VARCHAR(255) NOT NULL, payload JSON NOT NULL, created_at DATETIME NOT NULL, ip_address VARCHAR(255) NOT NULL, user_agent VARCHAR(255) NOT NULL, actor_id_id INT DEFAULT NULL, INDEX IDX_F6E1C0F55BC075C3 (actor_id_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE candidate_short_list (id INT AUTO_INCREMENT NOT NULL, note VARCHAR(255) NOT NULL, created_at DATETIME NOT NULL, company_id_id INT DEFAULT NULL, student_profile_id_id INT DEFAULT NULL, opportunity_id_id INT DEFAULT NULL, INDEX IDX_16C821B338B53C32 (company_id_id), INDEX IDX_16C821B3DD0CF91B (student_profile_id_id), INDEX IDX_16C821B327D0903F (opportunity_id_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE company_post (id INT AUTO_INCREMENT NOT NULL, title VARCHAR(255) NOT NULL, content VARCHAR(255) NOT NULL, category VARCHAR(255) NOT NULL, visibility VARCHAR(255) NOT NULL, image_url VARCHAR(255) NOT NULL, publied_at DATETIME NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, company_id_id INT DEFAULT NULL, author_id_id INT DEFAULT NULL, INDEX IDX_19E7766338B53C32 (company_id_id), INDEX IDX_19E7766369CCBE9A (author_id_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE conversation (id INT AUTO_INCREMENT NOT NULL, subject LONGTEXT NOT NULL, type VARCHAR(255) NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, conversation_participant_id_id INT DEFAULT NULL, INDEX IDX_8A8E26E91F97A432 (conversation_participant_id_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE conversation_participant (id INT AUTO_INCREMENT NOT NULL, joined_at DATETIME NOT NULL, last_read_at DATETIME NOT NULL, user_id INT DEFAULT NULL, INDEX IDX_39801661A76ED395 (user_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE language (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, level VARCHAR(255) NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, student_profile_id_id INT DEFAULT NULL, INDEX IDX_D4DB71B5DD0CF91B (student_profile_id_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE message (id INT AUTO_INCREMENT NOT NULL, body LONGTEXT NOT NULL, created_at DATETIME NOT NULL, edited_at DATETIME NOT NULL, is_deleted TINYINT NOT NULL, deleted_at DATETIME NOT NULL, conversation_id_id INT DEFAULT NULL, sender_id_id INT DEFAULT NULL, INDEX IDX_B6BD307F6B92BD7B (conversation_id_id), INDEX IDX_B6BD307F6061F7CF (sender_id_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE notification (id INT AUTO_INCREMENT NOT NULL, type VARCHAR(255) NOT NULL, title VARCHAR(255) NOT NULL, body LONGTEXT NOT NULL, data JSON NOT NULL, read_at DATETIME NOT NULL, created_at DATETIME NOT NULL, user_id_id INT DEFAULT NULL, INDEX IDX_BF5476CA9D86650F (user_id_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE opportunities (id INT AUTO_INCREMENT NOT NULL, title VARCHAR(255) NOT NULL, type VARCHAR(255) NOT NULL, department VARCHAR(255) NOT NULL, location VARCHAR(255) NOT NULL, remote_type VARCHAR(255) NOT NULL, salary_label VARCHAR(255) NOT NULL, description LONGTEXT NOT NULL, status VARCHAR(255) NOT NULL, is_enabled TINYINT NOT NULL, published_at DATETIME NOT NULL, application_dead_line DATETIME NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, is_deleted TINYINT NOT NULL, category VARCHAR(255) NOT NULL, experience_level VARCHAR(255) NOT NULL, number_of_positions VARCHAR(255) NOT NULL, company_id_id INT DEFAULT NULL, INDEX IDX_406D4DB038B53C32 (company_id_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE opportunity_requirement (id INT AUTO_INCREMENT NOT NULL, label VARCHAR(255) NOT NULL, type VARCHAR(255) NOT NULL, is_required TINYINT NOT NULL, created_at DATETIME NOT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE partnership (id INT AUTO_INCREMENT NOT NULL, status VARCHAR(255) NOT NULL, start_at DATETIME NOT NULL, ended_at DATETIME NOT NULL, notes VARCHAR(255) NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, company_id_id INT DEFAULT NULL, university_id_id INT DEFAULT NULL, INDEX IDX_8619D6AE38B53C32 (company_id_id), INDEX IDX_8619D6AE30068B48 (university_id_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE post_comments (id INT AUTO_INCREMENT NOT NULL, content VARCHAR(255) NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, post_id_id INT DEFAULT NULL, author_id_id INT DEFAULT NULL, INDEX IDX_E0731F8BE85F12B8 (post_id_id), INDEX IDX_E0731F8B69CCBE9A (author_id_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE post_reaction (id INT AUTO_INCREMENT NOT NULL, type VARCHAR(255) NOT NULL, created_at DATETIME NOT NULL, post_id_id INT DEFAULT NULL, user_id_id INT DEFAULT NULL, INDEX IDX_1B3A8E56E85F12B8 (post_id_id), INDEX IDX_1B3A8E569D86650F (user_id_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE student_documents (id INT AUTO_INCREMENT NOT NULL, type VARCHAR(255) NOT NULL, title VARCHAR(255) NOT NULL, file_name VARCHAR(255) NOT NULL, file_path VARCHAR(255) NOT NULL, mime_type VARCHAR(255) NOT NULL, file_size VARCHAR(255) NOT NULL, is_public TINYINT NOT NULL, uploaded_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, student_profile_id_id INT DEFAULT NULL, INDEX IDX_C5DEB308DD0CF91B (student_profile_id_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE student_profile (id INT AUTO_INCREMENT NOT NULL, profile_url VARCHAR(255) DEFAULT NULL, bio LONGTEXT DEFAULT NULL, skills JSON NOT NULL, profile_completion SMALLINT DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, full_name VARCHAR(255) NOT NULL, gpa VARCHAR(255) NOT NULL, gender VARCHAR(255) NOT NULL, user_id_id INT DEFAULT NULL, university_id_id INT DEFAULT NULL, UNIQUE INDEX UNIQ_6C611FF79D86650F (user_id_id), INDEX IDX_6C611FF730068B48 (university_id_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE system_setting (id INT AUTO_INCREMENT NOT NULL, value JSON NOT NULL, system_key VARCHAR(255) NOT NULL, updated_at DATETIME NOT NULL, created_at DATETIME NOT NULL, user_key_id_id INT DEFAULT NULL, INDEX IDX_7307C40B9D9A6E7C (user_key_id_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE training (id INT AUTO_INCREMENT NOT NULL, title VARCHAR(255) NOT NULL, type VARCHAR(255) NOT NULL, mode VARCHAR(255) NOT NULL, location VARCHAR(255) NOT NULL, duration_label VARCHAR(255) NOT NULL, seat_count VARCHAR(255) NOT NULL, description LONGTEXT NOT NULL, status VARCHAR(255) NOT NULL, start_at DATETIME NOT NULL, end_at DATETIME NOT NULL, published_at DATETIME NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, company_id_id INT DEFAULT NULL, INDEX IDX_D5128A8F38B53C32 (company_id_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE training_enrollment (id INT AUTO_INCREMENT NOT NULL, status VARCHAR(255) NOT NULL, enrolled_at DATETIME NOT NULL, completed_at DATETIME NOT NULL, training_id_id INT DEFAULT NULL, student_profile_id_id INT DEFAULT NULL, INDEX IDX_61626D87909E143A (training_id_id), INDEX IDX_61626D87DD0CF91B (student_profile_id_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE training_requirement (id INT AUTO_INCREMENT NOT NULL, label VARCHAR(255) NOT NULL, is_required TINYINT NOT NULL, is_deleted TINYINT NOT NULL, is_enabled TINYINT NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, training_id_id INT DEFAULT NULL, INDEX IDX_7F542F87909E143A (training_id_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE university_company (university_id INT NOT NULL, company_id INT NOT NULL, INDEX IDX_EF38AC78309D1878 (university_id), INDEX IDX_EF38AC78979B1AD6 (company_id), PRIMARY KEY (university_id, company_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('ALTER TABLE application ADD CONSTRAINT FK_A45BDDC12125FF59 FOREIGN KEY (student_profile_id) REFERENCES student_profile (id)');
        $this->addSql('ALTER TABLE application ADD CONSTRAINT FK_A45BDDC127D0903F FOREIGN KEY (opportunity_id_id) REFERENCES opportunities (id)');
        $this->addSql('ALTER TABLE application_status_history ADD CONSTRAINT FK_48A559FE336C37F3 FOREIGN KEY (changed_by_id_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE application_status_history ADD CONSTRAINT FK_48A559FE9CD0792D FOREIGN KEY (application_id_id) REFERENCES application (id)');
        $this->addSql('ALTER TABLE audit_log ADD CONSTRAINT FK_F6E1C0F55BC075C3 FOREIGN KEY (actor_id_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE candidate_short_list ADD CONSTRAINT FK_16C821B338B53C32 FOREIGN KEY (company_id_id) REFERENCES company (id)');
        $this->addSql('ALTER TABLE candidate_short_list ADD CONSTRAINT FK_16C821B3DD0CF91B FOREIGN KEY (student_profile_id_id) REFERENCES student_profile (id)');
        $this->addSql('ALTER TABLE candidate_short_list ADD CONSTRAINT FK_16C821B327D0903F FOREIGN KEY (opportunity_id_id) REFERENCES opportunities (id)');
        $this->addSql('ALTER TABLE company_post ADD CONSTRAINT FK_19E7766338B53C32 FOREIGN KEY (company_id_id) REFERENCES company (id)');
        $this->addSql('ALTER TABLE company_post ADD CONSTRAINT FK_19E7766369CCBE9A FOREIGN KEY (author_id_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE conversation ADD CONSTRAINT FK_8A8E26E91F97A432 FOREIGN KEY (conversation_participant_id_id) REFERENCES conversation_participant (id)');
        $this->addSql('ALTER TABLE conversation_participant ADD CONSTRAINT FK_39801661A76ED395 FOREIGN KEY (user_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE language ADD CONSTRAINT FK_D4DB71B5DD0CF91B FOREIGN KEY (student_profile_id_id) REFERENCES student_profile (id)');
        $this->addSql('ALTER TABLE message ADD CONSTRAINT FK_B6BD307F6B92BD7B FOREIGN KEY (conversation_id_id) REFERENCES conversation (id)');
        $this->addSql('ALTER TABLE message ADD CONSTRAINT FK_B6BD307F6061F7CF FOREIGN KEY (sender_id_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE notification ADD CONSTRAINT FK_BF5476CA9D86650F FOREIGN KEY (user_id_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE opportunities ADD CONSTRAINT FK_406D4DB038B53C32 FOREIGN KEY (company_id_id) REFERENCES company (id)');
        $this->addSql('ALTER TABLE partnership ADD CONSTRAINT FK_8619D6AE38B53C32 FOREIGN KEY (company_id_id) REFERENCES company (id)');
        $this->addSql('ALTER TABLE partnership ADD CONSTRAINT FK_8619D6AE30068B48 FOREIGN KEY (university_id_id) REFERENCES university (id)');
        $this->addSql('ALTER TABLE post_comments ADD CONSTRAINT FK_E0731F8BE85F12B8 FOREIGN KEY (post_id_id) REFERENCES company_post (id)');
        $this->addSql('ALTER TABLE post_comments ADD CONSTRAINT FK_E0731F8B69CCBE9A FOREIGN KEY (author_id_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE post_reaction ADD CONSTRAINT FK_1B3A8E56E85F12B8 FOREIGN KEY (post_id_id) REFERENCES company_post (id)');
        $this->addSql('ALTER TABLE post_reaction ADD CONSTRAINT FK_1B3A8E569D86650F FOREIGN KEY (user_id_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE student_documents ADD CONSTRAINT FK_C5DEB308DD0CF91B FOREIGN KEY (student_profile_id_id) REFERENCES student_profile (id)');
        $this->addSql('ALTER TABLE student_profile ADD CONSTRAINT FK_6C611FF79D86650F FOREIGN KEY (user_id_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE student_profile ADD CONSTRAINT FK_6C611FF730068B48 FOREIGN KEY (university_id_id) REFERENCES university (id)');
        $this->addSql('ALTER TABLE system_setting ADD CONSTRAINT FK_7307C40B9D9A6E7C FOREIGN KEY (user_key_id_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE training ADD CONSTRAINT FK_D5128A8F38B53C32 FOREIGN KEY (company_id_id) REFERENCES company (id)');
        $this->addSql('ALTER TABLE training_enrollment ADD CONSTRAINT FK_61626D87909E143A FOREIGN KEY (training_id_id) REFERENCES training (id)');
        $this->addSql('ALTER TABLE training_enrollment ADD CONSTRAINT FK_61626D87DD0CF91B FOREIGN KEY (student_profile_id_id) REFERENCES student_profile (id)');
        $this->addSql('ALTER TABLE training_requirement ADD CONSTRAINT FK_7F542F87909E143A FOREIGN KEY (training_id_id) REFERENCES training (id)');
        $this->addSql('ALTER TABLE university_company ADD CONSTRAINT FK_EF38AC78309D1878 FOREIGN KEY (university_id) REFERENCES university (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE university_company ADD CONSTRAINT FK_EF38AC78979B1AD6 FOREIGN KEY (company_id) REFERENCES company (id) ON DELETE CASCADE');
        $this->addSql('DROP TABLE classes');
        $this->addSql('DROP TABLE mark_type');
        $this->addSql('DROP TABLE student');
        $this->addSql('DROP TABLE student_skill');
        $this->addSql('ALTER TABLE academic_class ADD CONSTRAINT FK_1903745A64E7214B FOREIGN KEY (department_id_id) REFERENCES department (id)');
        $this->addSql('ALTER TABLE academic_program ADD CONSTRAINT FK_1763BD2264E7214B FOREIGN KEY (department_id_id) REFERENCES department (id)');
        $this->addSql('ALTER TABLE address ADD latitude VARCHAR(255) NOT NULL, ADD longitude VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE company ADD description VARCHAR(255) NOT NULL, ADD website_url VARCHAR(255) DEFAULT NULL, ADD status VARCHAR(255) NOT NULL, ADD registration_number VARCHAR(255) NOT NULL, ADD tax_id VARCHAR(255) NOT NULL, ADD verified_at DATETIME NOT NULL, ADD created_at DATETIME NOT NULL, ADD updated_at DATETIME NOT NULL');
        $this->addSql('ALTER TABLE department ADD code VARCHAR(255) NOT NULL, ADD description VARCHAR(255) NOT NULL, ADD university_id_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE department ADD CONSTRAINT FK_CD1DE18A30068B48 FOREIGN KEY (university_id_id) REFERENCES university (id)');
        $this->addSql('CREATE INDEX IDX_CD1DE18A30068B48 ON department (university_id_id)');
        $this->addSql('ALTER TABLE skills ADD updated_at DATETIME NOT NULL, ADD is_deleted TINYINT NOT NULL, ADD student_profile_id_id INT DEFAULT NULL, CHANGE category level VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE skills ADD CONSTRAINT FK_D5311670DD0CF91B FOREIGN KEY (student_profile_id_id) REFERENCES student_profile (id)');
        $this->addSql('CREATE INDEX IDX_D5311670DD0CF91B ON skills (student_profile_id_id)');
        $this->addSql('ALTER TABLE university ADD description LONGTEXT NOT NULL, ADD website_url VARCHAR(255) DEFAULT NULL, ADD status VARCHAR(100) NOT NULL, ADD registration_number VARCHAR(255) NOT NULL, ADD verified_at DATETIME NOT NULL, ADD updated_at DATETIME NOT NULL, ADD logo_url VARCHAR(255) NOT NULL, ADD is_deleted TINYINT NOT NULL, ADD user_id_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE university ADD CONSTRAINT FK_A07A85EC9D86650F FOREIGN KEY (user_id_id) REFERENCES `user` (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_A07A85EC9D86650F ON university (user_id_id)');
        $this->addSql('ALTER TABLE user ADD updated_at DATETIME NOT NULL, ADD last_login_at DATETIME NOT NULL, ADD is_actived TINYINT NOT NULL, ADD user_type_id_id INT DEFAULT NULL, DROP roles');
        $this->addSql('ALTER TABLE user ADD CONSTRAINT FK_8D93D649D62FDF4C FOREIGN KEY (user_type_id_id) REFERENCES user_type (id)');
        $this->addSql('CREATE INDEX IDX_8D93D649D62FDF4C ON user (user_type_id_id)');
        $this->addSql('ALTER TABLE user_type ADD is_deleted TINYINT NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE classes (id INT AUTO_INCREMENT NOT NULL, field_name VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, year VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, created_at DATETIME NOT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE mark_type (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, is_enabled TINYINT NOT NULL, created_at DATETIME NOT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE student (id INT AUTO_INCREMENT NOT NULL, first_name VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, last_name VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, availability VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, profile_score VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, birth_date VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, is_actived TINYINT NOT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE student_skill (id INT AUTO_INCREMENT NOT NULL, level VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE application DROP FOREIGN KEY FK_A45BDDC12125FF59');
        $this->addSql('ALTER TABLE application DROP FOREIGN KEY FK_A45BDDC127D0903F');
        $this->addSql('ALTER TABLE application_status_history DROP FOREIGN KEY FK_48A559FE336C37F3');
        $this->addSql('ALTER TABLE application_status_history DROP FOREIGN KEY FK_48A559FE9CD0792D');
        $this->addSql('ALTER TABLE audit_log DROP FOREIGN KEY FK_F6E1C0F55BC075C3');
        $this->addSql('ALTER TABLE candidate_short_list DROP FOREIGN KEY FK_16C821B338B53C32');
        $this->addSql('ALTER TABLE candidate_short_list DROP FOREIGN KEY FK_16C821B3DD0CF91B');
        $this->addSql('ALTER TABLE candidate_short_list DROP FOREIGN KEY FK_16C821B327D0903F');
        $this->addSql('ALTER TABLE company_post DROP FOREIGN KEY FK_19E7766338B53C32');
        $this->addSql('ALTER TABLE company_post DROP FOREIGN KEY FK_19E7766369CCBE9A');
        $this->addSql('ALTER TABLE conversation DROP FOREIGN KEY FK_8A8E26E91F97A432');
        $this->addSql('ALTER TABLE conversation_participant DROP FOREIGN KEY FK_39801661A76ED395');
        $this->addSql('ALTER TABLE language DROP FOREIGN KEY FK_D4DB71B5DD0CF91B');
        $this->addSql('ALTER TABLE message DROP FOREIGN KEY FK_B6BD307F6B92BD7B');
        $this->addSql('ALTER TABLE message DROP FOREIGN KEY FK_B6BD307F6061F7CF');
        $this->addSql('ALTER TABLE notification DROP FOREIGN KEY FK_BF5476CA9D86650F');
        $this->addSql('ALTER TABLE opportunities DROP FOREIGN KEY FK_406D4DB038B53C32');
        $this->addSql('ALTER TABLE partnership DROP FOREIGN KEY FK_8619D6AE38B53C32');
        $this->addSql('ALTER TABLE partnership DROP FOREIGN KEY FK_8619D6AE30068B48');
        $this->addSql('ALTER TABLE post_comments DROP FOREIGN KEY FK_E0731F8BE85F12B8');
        $this->addSql('ALTER TABLE post_comments DROP FOREIGN KEY FK_E0731F8B69CCBE9A');
        $this->addSql('ALTER TABLE post_reaction DROP FOREIGN KEY FK_1B3A8E56E85F12B8');
        $this->addSql('ALTER TABLE post_reaction DROP FOREIGN KEY FK_1B3A8E569D86650F');
        $this->addSql('ALTER TABLE student_documents DROP FOREIGN KEY FK_C5DEB308DD0CF91B');
        $this->addSql('ALTER TABLE student_profile DROP FOREIGN KEY FK_6C611FF79D86650F');
        $this->addSql('ALTER TABLE student_profile DROP FOREIGN KEY FK_6C611FF730068B48');
        $this->addSql('ALTER TABLE system_setting DROP FOREIGN KEY FK_7307C40B9D9A6E7C');
        $this->addSql('ALTER TABLE training DROP FOREIGN KEY FK_D5128A8F38B53C32');
        $this->addSql('ALTER TABLE training_enrollment DROP FOREIGN KEY FK_61626D87909E143A');
        $this->addSql('ALTER TABLE training_enrollment DROP FOREIGN KEY FK_61626D87DD0CF91B');
        $this->addSql('ALTER TABLE training_requirement DROP FOREIGN KEY FK_7F542F87909E143A');
        $this->addSql('ALTER TABLE university_company DROP FOREIGN KEY FK_EF38AC78309D1878');
        $this->addSql('ALTER TABLE university_company DROP FOREIGN KEY FK_EF38AC78979B1AD6');
        $this->addSql('DROP TABLE application');
        $this->addSql('DROP TABLE application_status_history');
        $this->addSql('DROP TABLE audit_log');
        $this->addSql('DROP TABLE candidate_short_list');
        $this->addSql('DROP TABLE company_post');
        $this->addSql('DROP TABLE conversation');
        $this->addSql('DROP TABLE conversation_participant');
        $this->addSql('DROP TABLE language');
        $this->addSql('DROP TABLE message');
        $this->addSql('DROP TABLE notification');
        $this->addSql('DROP TABLE opportunities');
        $this->addSql('DROP TABLE opportunity_requirement');
        $this->addSql('DROP TABLE partnership');
        $this->addSql('DROP TABLE post_comments');
        $this->addSql('DROP TABLE post_reaction');
        $this->addSql('DROP TABLE student_documents');
        $this->addSql('DROP TABLE student_profile');
        $this->addSql('DROP TABLE system_setting');
        $this->addSql('DROP TABLE training');
        $this->addSql('DROP TABLE training_enrollment');
        $this->addSql('DROP TABLE training_requirement');
        $this->addSql('DROP TABLE university_company');
        $this->addSql('ALTER TABLE academic_class DROP FOREIGN KEY FK_1903745A64E7214B');
        $this->addSql('ALTER TABLE academic_program DROP FOREIGN KEY FK_1763BD2264E7214B');
        $this->addSql('ALTER TABLE address DROP latitude, DROP longitude');
        $this->addSql('ALTER TABLE company DROP description, DROP website_url, DROP status, DROP registration_number, DROP tax_id, DROP verified_at, DROP created_at, DROP updated_at');
        $this->addSql('ALTER TABLE department DROP FOREIGN KEY FK_CD1DE18A30068B48');
        $this->addSql('DROP INDEX IDX_CD1DE18A30068B48 ON department');
        $this->addSql('ALTER TABLE department DROP code, DROP description, DROP university_id_id');
        $this->addSql('ALTER TABLE skills DROP FOREIGN KEY FK_D5311670DD0CF91B');
        $this->addSql('DROP INDEX IDX_D5311670DD0CF91B ON skills');
        $this->addSql('ALTER TABLE skills DROP updated_at, DROP is_deleted, DROP student_profile_id_id, CHANGE level category VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE university DROP FOREIGN KEY FK_A07A85EC9D86650F');
        $this->addSql('DROP INDEX UNIQ_A07A85EC9D86650F ON university');
        $this->addSql('ALTER TABLE university DROP description, DROP website_url, DROP status, DROP registration_number, DROP verified_at, DROP updated_at, DROP logo_url, DROP is_deleted, DROP user_id_id');
        $this->addSql('ALTER TABLE `user` DROP FOREIGN KEY FK_8D93D649D62FDF4C');
        $this->addSql('DROP INDEX IDX_8D93D649D62FDF4C ON `user`');
        $this->addSql('ALTER TABLE `user` ADD roles JSON NOT NULL, DROP updated_at, DROP last_login_at, DROP is_actived, DROP user_type_id_id');
        $this->addSql('ALTER TABLE user_type DROP is_deleted');
    }
}
