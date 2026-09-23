<?php

namespace App\Tests;

use App\Entity\StudentDocuments;
use App\Entity\StudentProfile;
use App\Entity\University;
use App\Entity\User;
use App\Entity\UserType;
use App\Repository\UniversityRepository;
use App\Security\StudentProfileAccessService;
use PHPUnit\Framework\TestCase;

final class StudentProfileAccessServiceTest extends TestCase
{
    public function testStudentCanOnlyViewOwnProfile(): void
    {
        $studentUser = $this->buildUserWithRole('STUDENT', 100);

        $profile = new StudentProfile();
        $owner = $this->buildUserWithRole('STUDENT', 100);
        $profile->setUserId($owner);
        $profile->setStatus('approved');
        $profile->setIsApproved(true);

        $universityRepository = $this->createMock(UniversityRepository::class);
        $service = new StudentProfileAccessService($universityRepository);

        $this->assertTrue($service->canViewProfile($studentUser, $profile));

        $profile->setUserId($this->buildUserWithRole('STUDENT', 101));
        $this->assertFalse($service->canViewProfile($studentUser, $profile));
    }

    public function testPendingStudentCanViewOwnProfile(): void
    {
        $studentUser = $this->buildUserWithRole('STUDENT', 100);

        $profile = new StudentProfile();
        $owner = $this->buildUserWithRole('STUDENT', 100);
        $profile->setUserId($owner);
        $profile->setStatus('pending');
        $profile->setIsApproved(false);

        $universityRepository = $this->createMock(UniversityRepository::class);
        $service = new StudentProfileAccessService($universityRepository);

        $this->assertTrue($service->canViewProfile($studentUser, $profile));
    }

    public function testUniversityScopeIsEnforced(): void
    {
        $universityActor = $this->buildUserWithRole('UNIVERSITY', 20);

        $actorUniversity = new University();
        $this->setEntityId($actorUniversity, 300);

        $profile = new StudentProfile();
        $profileUniversity = new University();
        $this->setEntityId($profileUniversity, 300);
        $profile->setUniversityId($profileUniversity);

        $universityRepository = $this->createMock(UniversityRepository::class);
        $universityRepository->method('findOneBy')->willReturn($actorUniversity);

        $service = new StudentProfileAccessService($universityRepository);
        $this->assertTrue($service->canEditProfile($universityActor, $profile));

        $otherUniversity = new University();
        $this->setEntityId($otherUniversity, 301);
        $profile->setUniversityId($otherUniversity);

        $this->assertFalse($service->canEditProfile($universityActor, $profile));
    }

    public function testExplainEditDenialForUniversityMismatch(): void
    {
        $universityActor = $this->buildUserWithRole('UNIVERSITY', 20);

        $actorUniversity = new University();
        $this->setEntityId($actorUniversity, 300);

        $profile = new StudentProfile();
        $profileUniversity = new University();
        $this->setEntityId($profileUniversity, 301);
        $profile->setUniversityId($profileUniversity);

        $universityRepository = $this->createMock(UniversityRepository::class);
        $universityRepository->method('findOneBy')->willReturn($actorUniversity);

        $service = new StudentProfileAccessService($universityRepository);

        $this->assertSame('This student belongs to a different university.', $service->explainEditDenial($universityActor, $profile));
    }

    public function testExplainEditDenialWhenActorHasNoLinkedUniversity(): void
    {
        $universityActor = $this->buildUserWithRole('UNIVERSITY', 20);

        $profile = new StudentProfile();
        $profileUniversity = new University();
        $this->setEntityId($profileUniversity, 301);
        $profile->setUniversityId($profileUniversity);

        $universityRepository = $this->createMock(UniversityRepository::class);
        $universityRepository->method('findOneBy')->willReturn(null);

        $service = new StudentProfileAccessService($universityRepository);

        $this->assertSame('Your account is not linked to a university.', $service->explainEditDenial($universityActor, $profile));
    }

    public function testExplainEditDenialReturnsNullWhenAllowed(): void
    {
        $universityActor = $this->buildUserWithRole('UNIVERSITY', 20);

        $actorUniversity = new University();
        $this->setEntityId($actorUniversity, 300);

        $profile = new StudentProfile();
        $profileUniversity = new University();
        $this->setEntityId($profileUniversity, 300);
        $profile->setUniversityId($profileUniversity);

        $universityRepository = $this->createMock(UniversityRepository::class);
        $universityRepository->method('findOneBy')->willReturn($actorUniversity);

        $service = new StudentProfileAccessService($universityRepository);

        $this->assertNull($service->explainEditDenial($universityActor, $profile));
    }

    public function testPublicDocumentCanBeViewed(): void
    {
        $student = $this->buildUserWithRole('STUDENT', 10);

        $document = new StudentDocuments();
        $document->setIsPublic(true);

        $universityRepository = $this->createMock(UniversityRepository::class);
        $service = new StudentProfileAccessService($universityRepository);

        $this->assertTrue($service->canViewDocument($student, $document));
    }

    private function buildUserWithRole(string $role, int $id): User
    {
        $user = new User();
        $type = new UserType();
        $type->setName($role);
        $type->setPermission([]);
        $type->setDescription('test');
        $type->setIsEnabled(true);
        $type->setIsDeleted(false);
        $user->setUserTypeId($type);

        $this->setEntityId($user, $id);

        return $user;
    }

    private function setEntityId(object $entity, int $id): void
    {
        $reflection = new \ReflectionClass($entity);
        $property = $reflection->getProperty('id');
        $property->setAccessible(true);
        $property->setValue($entity, $id);
    }
}
