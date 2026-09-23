<?php

namespace App\Tests;

use App\Controller\Candidature\ListCandidature;
use App\Entity\Candidature;
use App\Entity\Company;
use App\Entity\Opportunities;
use App\Entity\StudentProfile;
use App\Entity\User;
use App\Entity\UserType;
use App\Repository\CandidatureRepository;
use App\Security\Authorization\ActorContextResolver;
use App\Service\Api\HydraErrorResponseFactory;
use App\Service\Candidature\CandidatureResponseBuilder;
use App\Tests\Support\ControllerAuthTestHelper;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;

final class ListCandidatureTest extends TestCase
{
    use ControllerAuthTestHelper;

    public function testCompanyCanListOwnOpportunityCandidaturesWithFilters(): void
    {
        $actor = $this->buildUserWithRole('COMPANY', 5);

        $actorCompany = new Company();
        $this->setEntityId($actorCompany, 22);

        $candidature = $this->buildCandidature(
            candidatureId: 100,
            opportunityId: 40,
            companyId: 22,
            studentId: 9,
            status: 'interview',
            notes: json_encode([
                'motivation' => 'Distributed systems and backend performance.',
                'personal' => ['summary' => 'Student interested in platform engineering.'],
            ], JSON_THROW_ON_ERROR),
        );

        $repository = $this->createMock(CandidatureRepository::class);
        $repository->expects($this->once())
            ->method('search')
            ->with(
                $this->callback(function (array $filters): bool {
                    return ($filters['opportunityId'] ?? null) === 40
                        && ($filters['studentId'] ?? null) === 9
                        && ($filters['status'] ?? null) === 'interview'
                        && ($filters['visibleCompanyId'] ?? null) === 22;
                }),
                2,
                5,
            )
            ->willReturn([[$candidature], 1]);

        $resolver = $this->createMock(ActorContextResolver::class);
        $resolver->method('hasRole')->willReturnMap([
            [$actor, 'ROLE_ADMIN', false],
            [$actor, 'ROLE_COMPANY', true],
            [$actor, 'ROLE_STUDENT', false],
        ]);
        $resolver->method('resolveCompany')->with($actor)->willReturn($actorCompany);

        $request = new Request([
            'opportunityId' => '/api/opportunities/40',
            'studentId' => '/api/studentProfiles/9',
            'status' => 'interview',
            'page' => '2',
            'limit' => '5',
        ]);

        $controller = new ListCandidature();
        $controller->setContainer($this->createControllerContainer($actor));

        $response = $controller->__invoke(
            $request,
            $repository,
            $resolver,
            new CandidatureResponseBuilder(),
            new HydraErrorResponseFactory(),
        );

        $payload = json_decode((string) $response->getContent(), true);

        $this->assertSame(200, $response->getStatusCode());
        $this->assertCount(1, $payload['hydra:member']);
        $this->assertSame(40, $payload['hydra:member'][0]['opportunityId']);
        $this->assertSame(9, $payload['hydra:member'][0]['studentId']);
        $this->assertSame('interview', $payload['hydra:member'][0]['status']);
        $this->assertArrayHasKey('createdAt', $payload['hydra:member'][0]);
        $this->assertArrayHasKey('updatedAt', $payload['hydra:member'][0]);
        $this->assertArrayHasKey('interviewDate', $payload['hydra:member'][0]);

        $student = $payload['hydra:member'][0]['student'];
        $this->assertSame(9, $student['id']);
        $this->assertSame('Jane Student', $student['fullName']);
        $this->assertSame('jane@student.test', $student['email']);
        $this->assertSame('+212600000009', $student['phone']);
        $this->assertSame('Computer Science', $student['program']);
        $this->assertSame('M2', $student['level']);
        $this->assertSame('3.90', $student['gpa']);
        $this->assertSame(['php', 'symfony'], $student['skills']);
        $this->assertSame('Aspiring backend engineer.', $student['summary']);

        $this->assertSame([
            'motivation' => 'Distributed systems and backend performance.',
            'personal' => ['summary' => 'Student interested in platform engineering.'],
        ], $payload['hydra:member'][0]['notes']);
    }

