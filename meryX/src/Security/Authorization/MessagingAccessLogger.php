<?php

namespace App\Security\Authorization;

use Psr\Log\LoggerInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

final class MessagingAccessLogger
{
    public function __construct(
        private readonly LoggerInterface $logger,
        private readonly AuthorizationCheckerInterface $authorizationChecker,
    ) {
    }

    public function auditDeny(string $resource, string $action, object $subject, array $context = []): void
    {
        $this->logger->warning('Messaging access denied', [
            'resource' => $resource,
            'action' => $action,
            'subject' => get_debug_type($subject),
            'metadata' => $context,
            'authorized' => false,
            'timestamp' => (new \DateTimeImmutable())->format(DATE_ATOM),
        ]);
    }
}
