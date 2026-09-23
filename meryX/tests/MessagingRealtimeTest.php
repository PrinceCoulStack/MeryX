<?php

namespace App\Tests;

use App\Entity\Conversation;
use App\Entity\ConversationParticipant;
use App\Entity\Message;
use App\Entity\User;
use App\Service\Messaging\MessageReceiptHandler;
use App\Service\Messaging\RealtimePublisher;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;

final class MessagingRealtimeTest extends TestCase
{
    public function testRealtimePublisherDispatchesEvent(): void
    {
        $bus = new class implements MessageBusInterface {
            public array $messages = [];

            public function dispatch(object $message, array $stamps = []): Envelope
            {
                $this->messages[] = $message;

                return new Envelope($message);
            }
        };

        $publisher = new RealtimePublisher($bus, \Symfony\Component\HttpFoundation\RequestStack::class ? new \Symfony\Component\HttpFoundation\RequestStack() : new \Symfony\Component\HttpFoundation\RequestStack());
        $conversation = new Conversation();
        $conversation->setId(1);
        $conversation->setSubject('updates');
        $conversation->setType('university_student');

        $message = new Message();
        $message->setConversationId($conversation);
        $message->setBody('hello');

        $publisher->publish($message);

        $this->assertCount(1, $bus->messages);
    }

    public function testReceiptHandlerTracksDeliveredAndReadStates(): void
    {
        $entityManager = $this->createMock(EntityManagerInterface::class);
        $entityManager->expects($this->once())->method('persist');
        $entityManager->expects($this->exactly(2))->method('flush');

        $handler = new MessageReceiptHandler($entityManager);
        $message = new Message();
        $message->setBody('hi');

        $recipient = (new User())->setEmail('student@example.test');
        $handler->markDelivered($message, $recipient);
        $handler->markRead($message, $recipient);

        $this->assertSame('read', $message->getReceipts()->first()->getStatus());
    }
}
