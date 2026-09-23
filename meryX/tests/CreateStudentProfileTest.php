<?php

namespace App\Tests;

use App\Controller\StudentProfile\CreateStudentProfile;
use App\Entity\StudentProfile;
use App\Entity\University;
use App\Entity\User;
use App\Entity\UserType;
use App\Security\StudentProfileAccessService;
use App\Service\StudentProfile\StudentProfileResponseBuilder;
use App\Service\StudentProfile\StudentProfileUpsertService;
use App\Service\StudentProfile\StudentRadarStorageService;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

final class CreateStudentProfileTest extends TestCase
{
    public function testUniversityActorCanCreateWhenPayloadUsesUniversityIri(): void
    {
        $actor = $this->buildUserWithRole('UNIVERSITY', 32);
        $actorUniversity = new University();
        $this->setEntityId($actorUniversity, 2);

        $request = new Request(content: json_encode([
            'userId' => 44,
            'universityId' => '/universities/2',
            'fullName' => 'Fatim Student',
            'gpa' => '3.20',
            'gender' => 'female',
        ], JSON_THROW_ON_ERROR));
        $request->setMethod('POST');

        $params = $this->createMock(ParameterBagInterface::class);
        $upsertService = $this->createMock(StudentProfileUpsertService::class);
        $accessService = $this->createMock(StudentProfileAccessService::class);
        $radarStorageService = $this->createMock(StudentRadarStorageService::class);
        $responseBuilder = $this->createMock(StudentProfileResponseBuilder::class);

        $createdProfile = new StudentProfile();
        $createdProfile->setFullName('Fatim Student');
        $createdProfile->setGpa('3.20');
        $createdProfile->setGender('female');
        $createdProfile->setUserId(new User());
        $createdProfile->setUniversityId($actorUniversity);

        $accessService->method('hasRole')->willReturnMap([
            [$actor, 'ROLE_ADMIN', false],
            [$actor, 'ROLE_UNIVERSITY', true],
            [$actor, 'ROLE_STUDENT', false],
        ]);
        $accessService->method('resolveUniversityForActor')->with($actor)->willReturn($actorUniversity);

        $radarStorageService->method('normalizeBioPayload')->willReturn([]);
        $radarStorageService->method('integrateRadarProofFiles')->willReturn([]);

        $upsertService->expects($this->once())->method('create')->with($this->callback(function ($input): bool {
            return $input->universityId === 2;
        }))->willReturn($createdProfile);

        $responseBuilder->method('buildItem')->willReturn(['id' => 10, 'universityId' => 2]);

        $controller = new CreateStudentProfile();
        $controller->setContainer($this->createControllerContainer($actor));

        $response = $controller->__invoke($request, $params, $upsertService, $accessService, $radarStorageService, $responseBuilder);
        $payload = json_decode((string) $response->getContent(), true);

        $this->assertSame(201, $response->getStatusCode());
        $this->assertSame(2, $payload['universityId']);
    }

    private function createControllerContainer(User $user): ContainerInterface
    {
        $token = $this->createMock(TokenInterface::class);
        $token->method('getUser')->willReturn($user);

        $tokenStorage = $this->createMock(TokenStorageInterface::class);
        $tokenStorage->method('getToken')->willReturn($token);

        return new class($tokenStorage) implements ContainerInterface {
            public function __construct(private readonly TokenStorageInterface $tokenStorage)
            {
            }

            public function get(string $id)
            {
                if ($id === 'security.token_storage') {
                    return $this->tokenStorage;
                }

                throw new class extends \RuntimeException implements \Psr\Container\NotFoundExceptionInterface {
                };
            }

            public function has(string $id): bool
            {
                return $id === 'security.token_storage';
            }
        };
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
