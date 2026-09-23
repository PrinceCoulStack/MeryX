<?php

namespace App\Service\Workflow;

final class StatusNormalizer
{
    private const REVIEW_STATUSES = ['pending', 'approved', 'rejected'];
    private const ACCOUNT_STATUSES = ['active', 'inactive', 'suspended'];

    public function normalizeReviewStatus(mixed $value, string $default = 'pending'): string
    {
        $normalized = strtolower(trim((string) $value));

        if (in_array($normalized, self::REVIEW_STATUSES, true)) {
            return $normalized;
        }

        return $default;
    }

    public function normalizeAccountStatus(mixed $value, string $default = 'active'): string
    {
        $normalized = strtolower(trim((string) $value));

        if (in_array($normalized, self::ACCOUNT_STATUSES, true)) {
            return $normalized;
        }

        return $default;
    }

    public function isApprovedStatus(string $status): bool
    {
        return strtolower($status) === 'approved';
    }

    public function isActiveAccount(string $status): bool
    {
        return strtolower($status) === 'active';
    }
}