    public function testOpportunityFilterReturnsOnlyRepositoryResults(): void
    {
        $actor = $this->buildUserWithRole('ADMIN', 1);

        $candidatureOne = $this->buildCandidature(
            candidatureId: 1,
            opportunityId: 77,
            companyId: 10,
            studentId: 4,
            status: 'applied',
            notes: 'note-1',
        );

        $candidatureTwo = $this->buildCandidature(
            candidatureId: 2,
            opportunityId: 77,
            companyId: 10,
            studentId: 5,
            status: 'interview',
            notes: 'note-2',
        );

        $repository = $this->createMock(CandidatureRepository::class);
        $repository->expects($this->once())
            ->method('search')
            ->with(
                $this->callback(static fn (array $filters): bool => ($filters['opportunityId'] ?? null) === 77),
                1,
                20,
            )
            ->willReturn([[$candidatureOne, $candidatureTwo], 2]);

        $resolver = $this->createMock(ActorContextResolver::class);
        $resolver->method('hasRole')->willReturnMap([
            [$actor, 'ROLE_ADMIN', true],
            [$actor, 'ROLE_COMPANY', false],
            [$actor, 'ROLE_STUDENT', false],
        ]);

        $request = new Request([
            'opportunityId' => '77',
        ]);

        $controller = new ListCandidature();
        $controller->setContainer($this->createControllerContainer($actor));

        $response = $controller->__invoke(
            $request,
            $repository,
            $resolver,
            new CandidatureResponseBuilder(),
            new HydraErrorResponseFactory(),
        );

        $payload = json_decode((string) $response->getContent(), true);

        $this->assertSame(200, $response->getStatusCode());
        $this->assertCount(2, $payload['hydra:member']);
        foreach ($payload['hydra:member'] as $item) {
            $this->assertSame(77, $item['opportunityId']);
        }
    }

    private function buildCandidature(
        int $candidatureId,
        int $opportunityId,
        int $companyId,
        int $studentId,
        string $status,
        ?string $notes,
    ): Candidature {
        $company = new Company();
        $company->setName('Test Company');
        $company->setLogo('logo');
        $company->setSector('tech');
        $company->setRankingScore('10');
        $company->setIsApproved(true);
        $company->setDescription('desc');
        $company->setStatus('approved');
        $company->setRegistrationNumber('reg');
        $company->setTaxId('tax');
        $company->setVerifiedAt(new \DateTimeImmutable());
        $company->setCreatedAt(new \DateTimeImmutable('-10 days'));
        $company->setUpdatedAt(new \DateTimeImmutable('-1 day'));
        $this->setEntityId($company, $companyId);

        $opportunity = new Opportunities();
        $opportunity->setCompanyId($company);
        $opportunity->setTitle('Backend Engineer Intern');
        $opportunity->setType('internship');
        $opportunity->setDepartment('Engineering');
        $opportunity->setLocation('Remote');
        $opportunity->setRemoteType('remote');
        $opportunity->setSalaryLabel('market');
        $opportunity->setDescription('desc');
        $opportunity->setStatus('open');
        $opportunity->setIsEnabled(true);
        $opportunity->setPublishedAt(new \DateTimeImmutable('-5 days'));
        $opportunity->setApplicationDeadLine(new \DateTimeImmutable('+15 days'));
        $opportunity->setCreatedAt(new \DateTimeImmutable('-10 days'));
        $opportunity->setUpdatedAt(new \DateTimeImmutable('-1 day'));
        $opportunity->setIsDeleted(false);
        $opportunity->setCategory('tech');
        $opportunity->setExperienceLevel('junior');
        $opportunity->setNumberOfPositions('2');
        $this->setEntityId($opportunity, $opportunityId);

        $user = new User();
        $type = new UserType();
        $type->setName('STUDENT');
        $type->setPermission([]);
        $type->setDescription('student');
        $type->setIsEnabled(true);
        $type->setIsDeleted(false);
        $user->setUserTypeId($type);
        $user->setEmail('jane@student.test');
        $user->setPhone('+212600000009');

        $student = new StudentProfile();
        $student->setUserId($user);
        $student->setFullName('Jane Student');
        $student->setProgram('Computer Science');
        $student->setGpa('3.90');
        $student->setSkills(['php', 'symfony']);
        $student->setBio(json_encode([
            'personal' => ['summary' => 'Aspiring backend engineer.'],
            'academic' => ['level' => 'M2'],
        ], JSON_THROW_ON_ERROR));
        $this->setEntityId($student, $studentId);

        $candidature = new Candidature();
        $candidature->setOpportunity($opportunity);
        $candidature->setStudent($student);
        $candidature->setStatus($status);
        $candidature->setAppliedDate(new \DateTimeImmutable('-2 days'));
        $candidature->setLastUpdated(new \DateTimeImmutable('-1 day'));
        $candidature->setNotes($notes);
        $candidature->setInterviewDate(new \DateTimeImmutable('+3 days'));
        $candidature->setCreatedAt(new \DateTimeImmutable('-2 days'));
        $candidature->setUpdatedAt(new \DateTimeImmutable('-1 day'));
        $this->setEntityId($candidature, $candidatureId);

        return $candidature;
    }
}
