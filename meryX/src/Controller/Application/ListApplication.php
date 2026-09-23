<?php

namespace App\Controller\Application;

use App\Repository\ApplicationRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Serializer\SerializerInterface;

#[AsController]
class ListApplication extends AbstractController
{
    public function __invoke(ApplicationRepository $repository, SerializerInterface $serializer): JsonResponse
    {
        $items = $repository->findAll();
        $json = $serializer->serialize($items, 'json', [
            'groups' => ['application:read'],
        ]);

        return new JsonResponse($json, 200, [
            'Content-Type' => 'application/json',
        ]);
    }
}
