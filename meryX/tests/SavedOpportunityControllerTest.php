<?php

namespace App\Tests;

use App\Controller\SavedOpportunity\CreateSavedOpportunity;
use App\Controller\SavedOpportunity\ListSavedOpportunity;
use App\Entity\Company;
use App\Entity\Opportunities;
use App\Entity\SavedOpportunity;
use App\Entity\StudentProfile;
use App\Entity\User;
use App\Entity\UserType;
use App\Repository\OpportunitiesRepository;
use App\Repository\SavedOpportunityRepository;
use App\Repository\StudentProfileRepository;
use App\Security\Authorization\ActorContextResolver;
use App\Service\Api\HydraErrorResponseFactory;
use App\Service\Candidature\SavedOpportunityResponseBuilder;
use App\Tests\Support\ControllerAuthTestHelper;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;

final class SavedOpportunityControllerTest extends TestCase
{
    use ControllerAuthTestHelper;

    public function testPostIsIdempotentAndReturnsExistingRecord(): void
    {
        $actor = $this->buildUserWithRole('STUDENT', 3);
        $student = new StudentProfile();
        $this->setEntityId($student, 10);

        $opportunity = new Opportunities();
        $opportunity->setTitle('QA Internship');
        $opportunity->setType('internship');
        $opportunity->setDepartment('qa');
        $opportunity->setLocation('Remote');
        $opportunity->setRemoteType('remote');
        $opportunity->setSalaryLabel('stipend');
        $opportunity->setDescription('desc');
        $opportunity->setStatus('open');
        $opportunity->setIsEnabled(true);
        $opportunity->setPublishedAt(new \DateTimeImmutable());
        $opportunity->setApplicationDeadLine(new \DateTimeImmutable('+2 weeks'));
        $opportunity->setCreatedAt(new \DateTimeImmutable());
        $opportunity->setUpdatedAt(new \DateTimeImmutable());
        $opportunity->setIsDeleted(false);
        $opportunity->setCategory('qa');
        $opportunity->setExperienceLevel('junior');
        $opportunity->setNumberOfPositions('1');
        $this->setEntityId($opportunity, 7);

        $existing = new SavedOpportunity();
        $existing->setOpportunity($opportunity);
        $existing->setStudent($student);
        $existing->setSavedDate(new \DateTimeImmutable('-1 day'));
        $existing->setCreatedAt(new \DateTimeImmutable('-1 day'));
        $existing->setUpdatedAt(new \DateTimeImmutable('-1 day'));
        $this->setEntityId($existing, 88);

        $repo = $this->createMock(SavedOpportunityRepository::class);
        $repo->method('findOneBy')->willReturn($existing);

        $opportunityRepo = $this->createMock(OpportunitiesRepository::class);
        $opportunityRepo->method('find')->with(7)->willReturn($opportunity);

        $studentRepo = $this->createMock(StudentProfileRepository::class);
        $studentRepo->method('find')->with(10)->willReturn($student);

        $resolver = $this->createMock(ActorContextResolver::class);
        $resolver->method('hasRole')->willReturnMap([
            [$actor, 'ROLE_ADMIN', false],
            [$actor, 'ROLE_STUDENT', true],
            [$actor, 'ROLE_COMPANY', false],
        ]);
        $resolver->method('resolveStudentProfile')->willReturn($student);

        $controller = new CreateSavedOpportunity();
        $controller->setContainer($this->createControllerContainer($actor));

        $response = $controller->__invoke(
            new Request(content: json_encode(['opportunityId' => 7, 'studentId' => 10], JSON_THROW_ON_ERROR)),
            $this->createMock(EntityManagerInterface::class),
            $repo,
            $opportunityRepo,
            $studentRepo,
            $resolver,
            new SavedOpportunityResponseBuilder(),
            new HydraErrorResponseFactory(),
        );

        $this->assertSame(200, $response->getStatusCode());
    }

    public function testStudentSeesOwnSavedOpportunitiesOnly(): void
    {
        $actor = $this->buildUserWithRole('STUDENT', 3);
        $profile = new StudentProfile();
        $this->setEntityId($profile, 10);

        $repo = $this->createMock(SavedOpportunityRepository::class);
        $repo->expects($this->once())->method('search')->with(
            $this->callback(function (array $filters): bool {
                return ($filters['visibleStudentId'] ?? null) === 10;
            }),
            1,
            20,
        )->willReturn([[], 0]);

        $resolver = $this->createMock(ActorContextResolver::class);
        $resolver->method('hasRole')->willReturnMap([
            [$actor, 'ROLE_ADMIN', false],
            [$actor, 'ROLE_STUDENT', true],
            [$actor, 'ROLE_COMPANY', false],
        ]);
        $resolver->method('resolveStudentProfile')->willReturn($profile);

        $controller = new ListSavedOpportunity();
        $controller->setContainer($this->createControllerContainer($actor));

        $response = $controller->__invoke(
            new Request(query: ['page' => 1, 'limit' => 20]),
            $repo,
            $resolver,
            new SavedOpportunityResponseBuilder(),
            new HydraErrorResponseFactory(),
        );

        $payload = json_decode((string) $response->getContent(), true);
        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame('hydra:Collection', $payload['@type']);
        $this->assertSame(0, $payload['hydra:totalItems']);
    }

