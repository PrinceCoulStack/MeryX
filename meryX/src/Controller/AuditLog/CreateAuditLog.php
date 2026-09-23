<?php

namespace App\Controller\AuditLog;

use App\Entity\AuditLog;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\AsController;

#[AsController]
class CreateAuditLog extends AbstractController
{
    public function __invoke(EntityManagerInterface $em, Request $request, UserRepository $userRepo)
    {
        $auditLog = new AuditLog();
        $data = json_decode($request->getContent(), true);

        if ($request->isMethod('POST')) {
            if (!empty($data['actorId'])) {
                $actor = $userRepo->find($data['actorId']);
                if ($actor) {
                    $auditLog->setActorId($actor);
                } else {
                    return $this->json(['message' => 'Invalid actor ID'], 400);
                }
            }

            $auditLog->setAction($data['action'] ?? null);
            $auditLog->setEntityType($data['entityType'] ?? null);
            $auditLog->setEntityId($data['entityId'] ?? null);
            $auditLog->setPayload($data['payload'] ?? []);
            $auditLog->setCreatedAt(new \DateTimeImmutable());
            $auditLog->setIpAddress($data['ipAddress'] ?? '');
            $auditLog->setUserAgent($data['userAgent'] ?? '');

            $em->persist($auditLog);
            $em->flush();

            return $this->json([
                'message' => 'Audit log created successfully',
                'id' => $auditLog->getId(),
            ], 201);
        }
    }
}
