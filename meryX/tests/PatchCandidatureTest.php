<?php

namespace App\Tests;

use App\Controller\Candidature\PatchCandidature;
use App\Entity\Candidature;
use App\Entity\Company;
use App\Entity\Opportunities;
use App\Entity\StudentProfile;
use App\Repository\CandidatureRepository;
use App\Security\Authorization\ActorContextResolver;
use App\Service\Api\HydraErrorResponseFactory;
use App\Service\Candidature\CandidatureAuditLogger;
use App\Service\Candidature\CandidatureResponseBuilder;
use App\Service\Candidature\CandidatureTransitionValidator;
use App\Tests\Support\ControllerAuthTestHelper;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;

final class PatchCandidatureTest extends TestCase
{
    use ControllerAuthTestHelper;

    public function testValidTransitionUpdatesStatus(): void
    {
        $actor = $this->buildUserWithRole('ADMIN', 1);
        $candidature = $this->buildCandidature('applied');

        $repo = $this->createMock(CandidatureRepository::class);
        $repo->method('find')->with(7)->willReturn($candidature);

        $em = $this->createMock(EntityManagerInterface::class);
        $em->expects($this->once())->method('flush');

        $audit = $this->createMock(CandidatureAuditLogger::class);
        $audit->expects($this->once())->method('logStatusChange');

        $resolver = $this->createMock(ActorContextResolver::class);
        $resolver->method('hasRole')->willReturnMap([
            [$actor, 'ROLE_ADMIN', true],
            [$actor, 'ROLE_COMPANY', false],
        ]);

        $request = new Request(attributes: ['id' => 7], content: json_encode([
            'status' => 'interview',
            'notes' => 'Proceed to technical interview',
        ], JSON_THROW_ON_ERROR));

        $controller = new PatchCandidature();
        $controller->setContainer($this->createControllerContainer($actor));

        $response = $controller->__invoke(
            $request,
            $repo,
            $em,
            $resolver,
            new CandidatureTransitionValidator(),
            $audit,
            new CandidatureResponseBuilder(),
            new HydraErrorResponseFactory(),
        );

        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame('interview', $candidature->getStatus());
        $this->assertNotNull($candidature->getLastUpdated());
    }

    public function testInvalidTransitionReturnsBadRequest(): void
    {
        $actor = $this->buildUserWithRole('ADMIN', 1);
        $candidature = $this->buildCandidature('applied');

        $repo = $this->createMock(CandidatureRepository::class);
        $repo->method('find')->willReturn($candidature);

        $resolver = $this->createMock(ActorContextResolver::class);
        $resolver->method('hasRole')->willReturnMap([
            [$actor, 'ROLE_ADMIN', true],
            [$actor, 'ROLE_COMPANY', false],
        ]);

        $request = new Request(attributes: ['id' => 7], content: json_encode([
            'status' => 'accepted',
        ], JSON_THROW_ON_ERROR));

        $controller = new PatchCandidature();
        $controller->setContainer($this->createControllerContainer($actor));

        $response = $controller->__invoke(
            $request,
            $repo,
            $this->createMock(EntityManagerInterface::class),
            $resolver,
            new CandidatureTransitionValidator(),
            $this->createMock(CandidatureAuditLogger::class),
            new CandidatureResponseBuilder(),
            new HydraErrorResponseFactory(),
        );

        $this->assertSame(400, $response->getStatusCode());
    }

    public function testStudentCannotUpdateCandidature(): void
    {
        $actor = $this->buildUserWithRole('STUDENT', 1);

        $resolver = $this->createMock(ActorContextResolver::class);
        $resolver->method('hasRole')->willReturnMap([
            [$actor, 'ROLE_ADMIN', false],
            [$actor, 'ROLE_COMPANY', false],
        ]);

        $controller = new PatchCandidature();
        $controller->setContainer($this->createControllerContainer($actor));

        $response = $controller->__invoke(
            new Request(attributes: ['id' => 1], content: json_encode(['status' => 'interview'], JSON_THROW_ON_ERROR)),
            $this->createMock(CandidatureRepository::class),
            $this->createMock(EntityManagerInterface::class),
            $resolver,
            new CandidatureTransitionValidator(),
            $this->createMock(CandidatureAuditLogger::class),
            new CandidatureResponseBuilder(),
            new HydraErrorResponseFactory(),
        );

        $this->assertSame(403, $response->getStatusCode());
    }

