<?php

namespace App\Tests\Support;

use App\Entity\User;
use App\Entity\UserType;
use Psr\Container\ContainerInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

trait ControllerAuthTestHelper
{
    private function createControllerContainer(?User $user): ContainerInterface
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
