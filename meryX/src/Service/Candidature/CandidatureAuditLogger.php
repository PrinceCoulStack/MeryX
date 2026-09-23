<?php

namespace App\Service\Candidature;

use App\Entity\AuditLog;
use App\Entity\Candidature;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;

class CandidatureAuditLogger
{
    public function __construct(private readonly EntityManagerInterface $entityManager)
    {
    }

    public function logStatusChange(User $actor, Candidature $candidature, string $oldStatus, string $newStatus, Request $request): void
    {
        $log = new AuditLog();
        $log->setActorId($actor);
        $log->setAction('candidature.status.changed');
        $log->setEntityType('candidature');
        $log->setEntityId((string) $candidature->getId());
        $log->setPayload([
            'oldStatus' => $oldStatus,
            'newStatus' => $newStatus,
            'studentId' => $candidature->getStudent()?->getId(),
            'opportunityId' => $candidature->getOpportunity()?->getId(),
        ]);
        $log->setCreatedAt(new \DateTimeImmutable('now', new \DateTimeZone('UTC')));
        $log->setIpAddress((string) ($request->getClientIp() ?? 'unknown'));
        $log->setUserAgent((string) ($request->headers->get('User-Agent') ?? 'unknown'));

        $this->entityManager->persist($log);
    }
}
