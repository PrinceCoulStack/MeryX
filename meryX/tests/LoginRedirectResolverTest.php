<?php

namespace App\Tests;

use App\Entity\User;
use App\Entity\UserType;
use App\Security\LoginRedirectResolver;
use PHPUnit\Framework\TestCase;

final class LoginRedirectResolverTest extends TestCase
{
    public function testUniversityUserResolvesToUniversityInterface(): void
    {
        $resolver = new LoginRedirectResolver();
        $user = $this->buildUser('University');

        $resolved = $resolver->resolveForUser($user);

        $this->assertSame('UNIVERSITY', $resolved['role']);
        $this->assertSame(['ROLE_UNIVERSITY'], $resolved['roles']);
        $this->assertSame('University', $resolved['interface']);
        $this->assertSame('university', $resolved['interfaceKey']);
        $this->assertSame('/university', $resolved['redirectTarget']);
    }

    public function testAdminRoleResolvesToSuperAdminInterface(): void
    {
        $resolver = new LoginRedirectResolver();
        $user = $this->buildUser('Admin');

        $resolved = $resolver->resolveForUser($user);

        $this->assertSame('ADMIN', $resolved['role']);
        $this->assertSame('Super Admin', $resolved['interface']);
        $this->assertSame('/super-admin', $resolved['redirectTarget']);
    }

    public function testPrefixedStudentRoleResolvesToStudentInterface(): void
    {
        $resolver = new LoginRedirectResolver();
        $user = $this->buildUser('ROLE_STUDENT');

        $resolved = $resolver->resolveForUser($user);

        $this->assertSame('STUDENT', $resolved['role']);
        $this->assertSame(['ROLE_STUDENT'], $resolved['roles']);
        $this->assertSame('Student', $resolved['interface']);
        $this->assertSame('student', $resolved['interfaceKey']);
        $this->assertSame('/student', $resolved['redirectTarget']);
    }

    private function buildUser(string $roleName): User
    {
        $userType = new UserType();
        $userType->setName($roleName);
        $userType->setPermission([]);
        $userType->setDescription('test');
        $userType->setIsEnabled(true);
        $userType->setIsDeleted(false);

        $user = new User();
        $user->setUserTypeId($userType);

        return $user;
    }
}
