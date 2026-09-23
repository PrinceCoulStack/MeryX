<?php

namespace App\Service\Messaging;

use App\Entity\Message;
use App\Repository\MessageRepository;
use Doctrine\ORM\EntityManagerInterface;

final class MessageDeliveryService
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly MessageRepository $messageRepository,
    ) {
    }

    public function createWithIdempotency(Message $message, ?string $idempotencyKey = null): Message
    {
        if ($idempotencyKey !== null && $idempotencyKey !== '') {
            $existing = $this->messageRepository->findOneBy(['idempotencyKey' => $idempotencyKey]);
            if ($existing instanceof Message) {
                return $existing;
            }
        }

        $message->setIdempotencyKey($idempotencyKey);
        $message->setDeliveryState('sent');
        $this->entityManager->persist($message);
        $this->entityManager->flush();

        return $message;
    }
}
