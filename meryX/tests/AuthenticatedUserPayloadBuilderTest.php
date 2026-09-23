<?php

namespace App\Tests;

use App\Entity\User;
use App\Entity\UserType;
use App\Security\AuthenticatedUserPayloadBuilder;
use App\Security\LoginRedirectResolver;
use PHPUnit\Framework\TestCase;

final class AuthenticatedUserPayloadBuilderTest extends TestCase
{
    public function testBuildReturnsConsistentRoleAndInterfaceMetadata(): void
    {
        $userType = new UserType();
        $userType->setName('University');
        $userType->setPermission(['students:read']);
        $userType->setDescription('test');
        $userType->setIsEnabled(true);
        $userType->setIsDeleted(false);

        $user = new User();
        $user->setUserTypeId($userType);
        $user->setEmail('university@example.test');

        $builder = new AuthenticatedUserPayloadBuilder(new LoginRedirectResolver());
        $payload = $builder->build($user);

        $this->assertSame('university@example.test', $payload['email']);
        $this->assertSame('UNIVERSITY', $payload['role']);
        $this->assertSame(['ROLE_UNIVERSITY'], $payload['roles']);
        $this->assertSame('University', $payload['interface']);
        $this->assertSame('university', $payload['interfaceKey']);
        $this->assertSame('/university', $payload['redirectTarget']);
        $this->assertSame(['students:read'], $payload['permissions']);
        $this->assertSame('UNIVERSITY', $payload['userTypeId']['role']);
    }
}
