<?php

namespace App\Tests;

use App\Service\Workflow\StatusNormalizer;
use PHPUnit\Framework\TestCase;

final class StatusNormalizerTest extends TestCase
{
    public function testNormalizesReviewAndAccountStatuses(): void
    {
        $normalizer = new StatusNormalizer();

        $this->assertSame('approved', $normalizer->normalizeReviewStatus('APPROVED'));
        $this->assertSame('pending', $normalizer->normalizeReviewStatus('unknown'));

        $this->assertSame('suspended', $normalizer->normalizeAccountStatus('SUSPENDED'));
        $this->assertSame('active', $normalizer->normalizeAccountStatus('???'));

        $this->assertTrue($normalizer->isApprovedStatus('approved'));
        $this->assertTrue($normalizer->isActiveAccount('active'));
        $this->assertFalse($normalizer->isActiveAccount('inactive'));
    }
}
