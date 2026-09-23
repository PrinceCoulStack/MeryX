<?php

namespace App\Service\Messaging;

use App\Entity\Message;
use App\Entity\MessageReceipt;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;

final class MessageReceiptHandler
{
    public function __construct(private readonly EntityManagerInterface $entityManager)
    {
    }

    public function markDelivered(Message $message, User $recipient): void
    {
        $receipt = $this->findOrCreate($message, $recipient);
        $receipt->setDeliveredAt(new \DateTimeImmutable());
        $receipt->setStatus('delivered');
        $this->entityManager->flush();
    }

    public function markRead(Message $message, User $recipient): void
    {
        $receipt = $this->findOrCreate($message, $recipient);
        $receipt->setReadAt(new \DateTimeImmutable());
        $receipt->setStatus('read');
        $this->entityManager->flush();
    }

    private function findOrCreate(Message $message, User $recipient): MessageReceipt
    {
        foreach ($message->getReceipts() as $receipt) {
            if ($receipt->getUser()?->getId() === $recipient->getId()) {
                return $receipt;
            }
        }

        $receipt = new MessageReceipt();
        $receipt->setMessage($message);
        $receipt->setUser($recipient);
        $receipt->setStatus('sent');
        $message->addReceipt($receipt);
        $this->entityManager->persist($receipt);

        return $receipt;
    }
}
