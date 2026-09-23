<?php

namespace App\Tests;

use App\Entity\Conversation;
use App\Entity\ConversationParticipant;
use App\Entity\StudentProfile;
use App\Entity\University;
use App\Entity\User;
use App\Repository\StudentProfileRepository;
use App\Repository\UniversityRepository;
use App\Security\StudentProfileAccessService;
use App\Service\Messaging\UniversityStudentConversationPolicy;
use PHPUnit\Framework\TestCase;

final class UniversityStudentConversationPolicyTest extends TestCase
{
    public function testUniversityStaffCanCreateConversationOnlyWithLinkedStudent(): void
    {
        $university = new University();
        $this->setEntityId($university, 7);

        $actor = (new User())->setEmail('staff@example.test');
        $actor->setUserTypeId($this->buildUserType('UNIVERSITY'));
        $actor->setStatus('active');

        $studentUser = (new User())->setEmail('student@example.test');
        $studentUser->setStatus('active');

        $studentProfile = new StudentProfile();
        $studentProfile->setUserId($studentUser);
        $studentProfile->setUniversityId($university);
        $studentProfile->setStatus('approved');
        $studentProfile->setProgram('Computer Science');
        $studentProfile->setFaculty('Engineering');
        $studentProfile->setInternshipCycle('Summer 2026');
        $studentProfile->setAcademicYear('2026');

        $resolvedStudentProfile = $studentProfile;
        $profileRepo = $this->createMock(StudentProfileRepository::class);
        $profileRepo->method('findOneBy')->willReturnCallback(static function (array $criteria) use ($studentUser, &$resolvedStudentProfile) {
            return $criteria['userId'] === $studentUser ? $resolvedStudentProfile : null;
        });

        $universityRepo = $this->createMock(UniversityRepository::class);
        $universityRepo->method('findOneBy')->willReturnCallback(static fn (array $criteria) => $criteria['userId'] === $actor ? $university : null);

        $service = new UniversityStudentConversationPolicy(new StudentProfileAccessService($universityRepo), $profileRepo);

        $this->assertSame(['allowed' => true, 'reason' => 'allowed', 'readOnly' => false], $service->canCreateConversation($actor, $studentUser));

        $otherUniversity = new University();
        $this->setEntityId($otherUniversity, 99);
        $wrongStudentProfile = new StudentProfile();
        $wrongStudentProfile->setUserId($studentUser);
        $wrongStudentProfile->setUniversityId($otherUniversity);
        $wrongStudentProfile->setStatus('approved');

        $resolvedStudentProfile = $wrongStudentProfile;
        $this->assertSame(['allowed' => false, 'reason' => 'university_mismatch'], $service->canCreateConversation($actor, $studentUser));
    }

    public function testPendingOrRejectedStudentsAreReadOnlyAndEscalate(): void
    {
        $university = new University();
        $this->setEntityId($university, 12);

        $actor = (new User())->setEmail('staff@example.test');
        $actor->setUserTypeId($this->buildUserType('UNIVERSITY'));

        $studentUser = (new User())->setEmail('pending-student@example.test');
        $studentProfile = new StudentProfile();
        $studentProfile->setUserId($studentUser);
        $studentProfile->setUniversityId($university);
        $studentProfile->setStatus('pending');
        $studentProfile->setProgram('Business');

        $profileRepo = $this->createMock(StudentProfileRepository::class);
        $profileRepo->method('findOneBy')->willReturn($studentProfile);

        $universityRepo = $this->createMock(UniversityRepository::class);
        $universityRepo->method('findOneBy')->willReturnCallback(static fn (array $criteria) => $criteria['userId'] === $actor ? $university : null);

        $policy = new UniversityStudentConversationPolicy(new StudentProfileAccessService($universityRepo), $profileRepo);

        $this->assertSame(['allowed' => true, 'reason' => 'restricted_by_status', 'readOnly' => true], $policy->canCreateConversation($actor, $studentUser));
        $this->assertSame(['allowed' => false, 'reason' => 'student_status_restricted', 'readOnly' => true], $policy->canSendMessage($actor, $studentUser, 'Hello'));

        $conversation = new Conversation();
        $conversation->setType('university_student');
        $conversation->setSubject('Admissions follow-up');

        $participant = new ConversationParticipant();
        $participant->setUser($studentUser);
        $conversation->addParticipant($participant);

        $this->assertTrue($policy->requiresIntervention($actor, $conversation, $studentProfile));
        $this->assertSame('ROLE_UNIVERSITY_ADMIN', $policy->notificationTargetRole());

        $this->assertSame('read_only', $policy->buildRestrictedTemplate($studentProfile)['mode']);
        $this->assertSame('pending', strtolower((string) $studentProfile->getStatus()));
    }

    public function testAdminCanAuditWithoutImpersonatingParticipant(): void
    {
        $admin = (new User())->setEmail('admin@example.test');
        $admin->setUserTypeId($this->buildUserType('ADMIN'));

        $student = (new User())->setEmail('student@example.test');
        $student->setUserTypeId($this->buildUserType('STUDENT'));

        $conversation = new Conversation();
        $conversation->setType('university_student');

        $participant = new ConversationParticipant();
        $participant->setUser($student);
        $conversation->addParticipant($participant);

        $universityRepo = $this->createMock(UniversityRepository::class);
        $universityRepo->method('findOneBy')->willReturn(null);

        $policy = new UniversityStudentConversationPolicy(new StudentProfileAccessService($universityRepo), $this->createMock(StudentProfileRepository::class));

        $this->assertTrue($policy->canAuditConversation($admin, $conversation));
        $this->assertFalse($policy->canAuditConversation($student, $conversation));
    }

    private function buildUserType(string $roleName): \App\Entity\UserType
    {
        $userType = new \App\Entity\UserType();
        $userType->setName($roleName);
        $userType->setPermission([]);
        $userType->setDescription($roleName);
        $userType->setIsEnabled(true);

        return $userType;
    }

    private function setEntityId(object $entity, int $id): void
    {
        $property = new \ReflectionProperty($entity, 'id');
        $property->setAccessible(true);
        $property->setValue($entity, $id);
    }
}
