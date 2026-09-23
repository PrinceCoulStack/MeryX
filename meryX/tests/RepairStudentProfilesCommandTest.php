<?php

namespace App\Tests;

use App\Command\RepairStudentProfilesCommand;
use App\Entity\StudentProfile;
use App\Entity\University;
use App\Entity\User;
use App\Repository\StudentProfileRepository;
use App\Repository\UniversityRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Tester\CommandTester;

final class RepairStudentProfilesCommandTest extends TestCase
{
    public function testRepairsMissingRelationsAndProfileUrlFromBioPayload(): void
    {
        $profile = new StudentProfile();
        $this->setEntityId($profile, 5);
        $profile->setFullName('Repair Student');
        $profile->setGpa('3.45');
        $profile->setGender('female');
        $profile->setBio(json_encode([
            'personal' => ['userId' => 12],
            'academic' => ['universityId' => 44],
        ], JSON_THROW_ON_ERROR));

        $studentProfileRepository = $this->createMock(StudentProfileRepository::class);
        $studentProfileRepository->method('findAll')->willReturn([$profile]);

        $user = new User();
        $user->setEmail('repair@example.test');
        $userRepository = $this->createMock(UserRepository::class);
        $userRepository->method('find')->with(12)->willReturn($user);

        $university = new University();
        $this->setEntityId($university, 44);
        $universityRepository = $this->createMock(UniversityRepository::class);
        $universityRepository->method('find')->with(44)->willReturn($university);

        $entityManager = $this->createMock(EntityManagerInterface::class);
        $entityManager->expects($this->once())->method('flush');

        $command = new RepairStudentProfilesCommand($studentProfileRepository, $userRepository, $universityRepository, $entityManager);
        $tester = new CommandTester($command);
        $exitCode = $tester->execute([]);

        $this->assertSame(0, $exitCode);
        $this->assertSame($user, $profile->getUserId());
        $this->assertSame($university, $profile->getUniversityId());
        $this->assertStringContainsString('ui-avatars.com', (string) $profile->getProfileUrl());
    }

    private function setEntityId(object $entity, int $id): void
    {
        $reflection = new \ReflectionClass($entity);
        $property = $reflection->getProperty('id');
        $property->setAccessible(true);
        $property->setValue($entity, $id);
    }
}
