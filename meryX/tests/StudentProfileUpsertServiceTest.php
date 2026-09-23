<?php

namespace App\Tests;

use App\Dto\StudentProfileUpsertInput;
use App\Entity\Skills;
use App\Entity\StudentProfile;
use App\Entity\University;
use App\Entity\User;
use App\Exception\DomainValidationException;
use App\Repository\UniversityRepository;
use App\Repository\UserRepository;
use App\Service\StudentProfile\StudentProfileUpsertService;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;

final class StudentProfileUpsertServiceTest extends TestCase
{
    public function testCreateProfileWithNestedChildren(): void
    {
        $entityManager = $this->createEntityManagerMock();
        $entityManager->expects($this->once())->method('beginTransaction');
        $entityManager->expects($this->once())->method('persist');
        $entityManager->expects($this->once())->method('flush');
        $entityManager->expects($this->once())->method('commit');
        $entityManager->expects($this->never())->method('rollback');

        $userRepository = $this->createMock(UserRepository::class);
        $user = new User();
        $this->setEntityId($user, 50);
        $user->setEmail('jane@example.test');
        $userRepository->method('find')->willReturn($user);

        $university = new University();
        $this->setEntityId($university, 10);

        $universityRepository = $this->createMock(UniversityRepository::class);
        $universityRepository->method('find')->willReturn($university);

        $service = new StudentProfileUpsertService($entityManager, $userRepository, $universityRepository);

        $input = new StudentProfileUpsertInput();
        $input->fullName = 'Jane Student';
        $input->gpa = '3.75';
        $input->gender = 'female';
        $input->userId = 50;
        $input->universityId = 10;
        $input->skills = [
            ['name' => 'PHP', 'level' => 'advanced'],
            ['name' => 'Vue', 'level' => 'intermediate'],
        ];
        $input->languages = [
            ['name' => 'English', 'level' => 'native'],
        ];
        $input->projects = [
            ['title' => 'Career Portal', 'technologies' => ['Symfony', 'Vue']],
        ];
        $input->academicRecords = [
            ['institutionName' => 'Example University', 'degree' => 'BSc CS', 'startDate' => '2022-09-01'],
        ];

        $profile = $service->create($input);

        $this->assertSame('Jane Student', $profile->getFullName());
        $this->assertSame('3.75', $profile->getGpa());
        $this->assertSame(50, $profile->getUserId()?->getId());
        $this->assertSame(10, $profile->getUniversityId()?->getId());
        $this->assertStringContainsString('ui-avatars.com', (string) $profile->getProfileUrl());
        $this->assertCount(2, $profile->getSkill());
        $this->assertCount(1, $profile->getLanguages());
        $this->assertCount(1, $profile->getStudentProjects());
        $this->assertCount(1, $profile->getAcademicRecords());
        $this->assertSame(['PHP', 'Vue'], $profile->getSkills());
    }

    public function testUpdateProfileReplacesSkillCollection(): void
    {
        $entityManager = $this->createEntityManagerMock();
        $entityManager->expects($this->once())->method('beginTransaction');
        $entityManager->expects($this->once())->method('flush');
        $entityManager->expects($this->once())->method('commit');
        $entityManager->expects($this->never())->method('rollback');

        $userRepository = $this->createMock(UserRepository::class);
        $universityRepository = $this->createMock(UniversityRepository::class);

        $service = new StudentProfileUpsertService($entityManager, $userRepository, $universityRepository);

        $profile = new StudentProfile();
        $profile->setFullName('Old Name');
        $profile->setGpa('3.00');
        $profile->setGender('male');
        $profile->setUserId(new User());
        $profileUniversity = new University();
        $profile->setUniversityId($profileUniversity);
        $oldSkill = new Skills();
        $oldSkill->setName('Legacy Skill');
        $oldSkill->setLevel('beginner');
        $oldSkill->setIsEnabled(true);
        $oldSkill->setIsDeleted(false);
        $oldSkill->setCreatedAt(new \DateTimeImmutable());
        $oldSkill->setUpdatedAt(new \DateTimeImmutable());
        $profile->addSkill($oldSkill);

        $input = new StudentProfileUpsertInput();
        $input->skills = [
            ['name' => 'Symfony', 'level' => 'expert'],
        ];

        $service->update($profile, $input);

        $this->assertCount(1, $profile->getSkill());
        $this->assertSame('Symfony', $profile->getSkill()->first()->getName());
        $this->assertSame(['Symfony'], $profile->getSkills());
    }

