<?php

namespace App\Service\Api;

use Symfony\Component\HttpFoundation\JsonResponse;

final class ApiErrorResponseFactory
{
    /**
     * @param array<string, mixed> $extra
     */
    public function create(int $status, string $message, ?string $detail = null, array $extra = []): JsonResponse
    {
        $payload = [
            'status' => $status,
            'message' => $message,
            'detail' => $detail ?? $message,
        ];

        foreach ($extra as $key => $value) {
            $payload[$key] = $value;
        }

        return new JsonResponse($payload, $status);
    }
}
