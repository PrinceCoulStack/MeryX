<?php

namespace App\Service\Candidature;

class CandidatureTransitionValidator
{
    /**
     * @var array<string, array<int, string>>
     */
    private const ALLOWED_TRANSITIONS = [
        'applied' => ['interview', 'rejected'],
        'interview' => ['offer', 'rejected'],
        'offer' => ['accepted', 'rejected'],
        'accepted' => [],
        'rejected' => [],
    ];

    public function assertTransition(string $currentStatus, string $nextStatus): void
    {
        $from = strtolower(trim($currentStatus));
        $to = strtolower(trim($nextStatus));

        if (!array_key_exists($from, self::ALLOWED_TRANSITIONS)) {
            throw new \InvalidArgumentException('Invalid current status value.');
        }

        if (!array_key_exists($to, self::ALLOWED_TRANSITIONS)) {
            throw new \InvalidArgumentException('Invalid target status value.');
        }

        if ($from === $to) {
            return;
        }

        if (!in_array($to, self::ALLOWED_TRANSITIONS[$from], true)) {
            throw new \InvalidArgumentException(sprintf('Invalid status transition from "%s" to "%s".', $from, $to));
        }
    }
}
