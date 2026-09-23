<?php

namespace App\Service\Messaging;

use App\Entity\Message;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;

final class RealtimePublisher
{
    public function __construct(
        private readonly MessageBusInterface $messageBus,
        private readonly RequestStack $requestStack,
    ) {
    }

    public function publish(Message $message): void
    {
        $topic = sprintf('/conversations/%d', $message->getConversationId()?->getId() ?? 0);
        $this->messageBus->dispatch(new RealtimeMessageEvent($message, $topic));
    }
}

final class RealtimeMessageEvent
{
    public function __construct(
        public readonly Message $message,
        public readonly string $topic,
    ) {
    }
}
