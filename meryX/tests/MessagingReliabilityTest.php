<?php

namespace App\Tests;

use App\Entity\Message;
use App\Repository\MessageRepository;
use App\Service\Messaging\MessageDeliveryService;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;

final class MessagingReliabilityTest extends TestCase
{
    public function testDuplicateIdempotencyKeyReturnsExistingMessage(): void
    {
        $messageRepository = $this->createMock(MessageRepository::class);
        $entityManager = $this->createMock(EntityManagerInterface::class);

        $existing = new Message();
        $existing->setBody('cached');
        $existing->setIdempotencyKey('abc-123');

        $messageRepository->expects($this->once())->method('findOneBy')->with(['idempotencyKey' => 'abc-123'])->willReturn($existing);
        $entityManager->expects($this->never())->method('persist');
        $entityManager->expects($this->never())->method('flush');

        $service = new MessageDeliveryService($entityManager, $messageRepository);

        $result = $service->createWithIdempotency(new Message(), 'abc-123');

        $this->assertSame($existing, $result);
    }
}
