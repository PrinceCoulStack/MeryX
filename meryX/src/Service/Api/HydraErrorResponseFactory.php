<?php

namespace App\Service\Api;

use Symfony\Component\HttpFoundation\JsonResponse;

class HydraErrorResponseFactory
{
    public function create(string $title, string $description, int $status): JsonResponse
    {
        return new JsonResponse([
            'status' => $status,
            'message' => $title,
            'detail' => $description,
            '@context' => '/api/contexts/Error',
            '@type' => 'hydra:Error',
            'hydra:title' => $title,
            'hydra:description' => $description,
        ], $status);
    }
}
