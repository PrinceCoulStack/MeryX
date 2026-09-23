<?php

namespace App\Tests;

use App\Entity\Candidature;
use App\Entity\Company;
use App\Entity\Conversation;
use App\Entity\ConversationParticipant;
use App\Entity\Message;
use App\Entity\StudentProfile;
use App\Entity\University;
use App\Entity\User;
use App\Repository\CandidatureRepository;
use App\Repository\CompanyRepository;
use App\Repository\StudentProfileRepository;
use App\Repository\UniversityRepository;
use App\Service\Messaging\InboxThreadResponseBuilder;
use PHPUnit\Framework\TestCase;

final class InboxThreadResponseBuilderTest extends TestCase
{
    public function testBuildConversationPayloadContainsNormalizedThreadAndCandidatureContext(): void
    {
        $studentUser = (new User())->setEmail('student@example.test');
        $companyUser = (new User())->setEmail('company@example.test');
        $viewer = (new User())->setEmail('viewer@example.test');

        $this->setEntityId($studentUser, 10);
        $this->setEntityId($companyUser, 20);
        $this->setEntityId($viewer, 30);

        $conversation = new Conversation();
        $conversation->setId(44);
        $conversation->setSubject('Application thread');
        $conversation->setType('university_company');
        $conversation->setCreatedAt(new \DateTimeImmutable('2026-09-01T08:00:00+00:00'));
        $conversation->setUpdatedAt(new \DateTimeImmutable('2026-09-01T10:00:00+00:00'));

        $studentParticipant = (new ConversationParticipant())->setUser($studentUser)
            ->setJoinedAt(new \DateTimeImmutable('2026-09-01T08:00:00+00:00'))
            ->setLastReadAt(new \DateTimeImmutable('2026-09-01T09:00:00+00:00'));
        $companyParticipant = (new ConversationParticipant())->setUser($companyUser)
            ->setJoinedAt(new \DateTimeImmutable('2026-09-01T08:05:00+00:00'))
            ->setLastReadAt(new \DateTimeImmutable('2026-09-01T09:05:00+00:00'));

        $conversation->addParticipant($studentParticipant);
        $conversation->addParticipant($companyParticipant);

        $message = new Message();
        $this->setEntityId($message, 101);
        $message->setConversationId($conversation);
        $message->setSenderId($studentUser);
        $message->setBody('hello from student');
        $message->setCreatedAt(new \DateTimeImmutable('2026-09-01T09:30:00+00:00'));

        $studentProfile = (new StudentProfile())
            ->setUserId($studentUser)
            ->setFullName('Student One');
        $company = (new Company())
            ->setName('ACME Corp')
            ->setEmail('hr@acme.test')
            ->setUserId($companyUser);
        $university = (new University())
            ->setName('University X')
            ->setEmail('advisor@uni.test');
        $candidature = (new Candidature())
            ->setStatus('interview_scheduled')
            ->setAppliedDate(new \DateTimeImmutable('2026-08-28T12:00:00+00:00'))
            ->setInterviewDate(new \DateTimeImmutable('2026-09-05T13:00:00+00:00'))
            ->setLastUpdated(new \DateTimeImmutable('2026-09-01T11:00:00+00:00'));

        $this->setEntityId($studentProfile, 501);
        $this->setEntityId($company, 601);
        $this->setEntityId($university, 701);

        $studentRepo = $this->createMock(StudentProfileRepository::class);
        $studentRepo->method('findOneBy')->willReturnMap([
            [['userId' => $studentUser], $studentProfile],
            [['userId' => $companyUser], null],
        ]);

        $companyRepo = $this->createMock(CompanyRepository::class);
        $companyRepo->method('findOneBy')->willReturnMap([
            [['userId' => $studentUser], null],
            [['userId' => $companyUser], $company],
        ]);

        $universityRepo = $this->createMock(UniversityRepository::class);
        $universityRepo->method('findOneBy')->willReturn($university);

        $candidatureRepo = $this->createMock(CandidatureRepository::class);
        $candidatureRepo->expects($this->once())
            ->method('findLatestForStudentAndCompany')
            ->with(501, 601)
            ->willReturn($candidature);

        $builder = new InboxThreadResponseBuilder(
            $studentRepo,
            $companyRepo,
            $universityRepo,
            $candidatureRepo,
        );

        $payload = $builder->buildConversationPayload($conversation, $viewer, [$message]);

        $this->assertSame(44, $payload['id']);
        $this->assertSame('/api/conversations/44', $payload['@id']);
        $this->assertSame('interview_scheduled', $payload['candidatureStatus']);
        $this->assertSame('2026-09-05T13:00:00+00:00', $payload['interviewDate']);
        $this->assertSame('2026-08-28T12:00:00+00:00', $payload['appliedDate']);
        $this->assertSame('2026-09-01T11:00:00+00:00', $payload['lastUpdated']);
        $this->assertSame(2, count($payload['thread']['participants']));
        $this->assertSame(101, $payload['thread']['lastMessage']['id']);
        $this->assertSame(501, $payload['student']['id']);
        $this->assertSame('Student One', $payload['student']['fullName']);
        $this->assertSame('ACME Corp', $payload['company']['name']);
        $this->assertSame('University X', $payload['university']['name']);
        $this->assertSame(30, $payload['viewer']['id']);
    }

    private function setEntityId(object $entity, int $id): void
    {
        $reflection = new \ReflectionClass($entity);
        $property = $reflection->getProperty('id');
        $property->setAccessible(true);
        $property->setValue($entity, $id);
    }
}
