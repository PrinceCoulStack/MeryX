<?php

namespace App\Tests;

use App\Controller\Users\CreateUser;
use App\Dto\StudentProfileUpsertInput;
use App\Entity\StudentProfile;
use App\Entity\University;
use App\Entity\User;
use App\Entity\UserType;
use App\Exception\DomainValidationException;
use App\Repository\AddressRepository;
use App\Repository\StudentProfileRepository;
use App\Repository\UniversityRepository;
use App\Repository\UserRepository;
use App\Repository\UserTypeRepository;
use App\Service\StudentProfile\StudentProfileResponseBuilder;
use App\Service\StudentProfile\StudentProfileUpsertService;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

final class CreateUserTest extends TestCase
{
    public function testStudentRegistrationCreatesProfileWithinTransaction(): void
    {
        $controller = new CreateUser();
        $request = new Request(content: json_encode([
            'email' => 'student@example.test',
            'password' => 'Secret123',
            'phone' => '123456789',
            'userTypeId' => 4,
            'universityName' => 'Uni Test',
            'universityEmail' => 'uni@example.test',
            'program' => 'Computer Science',
            'level' => 'master',
            'fullName' => 'Student Example',
            'gpa' => '3.25',
            'gender' => 'female',
        ], JSON_THROW_ON_ERROR));
        $request->setMethod('POST');

        $entityManager = $this->createMock(EntityManagerInterface::class);
        $connection = $this->createMock(Connection::class);
        $connection->method('isTransactionActive')->willReturn(false);
        $entityManager->method('getConnection')->willReturn($connection);

        $persistedUser = null;
        $entityManager->expects($this->once())->method('persist')->willReturnCallback(function (User $user) use (&$persistedUser): void {
            $persistedUser = $user;
            $this->setEntityId($user, 123);
        });
        $entityManager->expects($this->once())->method('beginTransaction');
        $entityManager->expects($this->once())->method('commit');
        $entityManager->expects($this->never())->method('rollback');
        $entityManager->expects($this->exactly(2))->method('flush');

        $passwordHasher = $this->createMock(UserPasswordHasherInterface::class);
        $passwordHasher->method('hashPassword')->willReturn('hashed-password');

        $userRepository = $this->createMock(UserRepository::class);
        $userRepository->method('findOneBy')->with(['email' => 'student@example.test'])->willReturn(null);

        $userType = new UserType();
        $userType->setName('Student');
        $userType->setPermission([]);
        $userType->setDescription('test');
        $userType->setIsEnabled(true);
        $userType->setIsDeleted(false);

        $userTypeRepository = $this->createMock(UserTypeRepository::class);
        $userTypeRepository->method('find')->with(4)->willReturn($userType);

        $addressRepository = $this->createMock(AddressRepository::class);

        $university = new University();
        $this->setEntityId($university, 77);

        $universityRepository = $this->createMock(UniversityRepository::class);
        $universityRepository->method('findOneByExactNameAndEmail')->with('Uni Test', 'uni@example.test')->willReturn($university);

        $studentProfileRepository = $this->createMock(StudentProfileRepository::class);
        $studentProfileRepository->method('findOneBy')->willReturn(null);

        $studentProfile = new StudentProfile();
        $studentProfile->setFullName('Student Example');
        $studentProfile->setGpa('3.25');
        $studentProfile->setGender('female');
        $studentProfile->setUserId((new User())->setEmail('student@example.test'));
        $studentProfile->setUniversityId(new University());

        $upsertService = $this->createMock(StudentProfileUpsertService::class);
        $upsertService->expects($this->once())->method('create')->with($this->callback(function (StudentProfileUpsertInput $input): bool {
            return $input->userId === 123 && (string) $input->universityId === '77';
        }))->willReturn($studentProfile);

        $responseBuilder = $this->createMock(StudentProfileResponseBuilder::class);
        $responseBuilder->method('buildItem')->willReturn(['id' => 99, 'userId' => 123, 'universityId' => 77]);

        $response = $controller->__invoke($request, $entityManager, $passwordHasher, $userRepository, $userTypeRepository, $addressRepository, $studentProfileRepository, $upsertService, $responseBuilder, $universityRepository);
        $payload = json_decode((string) $response->getContent(), true);

        $this->assertSame(201, $response->getStatusCode());
        $this->assertSame(123, $payload['userId']);
        $this->assertSame(99, $payload['studentProfile']['id']);
        $this->assertSame('pending', $persistedUser?->getStatus());
    }