    public function testInvalidPayloadThrowsDomainValidationException(): void
    {
        $entityManager = $this->createEntityManagerMock();
        $entityManager->expects($this->once())->method('beginTransaction');
        $entityManager->expects($this->once())->method('rollback');
        $entityManager->expects($this->never())->method('flush');
        $entityManager->expects($this->never())->method('commit');

        $userRepository = $this->createMock(UserRepository::class);
        $universityRepository = $this->createMock(UniversityRepository::class);

        $service = new StudentProfileUpsertService($entityManager, $userRepository, $universityRepository);

        $input = new StudentProfileUpsertInput();
        $input->fullName = 'Bad GPA';
        $input->gpa = '8.99';
        $input->gender = 'unknown';
        $input->skills = [
            ['name' => 'PHP', 'level' => 'wrong-level'],
        ];

        $this->expectException(DomainValidationException::class);
        $service->create($input);
    }

    public function testUsesBioAcademicAndPersonalFallbackValues(): void
    {
        $entityManager = $this->createEntityManagerMock();
        $entityManager->expects($this->once())->method('beginTransaction');
        $entityManager->expects($this->once())->method('persist');
        $entityManager->expects($this->once())->method('flush');
        $entityManager->expects($this->once())->method('commit');
        $entityManager->expects($this->never())->method('rollback');

        $userRepository = $this->createMock(UserRepository::class);
        $user = new User();
        $user->setEmail('bio@example.test');
        $userRepository->method('find')->willReturn($user);

        $university = new University();
        $this->setEntityId($university, 11);
        $universityRepository = $this->createMock(UniversityRepository::class);
        $universityRepository->method('find')->willReturn($university);

        $service = new StudentProfileUpsertService($entityManager, $userRepository, $universityRepository);

        $input = new StudentProfileUpsertInput();
        $input->userId = 21;
        $input->bio = [
            'personal' => [
                'fullName' => 'Bio Name',
                'gender' => 'female',
            ],
            'academic' => [
                'gpa' => '3.20',
                'profileCompletion' => 75,
                'universityId' => 11,
            ],
            'radar' => [
                'academic' => [],
                'certificate' => [],
                'numerique' => [],
                'langue' => [],
                'stage' => [],
            ],
        ];

        $profile = $service->create($input);

        $this->assertSame('Bio Name', $profile->getFullName());
        $this->assertSame('3.20', $profile->getGpa());
        $this->assertSame(75, $profile->getProfileCompletion());
        $this->assertSame(11, $profile->getUniversityId()?->getId());

        $storedBio = json_decode((string) $profile->getBio(), true);
        $this->assertIsArray($storedBio);
        $this->assertSame('female', $storedBio['personal']['gender']);
    }

    public function testCreateProfileAcceptsLegacyFlatSkillStrings(): void
    {
        $entityManager = $this->createEntityManagerMock();
        $entityManager->expects($this->once())->method('beginTransaction');
        $entityManager->expects($this->once())->method('persist');
        $entityManager->expects($this->once())->method('flush');
        $entityManager->expects($this->once())->method('commit');
        $entityManager->expects($this->never())->method('rollback');

        $userRepository = $this->createMock(UserRepository::class);
        $user = new User();
        $user->setEmail('legacy@example.test');
        $userRepository->method('find')->willReturn($user);

        $university = new University();
        $this->setEntityId($university, 17);
        $universityRepository = $this->createMock(UniversityRepository::class);
        $universityRepository->method('find')->willReturn($university);

        $service = new StudentProfileUpsertService($entityManager, $userRepository, $universityRepository);

        $input = new StudentProfileUpsertInput();
        $input->fullName = 'Legacy Student';
        $input->gpa = '3.50';
        $input->gender = 'male';
        $input->userId = 91;
        $input->universityId = 17;
        $input->skills = ['PHP', 'Vue'];

        $profile = $service->create($input);

        $this->assertSame(['PHP', 'Vue'], $profile->getSkills());
        $this->assertCount(0, $profile->getSkill());

        $storedBio = json_decode((string) $profile->getBio(), true);
        $this->assertSame('Legacy Student', $storedBio['personal']['fullName']);
        $this->assertSame('3.50', $storedBio['academic']['gpa']);
    }

