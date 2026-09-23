<?php

namespace App\Tests;

use App\Controller\Candidature\CreateCandidature;
use App\Entity\Candidature;
use App\Entity\Opportunities;
use App\Entity\StudentProfile;
use App\Repository\CandidatureRepository;
use App\Repository\OpportunitiesRepository;
use App\Repository\StudentProfileRepository;
use App\Security\Authorization\ActorContextResolver;
use App\Service\Api\HydraErrorResponseFactory;
use App\Service\Candidature\CandidatureResponseBuilder;
use App\Tests\Support\ControllerAuthTestHelper;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;

final class CreateCandidatureTest extends TestCase
{
    use ControllerAuthTestHelper;

    public function testCreateCandidatureSuccessfully(): void
    {
        $actor = $this->buildUserWithRole('STUDENT', 50);
        $student = new StudentProfile();
        $student->setStatus('approved');
        $student->setIsApproved(true);
        $this->setEntityId($student, 10);

        $opportunity = new Opportunities();
        $opportunity->setTitle('Backend Intern');
        $opportunity->setType('internship');
        $opportunity->setDepartment('Engineering');
        $opportunity->setLocation('Remote');
        $opportunity->setRemoteType('remote');
        $opportunity->setSalaryLabel('n/a');
        $opportunity->setDescription('desc');
        $opportunity->setStatus('open');
        $opportunity->setIsEnabled(true);
        $opportunity->setPublishedAt(new \DateTimeImmutable());
        $opportunity->setApplicationDeadLine(new \DateTimeImmutable('+15 days'));
        $opportunity->setCreatedAt(new \DateTimeImmutable());
        $opportunity->setUpdatedAt(new \DateTimeImmutable());
        $opportunity->setIsDeleted(false);
        $opportunity->setCategory('tech');
        $opportunity->setExperienceLevel('junior');
        $opportunity->setNumberOfPositions('1');
        $this->setEntityId($opportunity, 99);

        $request = new Request(content: json_encode([
            'opportunityId' => 99,
            'studentId' => 10,
            'notes' => 'Motivated candidate',
        ], JSON_THROW_ON_ERROR));

        $em = $this->createMock(EntityManagerInterface::class);
        $em->expects($this->once())->method('persist')->with($this->isInstanceOf(Candidature::class));
        $em->expects($this->once())->method('flush');

        $candidatureRepo = $this->createMock(CandidatureRepository::class);
        $candidatureRepo->method('findOneBy')->willReturn(null);

        $opportunitiesRepo = $this->createMock(OpportunitiesRepository::class);
        $opportunitiesRepo->method('find')->with(99)->willReturn($opportunity);

        $studentRepo = $this->createMock(StudentProfileRepository::class);
        $studentRepo->method('find')->with(10)->willReturn($student);

        $resolver = $this->createMock(ActorContextResolver::class);
        $resolver->method('hasRole')->willReturn(true);
        $resolver->method('resolveStudentProfile')->willReturn($student);
        $resolver->method('isStudentApproved')->willReturn(true);

        $controller = new CreateCandidature();
        $controller->setContainer($this->createControllerContainer($actor));

        $response = $controller->__invoke(
            $request,
            $em,
            $candidatureRepo,
            $opportunitiesRepo,
            $studentRepo,
            $resolver,
            new CandidatureResponseBuilder(),
            new HydraErrorResponseFactory(),
        );

        $payload = json_decode((string) $response->getContent(), true);
        $this->assertSame(201, $response->getStatusCode());
        $this->assertSame('applied', $payload['status']);
        $this->assertSame(99, $payload['opportunityId']);
        $this->assertSame(10, $payload['studentId']);
    }

    public function testDuplicateCandidatureReturnsConflict(): void
    {
        $actor = $this->buildUserWithRole('STUDENT', 1);
        $student = new StudentProfile();
        $student->setStatus('approved');
        $student->setIsApproved(true);
        $this->setEntityId($student, 10);

        $opportunity = new Opportunities();
        $opportunity->setTitle('Data Analyst');
        $opportunity->setType('job');
        $opportunity->setDepartment('Data');
        $opportunity->setLocation('Casablanca');
        $opportunity->setRemoteType('onsite');
        $opportunity->setSalaryLabel('market');
        $opportunity->setDescription('desc');
        $opportunity->setStatus('open');
        $opportunity->setIsEnabled(true);
        $opportunity->setPublishedAt(new \DateTimeImmutable());
        $opportunity->setApplicationDeadLine(new \DateTimeImmutable('+15 days'));
        $opportunity->setCreatedAt(new \DateTimeImmutable());
        $opportunity->setUpdatedAt(new \DateTimeImmutable());
        $opportunity->setIsDeleted(false);
        $opportunity->setCategory('data');
        $opportunity->setExperienceLevel('junior');
        $opportunity->setNumberOfPositions('1');
        $this->setEntityId($opportunity, 4);

        $request = new Request(content: json_encode([
            'opportunityId' => 4,
            'studentId' => 10,
        ], JSON_THROW_ON_ERROR));

        $candidatureRepo = $this->createMock(CandidatureRepository::class);
        $candidatureRepo->method('findOneBy')->willReturn(new Candidature());

        $controller = new CreateCandidature();
        $controller->setContainer($this->createControllerContainer($actor));

        $resolver = $this->createMock(ActorContextResolver::class);
        $resolver->method('hasRole')->willReturn(true);
        $resolver->method('resolveStudentProfile')->willReturn($student);
        $resolver->method('isStudentApproved')->willReturn(true);

        $opportunitiesRepo = $this->createMock(OpportunitiesRepository::class);
        $opportunitiesRepo->method('find')->with(4)->willReturn($opportunity);

        $studentRepo = $this->createMock(StudentProfileRepository::class);
        $studentRepo->method('find')->with(10)->willReturn($student);

        $response = $controller->__invoke(
            $request,
            $this->createMock(EntityManagerInterface::class),
            $candidatureRepo,
            $opportunitiesRepo,
            $studentRepo,
            $resolver,
            new CandidatureResponseBuilder(),
            new HydraErrorResponseFactory(),
        );

        $this->assertSame(409, $response->getStatusCode());
    }

