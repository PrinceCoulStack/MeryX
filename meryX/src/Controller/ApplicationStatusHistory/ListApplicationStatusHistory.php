<?php

namespace App\Controller\ApplicationStatusHistory;

use App\Repository\ApplicationStatusHistoryRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Serializer\SerializerInterface;

#[AsController]
class ListApplicationStatusHistory extends AbstractController
{
    public function __invoke(ApplicationStatusHistoryRepository $repository, SerializerInterface $serializer): JsonResponse
    {
        $items = $repository->findAll();
        $json = $serializer->serialize($items, 'json', [
            'groups' => ['applicationStatusHistory:read'],
        ]);

        return new JsonResponse($json, 200, [
            'Content-Type' => 'application/json',
        ]);
    }
}
