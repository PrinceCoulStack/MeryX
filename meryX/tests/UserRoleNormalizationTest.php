<?php

namespace App\Tests;

use App\Entity\User;
use App\Entity\UserType;
use PHPUnit\Framework\TestCase;

final class UserRoleNormalizationTest extends TestCase
{
    public function testUserRoleNamesAreNormalizedForFrontendRouting(): void
    {
        $userType = new UserType();
        $userType->setName('Super Admin');

        $user = new User();
        $user->setUserTypeId($userType);

        $this->assertSame('SUPER_ADMIN', $userType->getRoleName());
        $this->assertSame('SUPER_ADMIN', $user->getRoleName());
        $this->assertSame(['ROLE_SUPER_ADMIN'], $user->getRoles());
    }

    public function testRoleNamesStayStableAcrossExpectedVariants(): void
    {
        $cases = [
            'SUPER_ADMIN' => 'Super Admin',
            'ADMIN' => 'Admin',
            'UNIVERSITY' => 'University',
            'COMPANY' => 'Company',
            'STUDENT' => 'Student',
        ];

        foreach ($cases as $expected => $rawName) {
            $role = new UserType();
            $role->setName($rawName);

            $this->assertSame($expected, $role->getRoleName());
        }
    }

    public function testAssignedUserCountUsesActualUsers(): void
    {
        $role = new UserType();
        $userOne = new User();
        $userTwo = new User();

        $role->addUser($userOne);
        $role->addUser($userTwo);

        $this->assertSame(2, $role->getAssignedUserCount());
    }
}