    public function testMissingRelationsAreRejected(): void
    {
        $entityManager = $this->createEntityManagerMock();
        $entityManager->expects($this->once())->method('beginTransaction');
        $entityManager->expects($this->once())->method('persist');
        $entityManager->expects($this->once())->method('rollback');
        $entityManager->expects($this->never())->method('commit');

        $userRepository = $this->createMock(UserRepository::class);
        $universityRepository = $this->createMock(UniversityRepository::class);
        $service = new StudentProfileUpsertService($entityManager, $userRepository, $universityRepository);

        $input = new StudentProfileUpsertInput();
        $input->fullName = 'Relationless Student';
        $input->gpa = '3.00';
        $input->gender = 'female';

        try {
            $service->create($input);
            $this->fail('Expected DomainValidationException to be thrown.');
        } catch (DomainValidationException $exception) {
            $this->assertSame(['User relation is required'], $exception->getErrors()['userId']);
            $this->assertSame(['University relation is required'], $exception->getErrors()['academic.universityId']);
        }
    }

    public function testUpdatePersistsNormalizedSettingsFields(): void
    {
        $entityManager = $this->createEntityManagerMock();
        $entityManager->expects($this->once())->method('beginTransaction');
        $entityManager->expects($this->once())->method('flush');
        $entityManager->expects($this->once())->method('commit');
        $entityManager->expects($this->never())->method('rollback');
        $entityManager->expects($this->atLeastOnce())->method('persist');

        $userRepository = $this->createMock(UserRepository::class);
        $universityRepository = $this->createMock(UniversityRepository::class);
        $service = new StudentProfileUpsertService($entityManager, $userRepository, $universityRepository);

        $user = new User();
        $user->setEmail('old@example.test');
        $user->setPhone('+100');

        $university = new University();
        $this->setEntityId($university, 4);

        $profile = new StudentProfile();
        $profile->setUserId($user);
        $profile->setUniversityId($university);
        $profile->setFullName('Normalized Student');
        $profile->setGpa('3.00');
        $profile->setGender('female');

        $input = new StudentProfileUpsertInput();
        $input->email = 'new@example.test';
        $input->phone = '+212600000099';
        $input->program = 'Computer Science';
        $input->level = 'M2';
        $input->academicYear = '2026/2027';
        $input->faculty = 'Engineering';
        $input->department = 'Software';
        $input->studentId = 'ST-100';
        $input->nationality = 'Moroccan';
        $input->summary = 'Backend-focused student';
        $input->address = [
            'city' => 'Rabat',
            'state' => 'Rabat-Sale-Kenitra',
            'country' => 'Morocco',
        ];

        $service->update($profile, $input);

        $this->assertSame('new@example.test', $profile->getUserId()?->getEmail());
        $this->assertSame('+212600000099', $profile->getUserId()?->getPhone());
        $this->assertSame('Computer Science', $profile->getProgram());
        $this->assertSame('M2', $profile->getInternshipCycle());
        $this->assertSame('2026/2027', $profile->getAcademicYear());
        $this->assertSame('Engineering', $profile->getFaculty());
        $this->assertSame('Rabat', $profile->getUserId()?->getAddressId()?->getCity());
        $this->assertSame('Morocco', $profile->getUserId()?->getAddressId()?->getCountry());

        $storedBio = json_decode((string) $profile->getBio(), true);
        $this->assertSame('Software', $storedBio['academic']['department']);
        $this->assertSame('ST-100', $storedBio['academic']['studentId']);
        $this->assertSame('Moroccan', $storedBio['personal']['nationality']);
        $this->assertSame('Backend-focused student', $storedBio['personal']['summary']);
    }

    private function createEntityManagerMock(bool $transactionActive = false): EntityManagerInterface
    {
        $entityManager = $this->createMock(EntityManagerInterface::class);
        $connection = $this->createMock(Connection::class);
        $connection->method('isTransactionActive')->willReturn($transactionActive);
        $entityManager->method('getConnection')->willReturn($connection);

        return $entityManager;
    }

    private function setEntityId(object $entity, int $id): void
    {
        $reflection = new \ReflectionClass($entity);
        $property = $reflection->getProperty('id');
        $property->setAccessible(true);
        $property->setValue($entity, $id);
    }
}
