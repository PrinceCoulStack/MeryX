<?php

namespace App\Tests;

use App\Controller\StudentProfile\ListStudentRegistrationRequests;
use App\Entity\StudentProfile;
use App\Entity\University;
use App\Entity\User;
use App\Entity\UserType;
use App\Repository\StudentProfileRepository;
use App\Security\StudentProfileAccessService;
use App\Service\StudentProfile\StudentProfileResponseBuilder;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

final class ListStudentRegistrationRequestsTest extends TestCase
{
    public function testUniversityCanListOwnPendingRequests(): void
    {
        $actor = $this->buildUserWithRole('UNIVERSITY', 12);
        $actorUniversity = new University();
        $this->setEntityId($actorUniversity, 4);

        $requestProfile = new StudentProfile();
        $requestProfile->setFullName('Pending Student');
        $requestProfile->setGpa('0.00');
        $requestProfile->setGender('prefer_not_to_say');
        $requestProfile->setStatus('pending');
        $requestProfile->setIsApproved(false);
        $requestProfile->setUniversityId($actorUniversity);

        $request = new Request(['status' => 'pending']);

        $repository = $this->createMock(StudentProfileRepository::class);
        $repository->expects($this->once())
            ->method('findRegistrationRequestsForUniversity')
            ->with($actorUniversity, 'pending')
            ->willReturn([$requestProfile]);

        $accessService = $this->createMock(StudentProfileAccessService::class);
        $accessService->method('hasRole')->willReturnMap([
            [$actor, 'ROLE_ADMIN', false],
            [$actor, 'ROLE_UNIVERSITY', true],
        ]);
        $accessService->method('resolveUniversityForActor')->with($actor)->willReturn($actorUniversity);

        $responseBuilder = $this->createMock(StudentProfileResponseBuilder::class);
        $responseBuilder->method('buildCollection')->with([$requestProfile])->willReturn([
            [
                'fullName' => 'Pending Student',
                'status' => 'pending',
                'verificationData' => ['program' => 'CS'],
            ],
        ]);

        $controller = new ListStudentRegistrationRequests();
        $controller->setContainer($this->createControllerContainer($actor));

        $response = $controller->__invoke($request, $repository, $accessService, $responseBuilder);
        $payload = json_decode((string) $response->getContent(), true);

        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame('pending', $payload[0]['status']);
        $this->assertSame('CS', $payload[0]['verificationData']['program']);
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
