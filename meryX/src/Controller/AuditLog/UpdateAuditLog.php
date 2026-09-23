<?php

namespace App\Controller\AuditLog;

use App\Repository\AuditLogRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\AsController;

#[AsController]
class UpdateAuditLog extends AbstractController
{
    public function __invoke(EntityManagerInterface $em, Request $request, AuditLogRepository $repository, UserRepository $userRepo, int $id)
    {
        $auditLog = $repository->find($id);
        if (!$auditLog) {
            return $this->json(['message' => 'Audit log not found'], 404);
        }

        $data = json_decode($request->getContent(), true);

        if ($request->isMethod('PUT') || $request->isMethod('PATCH') || $request->isMethod('POST')) {
            if (!empty($data['actorId'])) {
                $actor = $userRepo->find($data['actorId']);
                if ($actor) {
                    $auditLog->setActorId($actor);
                } else {
                    return $this->json(['message' => 'Invalid actor ID'], 400);
                }
            }

            foreach (['action', 'entityType', 'entityId', 'ipAddress', 'userAgent'] as $field) {
                if (isset($data[$field])) {
                    $setter = 'set' . ucfirst($field);
                    $auditLog->$setter($data[$field]);
                }
            }

            if (isset($data['payload'])) {
                $auditLog->setPayload($data['payload']);
            }

            $em->flush();

            return $this->json(['message' => 'Audit log updated successfully'], 200);
        }
    }
}
