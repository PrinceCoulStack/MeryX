<?php

namespace App\Controller\AuditLog;

use App\Repository\AuditLogRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Serializer\SerializerInterface;

#[AsController]
class ListAuditLog extends AbstractController
{
    public function __invoke(AuditLogRepository $repository, SerializerInterface $serializer): JsonResponse
    {
        $items = $repository->findAll();
        $json = $serializer->serialize($items, 'json', [
            'groups' => ['auditLog:read'],
        ]);

        return new JsonResponse($json, 200, [
            'Content-Type' => 'application/json',
        ]);
    }
}
