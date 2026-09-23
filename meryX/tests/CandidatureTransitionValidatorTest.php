<?php

namespace App\Tests;

use App\Service\Candidature\CandidatureTransitionValidator;
use PHPUnit\Framework\TestCase;

final class CandidatureTransitionValidatorTest extends TestCase
{
    public function testValidTransitionsAreAccepted(): void
    {
        $validator = new CandidatureTransitionValidator();

        $validator->assertTransition('applied', 'interview');
        $validator->assertTransition('interview', 'offer');
        $validator->assertTransition('offer', 'accepted');

        $this->assertTrue(true);
    }

    public function testInvalidTransitionIsRejected(): void
    {
        $validator = new CandidatureTransitionValidator();

        $this->expectException(\InvalidArgumentException::class);
        $validator->assertTransition('applied', 'accepted');
    }

    public function testTerminalStatusIsProtected(): void
    {
        $validator = new CandidatureTransitionValidator();

        $this->expectException(\InvalidArgumentException::class);
        $validator->assertTransition('accepted', 'rejected');
    }
}
