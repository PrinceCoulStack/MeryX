<?php

namespace App\Controller\Marks;

use App\Repository\MarksRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Serializer\SerializerInterface;

#[AsController]
class ListMarks extends AbstractController
{
    public function __invoke(MarksRepository $repository, SerializerInterface $serializer): JsonResponse
    {
        $items = $repository->findAll();
        $json = $serializer->serialize($items, 'json', [
            'groups' => ['marks:read'],
        ]);

        return new JsonResponse($json, 200, [
            'Content-Type' => 'application/json',
        ]);
    }
}