    public function testCompanyCannotUpdateOtherCompanyOpportunity(): void
    {
        $actor = $this->buildUserWithRole('COMPANY', 5);
        $candidature = $this->buildCandidature('applied', companyId: 11);

        $repo = $this->createMock(CandidatureRepository::class);
        $repo->method('find')->willReturn($candidature);

        $actorCompany = new Company();
        $this->setEntityId($actorCompany, 22);

        $resolver = $this->createMock(ActorContextResolver::class);
        $resolver->method('hasRole')->willReturnMap([
            [$actor, 'ROLE_ADMIN', false],
            [$actor, 'ROLE_COMPANY', true],
        ]);
        $resolver->method('resolveCompany')->willReturn($actorCompany);

        $controller = new PatchCandidature();
        $controller->setContainer($this->createControllerContainer($actor));

        $response = $controller->__invoke(
            new Request(attributes: ['id' => 7], content: json_encode(['status' => 'interview'], JSON_THROW_ON_ERROR)),
            $repo,
            $this->createMock(EntityManagerInterface::class),
            $resolver,
            new CandidatureTransitionValidator(),
            $this->createMock(CandidatureAuditLogger::class),
            new CandidatureResponseBuilder(),
            new HydraErrorResponseFactory(),
        );

        $this->assertSame(403, $response->getStatusCode());
    }

    public function testAdminCanPersistInterviewDateAndStructuredNotes(): void
    {
        $actor = $this->buildUserWithRole('ADMIN', 1);
        $candidature = $this->buildCandidature('applied');

        $repo = $this->createMock(CandidatureRepository::class);
        $repo->method('find')->with(7)->willReturn($candidature);

        $em = $this->createMock(EntityManagerInterface::class);
        $em->expects($this->once())->method('flush');

        $resolver = $this->createMock(ActorContextResolver::class);
        $resolver->method('hasRole')->willReturnMap([
            [$actor, 'ROLE_ADMIN', true],
            [$actor, 'ROLE_COMPANY', false],
        ]);

        $interviewDate = '2026-09-20T10:30:00+00:00';
        $notes = [
            'motivation' => 'Strong backend and API collaboration fit.',
            'personal' => [
                'summary' => 'Candidate fits interview shortlist criteria.',
            ],
        ];

        $request = new Request(attributes: ['id' => 7], content: json_encode([
            'status' => 'interview',
            'interviewDate' => $interviewDate,
            'feedback' => 'Strong technical depth and communication.',
            'notes' => $notes,
        ], JSON_THROW_ON_ERROR));

        $controller = new PatchCandidature();
        $controller->setContainer($this->createControllerContainer($actor));

        $response = $controller->__invoke(
            $request,
            $repo,
            $em,
            $resolver,
            new CandidatureTransitionValidator(),
            $this->createMock(CandidatureAuditLogger::class),
            new CandidatureResponseBuilder(),
            new HydraErrorResponseFactory(),
        );

        $payload = json_decode((string) $response->getContent(), true);

        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame('interview', $candidature->getStatus());
        $this->assertSame($interviewDate, $candidature->getInterviewDate()?->format(\DateTimeInterface::ATOM));
        $this->assertSame('Strong technical depth and communication.', $candidature->getFeedback());
        $this->assertSame($notes, json_decode((string) $candidature->getNotes(), true));
        $this->assertSame($interviewDate, $payload['interviewDate']);
        $this->assertSame('Strong technical depth and communication.', $payload['feedback']);
        $this->assertSame($notes, $payload['notes']);
    }