    public function testStudentRegistrationAcceptsUniversityIdFromStudentProfilesWrappedPayload(): void
    {
        $controller = new CreateUser();
        $request = new Request(content: json_encode([
            'email' => 'student@example.test',
            'password' => 'Secret123',
            'phone' => '123456789',
            'userTypeId' => 4,
            'studentProfiles' => [
                'universityId' => 77,
                'fullName' => 'Student Example',
                'gpa' => '3.25',
                'gender' => 'female',
            ],
        ], JSON_THROW_ON_ERROR));
        $request->setMethod('POST');

        $entityManager = $this->createMock(EntityManagerInterface::class);
        $connection = $this->createMock(Connection::class);
        $connection->method('isTransactionActive')->willReturn(false);
        $entityManager->method('getConnection')->willReturn($connection);
        $persistedUser = null;
        $entityManager->expects($this->once())->method('persist')->willReturnCallback(function (User $user) use (&$persistedUser): void {
            $persistedUser = $user;
            $this->setEntityId($user, 123);
        });
        $entityManager->expects($this->once())->method('beginTransaction');
        $entityManager->expects($this->once())->method('commit');
        $entityManager->expects($this->exactly(2))->method('flush');

        $passwordHasher = $this->createMock(UserPasswordHasherInterface::class);
        $passwordHasher->method('hashPassword')->willReturn('hashed-password');

        $userRepository = $this->createMock(UserRepository::class);
        $userRepository->method('findOneBy')->with(['email' => 'student@example.test'])->willReturn(null);

        $userType = new UserType();
        $userType->setName('Student');
        $userType->setPermission([]);
        $userType->setDescription('test');
        $userType->setIsEnabled(true);
        $userType->setIsDeleted(false);

        $userTypeRepository = $this->createMock(UserTypeRepository::class);
        $userTypeRepository->method('find')->with(4)->willReturn($userType);

        $addressRepository = $this->createMock(AddressRepository::class);

        $university = new University();
        $this->setEntityId($university, 77);
        $university->setName('Uni Test');
        $user = new User();
        $user->setEmail('uni@example.test');
        $university->setUserId($user);

        $universityRepository = $this->createMock(UniversityRepository::class);
        $universityRepository->method('find')->with(77)->willReturn($university);
        $universityRepository->method('findOneByExactNameAndEmail')->willReturn(null);

        $studentProfileRepository = $this->createMock(StudentProfileRepository::class);
        $studentProfileRepository->method('findOneBy')->willReturn(null);

        $studentProfile = new StudentProfile();
        $studentProfile->setFullName('Student Example');
        $studentProfile->setGpa('3.25');
        $studentProfile->setGender('female');
        $studentProfile->setUserId((new User())->setEmail('student@example.test'));
        $studentProfile->setUniversityId($university);

        $upsertService = $this->createMock(StudentProfileUpsertService::class);
        $upsertService->expects($this->once())->method('create')->with($this->callback(function (StudentProfileUpsertInput $input): bool {
            return $input->userId === 123 && (string) $input->universityId === '77';
        }))->willReturn($studentProfile);

        $responseBuilder = $this->createMock(StudentProfileResponseBuilder::class);
        $responseBuilder->method('buildItem')->willReturn(['id' => 99, 'userId' => 123, 'universityId' => 77]);

        $response = $controller->__invoke($request, $entityManager, $passwordHasher, $userRepository, $userTypeRepository, $addressRepository, $studentProfileRepository, $upsertService, $responseBuilder, $universityRepository);
        $payload = json_decode((string) $response->getContent(), true);

        $this->assertSame(201, $response->getStatusCode());
        $this->assertSame(123, $payload['userId']);
        $this->assertSame(77, $payload['studentProfile']['universityId']);
    }

    public function testStudentRegistrationRollsBackWhenProfileValidationFails(): void
    {
        $controller = new CreateUser();
        $request = new Request(content: json_encode([
            'email' => 'student@example.test',
            'password' => 'Secret123',
            'phone' => '123456789',
            'userTypeId' => 4,
            'universityName' => 'Uni Test',
            'universityEmail' => 'uni@example.test',
            'fullName' => 'Student Example',
            'gpa' => '3.25',
            'gender' => 'female',
            'studentProfiles' => [],
        ], JSON_THROW_ON_ERROR));
        $request->setMethod('POST');

        $entityManager = $this->createMock(EntityManagerInterface::class);
        $connection = $this->createMock(Connection::class);
        $connection->method('isTransactionActive')->willReturn(false);
        $entityManager->method('getConnection')->willReturn($connection);
        $entityManager->expects($this->once())->method('persist');
        $entityManager->expects($this->once())->method('beginTransaction');
        $entityManager->expects($this->never())->method('commit');
        $entityManager->expects($this->once())->method('rollback');
        $entityManager->expects($this->once())->method('flush');

        $passwordHasher = $this->createMock(UserPasswordHasherInterface::class);
        $passwordHasher->method('hashPassword')->willReturn('hashed-password');

        $userRepository = $this->createMock(UserRepository::class);
        $userRepository->method('findOneBy')->willReturn(null);

        $userType = new UserType();
        $userType->setName('Student');
        $userType->setPermission([]);
        $userType->setDescription('test');
        $userType->setIsEnabled(true);
        $userType->setIsDeleted(false);

        $userTypeRepository = $this->createMock(UserTypeRepository::class);
        $userTypeRepository->method('find')->willReturn($userType);

        $addressRepository = $this->createMock(AddressRepository::class);
        $studentProfileRepository = $this->createMock(StudentProfileRepository::class);
        $universityRepository = $this->createMock(UniversityRepository::class);

        $university = new University();
        $this->setEntityId($university, 77);
        $universityRepository->method('findOneByExactNameAndEmail')->willReturn($university);

        $upsertService = $this->createMock(StudentProfileUpsertService::class);
        $upsertService->method('create')->willThrowException(new DomainValidationException([
            'academic.universityId' => ['University relation is required'],
        ]));

        $responseBuilder = $this->createMock(StudentProfileResponseBuilder::class);

        $response = $controller->__invoke($request, $entityManager, $passwordHasher, $userRepository, $userTypeRepository, $addressRepository, $studentProfileRepository, $upsertService, $responseBuilder, $universityRepository);
        $payload = json_decode((string) $response->getContent(), true);

        $this->assertSame(422, $response->getStatusCode());
        $this->assertSame(['University relation is required'], $payload['errors']['academic.universityId']);
    }

    private function setEntityId(object $entity, int $id): void
    {
        $reflection = new \ReflectionClass($entity);
        $property = $reflection->getProperty('id');
        $property->setAccessible(true);
        $property->setValue($entity, $id);
    }
}
