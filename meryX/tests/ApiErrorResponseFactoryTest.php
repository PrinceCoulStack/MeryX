<?php

namespace App\Tests;

use App\Service\Api\ApiErrorResponseFactory;
use PHPUnit\Framework\TestCase;

final class ApiErrorResponseFactoryTest extends TestCase
{
    public function testCreateReturnsConsistentShape(): void
    {
        $factory = new ApiErrorResponseFactory();
        $response = $factory->create(403, 'Forbidden', 'Access denied to this resource.', [
            'errors' => ['role' => ['missing role']],
        ]);

        $this->assertSame(403, $response->getStatusCode());

        $decoded = json_decode((string) $response->getContent(), true);
        $this->assertSame(403, $decoded['status']);
        $this->assertSame('Forbidden', $decoded['message']);
        $this->assertSame('Access denied to this resource.', $decoded['detail']);
        $this->assertSame(['role' => ['missing role']], $decoded['errors']);
    }
}