    public function testCompanyCanUpdateOwnOpportunityInterviewSchedule(): void
    {
        $actor = $this->buildUserWithRole('COMPANY', 5);
        $candidature = $this->buildCandidature('applied', companyId: 22);

        $repo = $this->createMock(CandidatureRepository::class);
        $repo->method('find')->with(7)->willReturn($candidature);

        $em = $this->createMock(EntityManagerInterface::class);
        $em->expects($this->once())->method('flush');

        $actorCompany = new Company();
        $this->setEntityId($actorCompany, 22);

        $resolver = $this->createMock(ActorContextResolver::class);
        $resolver->method('hasRole')->willReturnMap([
            [$actor, 'ROLE_ADMIN', false],
            [$actor, 'ROLE_COMPANY', true],
        ]);
        $resolver->method('resolveCompany')->with($actor)->willReturn($actorCompany);

        $request = new Request(attributes: ['id' => 7], content: json_encode([
            'status' => 'interview',
            'interviewDate' => '2026-09-21T15:00:00+00:00',
            'notes' => [
                'personal' => ['summary' => 'Confirmed by recruiter'],
            ],
        ], JSON_THROW_ON_ERROR));

        $controller = new PatchCandidature();
        $controller->setContainer($this->createControllerContainer($actor));

        $response = $controller->__invoke(
            $request,
            $repo,
            $em,
            $resolver,
            new CandidatureTransitionValidator(),
            $this->createMock(CandidatureAuditLogger::class),
            new CandidatureResponseBuilder(),
            new HydraErrorResponseFactory(),
        );

        $payload = json_decode((string) $response->getContent(), true);

        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame('interview', $payload['status']);
        $this->assertSame('2026-09-21T15:00:00+00:00', $payload['interviewDate']);
    }

    private function buildCandidature(string $status, int $companyId = 5): Candidature
    {
        $company = new Company();
        $company->setName('Test Company');
        $company->setLogo('logo');
        $company->setSector('tech');
        $company->setRankingScore('1');
        $company->setIsApproved(true);
        $company->setDescription('desc');
        $company->setStatus('approved');
        $company->setRegistrationNumber('reg');
        $company->setTaxId('tax');
        $company->setVerifiedAt(new \DateTimeImmutable());
        $company->setCreatedAt(new \DateTimeImmutable());
        $company->setUpdatedAt(new \DateTimeImmutable());
        $this->setEntityId($company, $companyId);

        $opportunity = new Opportunities();
        $opportunity->setCompanyId($company);
        $opportunity->setTitle('PHP Developer');
        $opportunity->setType('job');
        $opportunity->setDepartment('engineering');
        $opportunity->setLocation('Rabat');
        $opportunity->setRemoteType('hybrid');
        $opportunity->setSalaryLabel('market');
        $opportunity->setDescription('desc');
        $opportunity->setStatus('open');
        $opportunity->setIsEnabled(true);
        $opportunity->setPublishedAt(new \DateTimeImmutable());
        $opportunity->setApplicationDeadLine(new \DateTimeImmutable('+10 days'));
        $opportunity->setCreatedAt(new \DateTimeImmutable());
        $opportunity->setUpdatedAt(new \DateTimeImmutable());
        $opportunity->setIsDeleted(false);
        $opportunity->setCategory('tech');
        $opportunity->setExperienceLevel('junior');
        $opportunity->setNumberOfPositions('1');
        $this->setEntityId($opportunity, 40);

        $student = new StudentProfile();
        $student->setFullName('Test Student');
        $this->setEntityId($student, 12);

        $candidature = new Candidature();
        $candidature->setOpportunity($opportunity);
        $candidature->setStudent($student);
        $candidature->setStatus($status);
        $candidature->setAppliedDate(new \DateTimeImmutable('-3 days'));
        $candidature->setCreatedAt(new \DateTimeImmutable('-3 days'));
        $candidature->setUpdatedAt(new \DateTimeImmutable('-3 days'));
        $candidature->setLastUpdated(new \DateTimeImmutable('-3 days'));
        $this->setEntityId($candidature, 7);

        return $candidature;
    }
}
