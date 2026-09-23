<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260902011507 extends AbstractMigration
{
    private const FALLBACK_UNIVERSITY_NAME = 'Unknown University (Backfill)';

    public function getDescription(): string
    {
        return 'Adds student registration request fields, backfills missing student_profile relations/profile URL, and enforces non-null ownership relations.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql("ALTER TABLE student_profile
            ADD status VARCHAR(20) NOT NULL DEFAULT 'pending',
            ADD is_approved TINYINT(1) NOT NULL DEFAULT 0,
            ADD verification_data JSON DEFAULT NULL,
            ADD review_reason VARCHAR(255) DEFAULT NULL,
            ADD review_note LONGTEXT DEFAULT NULL,
            ADD reviewed_at DATETIME DEFAULT NULL");

        $studentRoleId = $this->findOrCreateUserType('Student', 'Default student role');
        $fallbackUniversityId = $this->findOrCreateFallbackUniversity($studentRoleId);

        $orphanProfiles = $this->connection->fetchAllAssociative('SELECT id FROM student_profile WHERE user_id_id IS NULL');
        foreach ($orphanProfiles as $row) {
            $profileId = (int) $row['id'];
            $generatedEmail = sprintf('backfill.student.%d@local.invalid', $profileId);

            $this->connection->insert('user', [
                'email' => $generatedEmail,
                'password' => '$2y$13$BackfillOnly.N1QXIecf6cOMSlL8EP09JjAORPThO0cH2fWfLZ4lu',
                'status' => 'active',
                'create_at' => (new \DateTimeImmutable())->format('Y-m-d H:i:s'),
                'phone' => '0000000000',
                'updated_at' => (new \DateTimeImmutable())->format('Y-m-d H:i:s'),
                'last_login_at' => (new \DateTimeImmutable())->format('Y-m-d H:i:s'),
                'is_actived' => 1,
                'address_id_id' => null,
                'user_type_id_id' => $studentRoleId,
            ]);

            $userId = (int) $this->connection->lastInsertId();
            $this->connection->update('student_profile', ['user_id_id' => $userId], ['id' => $profileId]);
        }

        $this->addSql('UPDATE student_profile SET university_id_id = :fallbackUniversity WHERE university_id_id IS NULL', [
            'fallbackUniversity' => $fallbackUniversityId,
        ]);

        $this->addSql("UPDATE student_profile
            SET profile_url = CONCAT('https://ui-avatars.com/api/?background=0D8ABC&color=fff&name=Student+', id)
            WHERE profile_url IS NULL OR TRIM(profile_url) = ''");

        $this->addSql("UPDATE student_profile
            SET status = 'approved',
                is_approved = 1
            WHERE status IS NULL OR TRIM(status) = '' OR status = 'pending'");

        $this->addSql("UPDATE student_profile
            SET verification_data = JSON_OBJECT(
                'email', NULL,
                'phone', NULL,
                'program', NULL,
                'level', NULL,
                'universityName', NULL,
                'universityEmail', NULL
            )
            WHERE verification_data IS NULL");

        $this->addSql('ALTER TABLE student_profile CHANGE user_id_id user_id_id INT NOT NULL, CHANGE university_id_id university_id_id INT NOT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE student_profile CHANGE user_id_id user_id_id INT DEFAULT NULL, CHANGE university_id_id university_id_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE student_profile DROP status, DROP is_approved, DROP verification_data, DROP review_reason, DROP review_note, DROP reviewed_at');
    }

    private function findOrCreateUserType(string $name, string $description): int
    {
        $id = $this->connection->fetchOne('SELECT id FROM user_type WHERE UPPER(name) = UPPER(:name) LIMIT 1', [
            'name' => $name,
        ]);

        if ($id !== false && $id !== null) {
            return (int) $id;
        }

        $this->connection->insert('user_type', [
            'name' => $name,
            'permission' => json_encode([], JSON_THROW_ON_ERROR),
            'description' => $description,
            'is_enabled' => 1,
            'is_deleted' => 0,
        ]);

        return (int) $this->connection->lastInsertId();
    }

    private function findOrCreateFallbackUniversity(int $studentRoleId): int
    {
        $existingId = $this->connection->fetchOne('SELECT id FROM university WHERE name = :name LIMIT 1', [
            'name' => self::FALLBACK_UNIVERSITY_NAME,
        ]);

        if ($existingId !== false && $existingId !== null) {
            return (int) $existingId;
        }

        $universityRoleId = $this->connection->fetchOne('SELECT id FROM user_type WHERE UPPER(name) = :name LIMIT 1', [
            'name' => 'UNIVERSITY',
        ]);

        $now = (new \DateTimeImmutable())->format('Y-m-d H:i:s');
        $universityEmail = sprintf('backfill.university.%s@local.invalid', bin2hex(random_bytes(4)));

        $this->connection->insert('user', [
            'email' => $universityEmail,
            'password' => '$2y$13$BackfillOnly.N1QXIecf6cOMSlL8EP09JjAORPThO0cH2fWfLZ4lu',
            'status' => 'active',
            'create_at' => $now,
            'phone' => '0000000000',
            'updated_at' => $now,
            'last_login_at' => $now,
            'is_actived' => 1,
            'address_id_id' => null,
            'user_type_id_id' => $universityRoleId !== false && $universityRoleId !== null ? (int) $universityRoleId : $studentRoleId,
        ]);

        $fallbackUserId = (int) $this->connection->lastInsertId();

        $this->connection->insert('university', [
            'name' => self::FALLBACK_UNIVERSITY_NAME,
            'type' => 'unknown',
            'accreditation_number' => 'BACKFILL',
            'ranking_score' => '0',
            'is_approved' => 0,
            'created_at' => $now,
            'description' => 'Auto-generated fallback university for student profile backfill.',
            'website_url' => null,
            'status' => 'pending',
            'registration_number' => 'BACKFILL',
            'verified_at' => null,
            'updated_at' => $now,
            'logo_url' => '/uploads/university/default.png',
            'is_deleted' => 0,
            'user_id' => $fallbackUserId,
        ]);

        return (int) $this->connection->lastInsertId();
    }
}
