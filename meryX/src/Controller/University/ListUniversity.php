<?php

namespace App\Controller\University;

use App\Repository\UniversityRepository;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\AsController;

#[AsController]
final class ListUniversity
{
    public function __invoke(UniversityRepository $repo): JsonResponse
    {
        $universities = $repo->findAll();
        $payload = [];

        foreach ($universities as $university) {
            $payload[] = [
                'id' => $university->getId(),
                'name' => $university->getName(),
                'email' => $university->getEmail(),
                'status' => $university->getStatus(),
            ];
        }

        return new JsonResponse($payload, 200);
    }
}