    public function testListResponseIsNormalizedForFrontendStore(): void
    {
        $actor = $this->buildUserWithRole('STUDENT', 3);
        $profile = new StudentProfile();
        $profile->setFullName('Student Saved');
        $profile->setGpa('3.20');
        $profile->setGender('female');
        $profile->setProgram('Software Engineering');
        $profile->setSkills(['php', 'symfony']);
        $profile->setBio(json_encode([
            'personal' => ['summary' => 'Interested in backend platforms.'],
            'academic' => ['level' => 'M1'],
        ], JSON_THROW_ON_ERROR));
        $studentUser = new User();
        $type = new UserType();
        $type->setName('STUDENT');
        $type->setPermission([]);
        $type->setDescription('student');
        $type->setIsEnabled(true);
        $type->setIsDeleted(false);
        $studentUser->setUserTypeId($type);
        $studentUser->setEmail('student@example.test');
        $studentUser->setPhone('+212600000010');
        $profile->setUserId($studentUser);
        $this->setEntityId($profile, 10);

        $company = new Company();
        $company->setName('Tech Co');
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

        $opportunity = new Opportunities();
        $opportunity->setCompanyId($company);
        $opportunity->setTitle('Backend Internship');
        $opportunity->setType('internship');
        $opportunity->setDepartment('Engineering');
        $opportunity->setLocation('Remote');
        $opportunity->setRemoteType('remote');
        $opportunity->setSalaryLabel('stipend');
        $opportunity->setDescription('desc');
        $opportunity->setStatus('open');
        $opportunity->setIsEnabled(true);
        $opportunity->setPublishedAt(new \DateTimeImmutable());
        $opportunity->setApplicationDeadLine(new \DateTimeImmutable('+2 weeks'));
        $opportunity->setCreatedAt(new \DateTimeImmutable());
        $opportunity->setUpdatedAt(new \DateTimeImmutable());
        $opportunity->setIsDeleted(false);
        $opportunity->setCategory('tech');
        $opportunity->setExperienceLevel('junior');
        $opportunity->setNumberOfPositions('1');
        $this->setEntityId($opportunity, 7);

        $saved = new SavedOpportunity();
        $saved->setStudent($profile);
        $saved->setOpportunity($opportunity);
        $saved->setNotes(json_encode([
            'motivation' => 'Save for later application',
            'personal' => ['summary' => 'Prefer remote roles'],
        ], JSON_THROW_ON_ERROR));
        $saved->setSavedDate(new \DateTimeImmutable('-1 day'));
        $saved->setCreatedAt(new \DateTimeImmutable('-1 day'));
        $saved->setUpdatedAt(new \DateTimeImmutable('-1 day'));
        $this->setEntityId($saved, 88);

        $repo = $this->createMock(SavedOpportunityRepository::class);
        $repo->expects($this->once())->method('search')->with(
            $this->callback(function (array $filters): bool {
                return ($filters['studentId'] ?? null) === 10 && ($filters['visibleStudentId'] ?? null) === 10;
            }),
            1,
            20,
        )->willReturn([[$saved], 1]);

        $resolver = $this->createMock(ActorContextResolver::class);
        $resolver->method('hasRole')->willReturnMap([
            [$actor, 'ROLE_ADMIN', false],
            [$actor, 'ROLE_STUDENT', true],
            [$actor, 'ROLE_COMPANY', false],
        ]);
        $resolver->method('resolveStudentProfile')->willReturn($profile);

        $controller = new ListSavedOpportunity();
        $controller->setContainer($this->createControllerContainer($actor));

        $response = $controller->__invoke(
            new Request(query: ['studentId' => 10]),
            $repo,
            $resolver,
            new SavedOpportunityResponseBuilder(),
            new HydraErrorResponseFactory(),
        );

        $payload = json_decode((string) $response->getContent(), true);

        $this->assertSame(200, $response->getStatusCode());
        $this->assertCount(1, $payload['hydra:member']);
        $this->assertSame(10, $payload['hydra:member'][0]['studentId']);
        $this->assertSame(7, $payload['hydra:member'][0]['opportunityId']);
        $this->assertSame('Backend Internship', $payload['hydra:member'][0]['opportunity']['title']);
        $this->assertSame('Tech Co', $payload['hydra:member'][0]['opportunity']['company']);
        $this->assertSame('student@example.test', $payload['hydra:member'][0]['student']['email']);
        $this->assertSame('Interested in backend platforms.', $payload['hydra:member'][0]['student']['summary']);
        $this->assertSame('Save for later application', $payload['hydra:member'][0]['notes']['motivation']);
    }
}