    public function testRequiredFieldsAreValidated(): void
    {
        $actor = $this->buildUserWithRole('STUDENT', 1);
        $student = new StudentProfile();
        $this->setEntityId($student, 10);

        $request = new Request(content: json_encode(['opportunityId' => 4], JSON_THROW_ON_ERROR));

        $resolver = $this->createMock(ActorContextResolver::class);
        $resolver->method('hasRole')->willReturn(true);

        $controller = new CreateCandidature();
        $controller->setContainer($this->createControllerContainer($actor));

        $response = $controller->__invoke(
            $request,
            $this->createMock(EntityManagerInterface::class),
            $this->createMock(CandidatureRepository::class),
            $this->createMock(OpportunitiesRepository::class),
            $this->createMock(StudentProfileRepository::class),
            $resolver,
            new CandidatureResponseBuilder(),
            new HydraErrorResponseFactory(),
        );

        $this->assertSame(400, $response->getStatusCode());
    }

    public function testAuthenticationIsRequired(): void
    {
        $request = new Request(content: json_encode([
            'opportunityId' => 4,
            'studentId' => 10,
        ], JSON_THROW_ON_ERROR));

        $controller = new CreateCandidature();
        $controller->setContainer($this->createControllerContainer(null));

        $response = $controller->__invoke(
            $request,
            $this->createMock(EntityManagerInterface::class),
            $this->createMock(CandidatureRepository::class),
            $this->createMock(OpportunitiesRepository::class),
            $this->createMock(StudentProfileRepository::class),
            $this->createMock(ActorContextResolver::class),
            new CandidatureResponseBuilder(),
            new HydraErrorResponseFactory(),
        );

        $this->assertSame(401, $response->getStatusCode());
    }

    public function testStructuredNotesPayloadIsPreserved(): void
    {
        $actor = $this->buildUserWithRole('STUDENT', 50);
        $student = new StudentProfile();
        $student->setStatus('approved');
        $student->setIsApproved(true);
        $this->setEntityId($student, 10);

        $opportunity = new Opportunities();
        $opportunity->setTitle('Backend Intern');
        $opportunity->setType('internship');
        $opportunity->setDepartment('Engineering');
        $opportunity->setLocation('Remote');
        $opportunity->setRemoteType('remote');
        $opportunity->setSalaryLabel('n/a');
        $opportunity->setDescription('desc');
        $opportunity->setStatus('open');
        $opportunity->setIsEnabled(true);
        $opportunity->setPublishedAt(new \DateTimeImmutable());
        $opportunity->setApplicationDeadLine(new \DateTimeImmutable('+15 days'));
        $opportunity->setCreatedAt(new \DateTimeImmutable());
        $opportunity->setUpdatedAt(new \DateTimeImmutable());
        $opportunity->setIsDeleted(false);
        $opportunity->setCategory('tech');
        $opportunity->setExperienceLevel('junior');
        $opportunity->setNumberOfPositions('1');
        $this->setEntityId($opportunity, 99);

        $notes = [
            'motivation' => 'I am motivated by backend reliability and API design.',
            'personal' => [
                'summary' => 'Final-year CS student focused on distributed systems.',
            ],
        ];

        $request = new Request(content: json_encode([
            'opportunityId' => 99,
            'studentId' => 10,
            'notes' => $notes,
        ], JSON_THROW_ON_ERROR));

        $em = $this->createMock(EntityManagerInterface::class);
        $em->expects($this->once())->method('persist')->with($this->isInstanceOf(Candidature::class));
        $em->expects($this->once())->method('flush');

        $candidatureRepo = $this->createMock(CandidatureRepository::class);
        $candidatureRepo->method('findOneBy')->willReturn(null);

        $opportunitiesRepo = $this->createMock(OpportunitiesRepository::class);
        $opportunitiesRepo->method('find')->with(99)->willReturn($opportunity);

        $studentRepo = $this->createMock(StudentProfileRepository::class);
        $studentRepo->method('find')->with(10)->willReturn($student);

        $resolver = $this->createMock(ActorContextResolver::class);
        $resolver->method('hasRole')->willReturn(true);
        $resolver->method('resolveStudentProfile')->willReturn($student);
        $resolver->method('isStudentApproved')->willReturn(true);

        $controller = new CreateCandidature();
        $controller->setContainer($this->createControllerContainer($actor));

        $response = $controller->__invoke(
            $request,
            $em,
            $candidatureRepo,
            $opportunitiesRepo,
            $studentRepo,
            $resolver,
            new CandidatureResponseBuilder(),
            new HydraErrorResponseFactory(),
        );

        $payload = json_decode((string) $response->getContent(), true);

        $this->assertSame(201, $response->getStatusCode());
        $this->assertSame($notes, $payload['notes']);
    }
}
