<?php

namespace App\Tests;

use App\Controller\University\ListUniversity;
use App\Entity\University;
use App\Entity\User;
use App\Repository\UniversityRepository;
use PHPUnit\Framework\TestCase;

final class ListUniversityTest extends TestCase
{
    public function testListContainsEmailFromLinkedUser(): void
    {
        $user = new User();
        $user->setEmail('admin@university.test');

        $university = new University();
        $university->setName('ENSA Test');
        $university->setType('Public');
        $university->setAccreditationNumber('ACC-123');
        $university->setRankingScore('1');
        $university->setIsApproved(true);
        $university->setDescription('desc');
        $university->setStatus('approved');
        $university->setRegistrationNumber('REG-1');
        $university->setUpdatedAt(new \DateTimeImmutable());
        $university->setLogoUrl('logo.png');
        $university->setUserId($user);
        $this->setEntityId($university, 7);

        $repo = $this->createMock(UniversityRepository::class);
        $repo->expects($this->once())->method('findAll')->willReturn([$university]);

        $controller = new ListUniversity();
        $response = $controller->__invoke($repo);

        $payload = json_decode((string) $response->getContent(), true);

        $this->assertSame(200, $response->getStatusCode());
        $this->assertCount(1, $payload);
        $this->assertSame([
            'id' => 7,
            'name' => 'ENSA Test',
            'email' => 'admin@university.test',
            'status' => 'approved',
        ], $payload[0]);
    }

    private function setEntityId(object $entity, int $id): void
    {
        $reflection = new \ReflectionClass($entity);
        $property = $reflection->getProperty('id');
        $property->setAccessible(true);
        $property->setValue($entity, $id);
    }
}
