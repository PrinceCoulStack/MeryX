<?php

namespace App\Tests;

use App\Controller\StudentProfile\ReviewStudentRegistrationRequest;
use App\Entity\StudentProfile;
use App\Entity\University;
use App\Entity\User;
use App\Entity\UserType;
use App\Security\StudentProfileAccessService;
use App\Service\StudentProfile\StudentProfileResponseBuilder;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

final class ReviewStudentRegistrationRequestTest extends TestCase
{
    public function testUniversityCannotReviewOtherUniversityRequest(): void
    {
        $actor = $this->buildUserWithRole('UNIVERSITY', 3);

        $profile = new StudentProfile();
        $profile->setFullName('Student Request');
        $profile->setGpa('0.00');
        $profile->setGender('prefer_not_to_say');
        $profile->setStatus('pending');
        $profile->setIsApproved(false);

        $request = new Request(content: json_encode(['status' => 'approved'], JSON_THROW_ON_ERROR));
        $request->setMethod('PATCH');

        $accessService = $this->createMock(StudentProfileAccessService::class);
        $accessService->method('hasRole')->willReturnMap([
            [$actor, 'ROLE_UNIVERSITY', true],
            [$actor, 'ROLE_ADMIN', false],
        ]);
        $accessService->method('canEditProfile')->with($actor, $profile)->willReturn(false);

        $responseBuilder = $this->createMock(StudentProfileResponseBuilder::class);
        $entityManager = $this->createMock(EntityManagerInterface::class);
        $entityManager->expects($this->never())->method('flush');

        $controller = new ReviewStudentRegistrationRequest();
        $controller->setContainer($this->createControllerContainer($actor));

        $response = $controller->__invoke($profile, $request, $accessService, $responseBuilder, $entityManager);

        $this->assertSame(403, $response->getStatusCode());
    }

    public function testApprovedReviewSetsApprovalFields(): void
    {
        $actor = $this->buildUserWithRole('UNIVERSITY', 3);
        $actorUniversity = new University();
        $this->setEntityId($actorUniversity, 1);

        $profile = new StudentProfile();
        $profile->setFullName('Student Request');
        $profile->setGpa('0.00');
        $profile->setGender('prefer_not_to_say');
        $profile->setStatus('pending');
        $profile->setIsApproved(false);
        $profile->setUniversityId($actorUniversity);

        $request = new Request(content: json_encode([
            'status' => 'approved',
            'reviewNote' => 'Verified transcript and identity.',
        ], JSON_THROW_ON_ERROR));
        $request->setMethod('PATCH');

        $accessService = $this->createMock(StudentProfileAccessService::class);
        $accessService->method('hasRole')->willReturnMap([
            [$actor, 'ROLE_UNIVERSITY', true],
            [$actor, 'ROLE_ADMIN', false],
        ]);
        $accessService->method('canEditProfile')->with($actor, $profile)->willReturn(true);

        $entityManager = $this->createMock(EntityManagerInterface::class);
        $entityManager->expects($this->once())->method('flush');

        $responseBuilder = $this->createMock(StudentProfileResponseBuilder::class);
        $responseBuilder->method('buildItem')->with($profile)->willReturn([
            'status' => 'approved',
            'isApproved' => true,
        ]);

        $controller = new ReviewStudentRegistrationRequest();
        $controller->setContainer($this->createControllerContainer($actor));

        $response = $controller->__invoke($profile, $request, $accessService, $responseBuilder, $entityManager);
        $payload = json_decode((string) $response->getContent(), true);

        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame('approved', $profile->getStatus());
        $this->assertTrue($profile->isApproved());
        $this->assertNotNull($profile->getReviewedAt());
        $this->assertSame('approved', $payload['status']);
    }

    public function testPutApprovalFlowMarksStudentProfileApproved(): void
    {
        $actor = $this->buildUserWithRole('UNIVERSITY', 3);
        $actorUniversity = new University();
        $this->setEntityId($actorUniversity, 1);

        $profile = new StudentProfile();
        $profile->setFullName('Student Request');
        $profile->setGpa('0.00');
        $profile->setGender('prefer_not_to_say');
        $profile->setStatus('pending');
        $profile->setIsApproved(false);
        $profile->setUniversityId($actorUniversity);
        $profile->setUserId(new User());

        $request = new Request(content: json_encode([
            'status' => 'approved',
            'isApproved' => true,
            'reviewNote' => 'Approved by university.',
        ], JSON_THROW_ON_ERROR));
        $request->setMethod('PUT');

        $studentProfileRepository = $this->createMock(\App\Repository\StudentProfileRepository::class);
        $studentProfileRepository->method('find')->with(12)->willReturn($profile);

        $accessService = $this->createMock(StudentProfileAccessService::class);
        $accessService->method('canEditProfile')->with($actor, $profile)->willReturn(true);

        $upsertService = $this->createMock(\App\Service\StudentProfile\StudentProfileUpsertService::class);
        $upsertService->expects($this->once())->method('update')->with($profile, $this->callback(function ($input): bool {
            return $input->status === 'approved' && $input->isApproved === true;
        }))->willReturnCallback(function ($profileEntity, $input) {
            $profileEntity->setStatus('approved');
            $profileEntity->setIsApproved(true);
            return $profileEntity;
        });

        $radarStorageService = $this->createMock(\App\Service\StudentProfile\StudentRadarStorageService::class);
        $radarStorageService->method('hasRadarProofFiles')->willReturn(false);

        $responseBuilder = $this->createMock(StudentProfileResponseBuilder::class);
        $responseBuilder->method('buildItem')->with($profile)->willReturn([
            'status' => 'approved',
            'isApproved' => true,
        ]);

        $entityManager = $this->createMock(EntityManagerInterface::class);
        $controller = new \App\Controller\StudentProfile\UpdateStudentProfile();
        $controller->setContainer($this->createControllerContainer($actor));

        $response = $controller->__invoke($request, $studentProfileRepository, $this->createMock(\Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface::class), $upsertService, $accessService, $radarStorageService, $responseBuilder, 12);

        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame('approved', $profile->getStatus());
        $this->assertTrue($profile->isApproved());
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
