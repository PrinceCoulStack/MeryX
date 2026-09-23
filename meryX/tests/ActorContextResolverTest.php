<?php

namespace App\Tests;

use App\Entity\User;
use App\Entity\UserType;
use App\Repository\CompanyRepository;
use App\Repository\StudentProfileRepository;
use App\Security\Authorization\ActorContextResolver;
use PHPUnit\Framework\TestCase;

final class ActorContextResolverTest extends TestCase
{
    public function testSuperAdminIsTreatedAsAdminForRoleChecks(): void
    {
        $resolver = new ActorContextResolver(
            $this->createMock(StudentProfileRepository::class),
            $this->createMock(CompanyRepository::class),
        );

        $user = new User();
        $userType = new UserType();
        $userType->setName('SUPER_ADMIN');
        $userType->setPermission([]);
        $userType->setDescription('super admin');
        $userType->setIsEnabled(true);
        $userType->setIsDeleted(false);
        $user->setUserTypeId($userType);

        $this->assertSame(['ROLE_SUPER_ADMIN'], $user->getRoles());
        $this->assertTrue($resolver->hasRole($user, 'ROLE_ADMIN'));
        $this->assertTrue($resolver->hasRole($user, 'ADMIN'));
        $this->assertTrue($resolver->hasRole($user, 'ROLE_SUPER_ADMIN'));
    }
}
