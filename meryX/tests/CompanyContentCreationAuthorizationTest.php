<?php

namespace App\Tests;

use App\Controller\CompanyPost\CreateCompanyPost;
use App\Controller\Opportunities\CreateOpportunities;
use App\Controller\Training\CreateTraining;
use App\Entity\Company;
use App\Entity\CompanyPost;
use App\Entity\Opportunities;
use App\Entity\Training;
use App\Entity\User;
use App\Entity\UserType;
use App\Repository\CompanyRepository;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

final class CompanyContentCreationAuthorizationTest extends TestCase
{
    public function testCompanyUserCanCreateCompanyPostWithoutExplicitCompanyOrAuthorPayload(): void
    {
        $actor = $this->buildCompanyUser(33);
        $actorCompany = new Company();
        $this->setEntityId($actorCompany, 5);

        $request = new Request(content: json_encode([
            'title' => 'Role test',
            'content' => 'Body',
            'category' => 'news',
            'visibility' => 'public',
            'imageUrl' => 'x',
            'publiedAt' => '2026-09-05T00:00:00+00:00',
        ], JSON_THROW_ON_ERROR));
        $request->setMethod('POST');

        $companyRepository = $this->createMock(CompanyRepository::class);
        $companyRepository->expects($this->once())
            ->method('findOneByUser')
            ->with($actor)
            ->willReturn($actorCompany);

        $entityManager = $this->createMock(EntityManagerInterface::class);
        $entityManager->expects($this->once())->method('persist')->with($this->callback(function (object $entity) use ($actor, $actorCompany): bool {
            if (!$entity instanceof CompanyPost) {
                return false;
            }

            return $entity->getCompanyId() === $actorCompany
                && $entity->getAuthorId() === $actor
                && $entity->getTitle() === 'Role test';
        }));
        $entityManager->expects($this->once())->method('flush');

        $controller = new CreateCompanyPost();
        $controller->setContainer($this->createControllerContainer($actor));

        $response = $controller->__invoke($entityManager, $request, $companyRepository);

        $this->assertSame(201, $response->getStatusCode());
    }

    public function testCompanyUserCanCreateOpportunityWithoutExplicitCompanyPayload(): void
    {
        $actor = $this->buildCompanyUser(33);
        $actorCompany = new Company();
        $this->setEntityId($actorCompany, 5);

        $request = new Request(content: json_encode([
            'title' => 'Dev Intern',
            'type' => 'internship',
            'department' => 'IT',
            'location' => 'Abidjan',
            'remoteType' => 'hybrid',
            'salaryLabel' => '1000',
            'description' => 'desc',
            'status' => 'open',
            'isEnabled' => true,
            'publishedAt' => '2026-09-05T00:00:00+00:00',
            'applicationDeadLine' => '2026-10-01T00:00:00+00:00',
            'isDeleted' => false,
            'category' => 'engineering',
            'experienceLevel' => 'junior',
            'numberOfPositions' => '1',
            'requirements' => ['php', 'symfony'],
        ], JSON_THROW_ON_ERROR));
        $request->setMethod('POST');

        $companyRepository = $this->createMock(CompanyRepository::class);
        $companyRepository->expects($this->once())
            ->method('findOneByUser')
            ->with($actor)
            ->willReturn($actorCompany);

        $entityManager = $this->createMock(EntityManagerInterface::class);
        $entityManager->expects($this->once())->method('persist')->with($this->callback(function (object $entity) use ($actorCompany): bool {
            if (!$entity instanceof Opportunities) {
                return false;
            }

            return $entity->getCompanyId() === $actorCompany
                && $entity->getTitle() === 'Dev Intern'
                && $entity->getRequirements() === ['php', 'symfony'];
        }));
        $entityManager->expects($this->once())->method('flush');

        $controller = new CreateOpportunities();
        $controller->setContainer($this->createControllerContainer($actor));

        $response = $controller->__invoke($entityManager, $request, $companyRepository);

        $this->assertSame(201, $response->getStatusCode());
    }

    public function testCompanyUserCanCreateTrainingWithoutExplicitCompanyPayload(): void
    {
        $actor = $this->buildCompanyUser(33);
        $actorCompany = new Company();
        $this->setEntityId($actorCompany, 5);

        $request = new Request(content: json_encode([
            'title' => 'Cloud Bootcamp',
            'type' => 'technical',
            'mode' => 'online',
            'location' => 'Remote',
            'durationLabel' => '4 weeks',
            'seatCount' => '30',
            'description' => 'learn',
            'status' => 'draft',
            'startAt' => '2026-10-01T00:00:00+00:00',
            'endAt' => '2026-10-31T00:00:00+00:00',
            'publishedAt' => '2026-09-05T00:00:00+00:00',
        ], JSON_THROW_ON_ERROR));
        $request->setMethod('POST');

        $companyRepository = $this->createMock(CompanyRepository::class);
        $companyRepository->expects($this->once())
            ->method('findOneByUser')
            ->with($actor)
            ->willReturn($actorCompany);

        $entityManager = $this->createMock(EntityManagerInterface::class);
        $entityManager->expects($this->once())->method('persist')->with($this->callback(function (object $entity) use ($actorCompany): bool {
            if (!$entity instanceof Training) {
                return false;
            }

            return $entity->getCompanyId() === $actorCompany
                && $entity->getTitle() === 'Cloud Bootcamp';
        }));
        $entityManager->expects($this->once())->method('flush');

        $controller = new CreateTraining();
        $controller->setContainer($this->createControllerContainer($actor));

        $response = $controller->__invoke($entityManager, $request, $companyRepository);

        $this->assertSame(201, $response->getStatusCode());
    }

    private function createControllerContainer(User $user): ContainerInterface
    {
        $token = $this->createMock(TokenInterface::class);
        $token->method('getUser')->willReturn($user);

        $tokenStorage = $this->createMock(TokenStorageInterface::class);
        $tokenStorage->method('getToken')->willReturn($token);

        $authorizationChecker = $this->createMock(AuthorizationCheckerInterface::class);
        $authorizationChecker->method('isGranted')->with('ROLE_COMPANY')->willReturn(true);

        return new class($tokenStorage, $authorizationChecker) implements ContainerInterface {
            public function __construct(
                private readonly TokenStorageInterface $tokenStorage,
                private readonly AuthorizationCheckerInterface $authorizationChecker
            ) {
            }

            public function get(string $id)
            {
                if ($id === 'security.token_storage') {
                    return $this->tokenStorage;
                }

                if ($id === 'security.authorization_checker') {
                    return $this->authorizationChecker;
                }

                throw new class extends \RuntimeException implements \Psr\Container\NotFoundExceptionInterface {
                };
            }

            public function has(string $id): bool
            {
                return in_array($id, ['security.token_storage', 'security.authorization_checker'], true);
            }
        };
    }

    private function buildCompanyUser(int $id): User
    {
        $user = new User();
        $user->setEmail('hellom@gmail.com');

        $companyType = new UserType();
        $companyType->setName('Company');
        $companyType->setPermission([]);
        $companyType->setDescription('test');
        $companyType->setIsEnabled(true);
        $companyType->setIsDeleted(false);

        $user->setUserTypeId($companyType);
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
