<?php

namespace App\Tests;

use App\Entity\User;
use App\Entity\UserType;
use App\Repository\StudentProfileRepository;
use App\Security\UserLoginChecker;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAccountStatusException;

final class UserLoginCheckerTest extends TestCase
{
    public function testActiveUserWithEnabledRolePasses(): void
    {
        $user = $this->buildUser(true, 'active', true, false);
        $profile = new \App\Entity\StudentProfile();
        $profile->setStatus('approved');
        $profile->setIsApproved(true);

        $repository = $this->createMock(StudentProfileRepository::class);
        $repository->method('findOneBy')->willReturn($profile);

        $checker = new UserLoginChecker($repository);
        $checker->checkPreAuth($user);

        $this->addToAssertionCount(1);
    }

    public function testInactiveUserIsRejected(): void
    {
        $checker = new UserLoginChecker($this->createMock(StudentProfileRepository::class));

        $this->expectException(CustomUserMessageAccountStatusException::class);
        $checker->checkPreAuth($this->buildUser(false, 'active', true, false));
    }

    public function testDisabledRoleIsRejected(): void
    {
        $checker = new UserLoginChecker($this->createMock(StudentProfileRepository::class));

        $this->expectException(CustomUserMessageAccountStatusException::class);
        $checker->checkPreAuth($this->buildUser(true, 'active', false, false));
    }

    public function testPendingStudentIsRejected(): void
    {
        $profile = new \App\Entity\StudentProfile();
        $profile->setStatus('pending');
        $profile->setIsApproved(false);

        $repository = $this->createMock(StudentProfileRepository::class);
        $repository->method('findOneBy')->willReturn($profile);

        $checker = new UserLoginChecker($repository);

        $this->expectException(CustomUserMessageAccountStatusException::class);
        $checker->checkPreAuth($this->buildUser(true, 'active', true, false));
    }

    public function testStudentWithApprovedStatusCanLoginEvenIfLegacyBooleanIsFalse(): void
    {
        $profile = new \App\Entity\StudentProfile();
        $profile->setStatus('approved');
        $profile->setIsApproved(false);

        $repository = $this->createMock(StudentProfileRepository::class);
        $repository->method('findOneBy')->willReturn($profile);

        $checker = new UserLoginChecker($repository);
        $checker->checkPreAuth($this->buildUser(true, 'active', true, false));

        $this->addToAssertionCount(1);
    }

    public function testStudentRolePrefixedWithRoleNamespaceIsAccepted(): void
    {
        $userType = new UserType();
        $userType->setName('ROLE_STUDENT');
        $userType->setPermission([]);
        $userType->setDescription('test');
        $userType->setIsEnabled(true);
        $userType->setIsDeleted(false);

        $profile = new \App\Entity\StudentProfile();
        $profile->setStatus('approved');
        $profile->setIsApproved(true);

        $user = new User();
        $user->setUserTypeId($userType);
        $user->setIsActived(true);
        $user->setStatus('active');

        $repository = $this->createMock(StudentProfileRepository::class);
        $repository->method('findOneBy')->willReturn($profile);

        $checker = new UserLoginChecker($repository);
        $checker->checkPreAuth($user);

        $this->addToAssertionCount(1);
    }

    private function buildUser(bool $isActived, string $status, bool $roleEnabled, bool $roleDeleted): User
    {
        $userType = new UserType();
        $userType->setName('Student');
        $userType->setPermission([]);
        $userType->setDescription('test');
        $userType->setIsEnabled($roleEnabled);
        $userType->setIsDeleted($roleDeleted);

        $user = new User();
        $user->setUserTypeId($userType);
        $user->setIsActived($isActived);
        $user->setStatus($status);

        return $user;
    }
}
