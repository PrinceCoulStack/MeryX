<?php

namespace App\Tests;

use App\Entity\Conversation;
use App\Entity\Message;
use App\Entity\User;
use App\Security\Voter\ConversationVoter;
use App\Repository\CompanyRepository;
use App\Repository\PartnershipRepository;
use App\Repository\StudentProfileRepository;
use App\Repository\UniversityRepository;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

final class MessagingAuthorizationTest extends TestCase
{
    public function testUnauthorizedReadIsDeniedByVoter(): void
    {
        $conversation = new Conversation();
        $conversation->setType('university_student');
        $conversation->setSubject('private');

        $actor = (new User())->setEmail('intruder@example.test');
        $actor->setStatus('active');

        $voter = new ConversationVoter(
            $this->createMock(PartnershipRepository::class),
            $this->createMock(UniversityRepository::class),
            $this->createMock(StudentProfileRepository::class),
            $this->createMock(CompanyRepository::class),
        );

        $token = new UsernamePasswordToken($actor, 'main', $actor->getRoles());
        $this->assertFalse($voter->vote($token, $conversation, [ConversationVoter::READ]) === 1);
    }

    public function testValidParticipantReadIsAllowed(): void
    {
        $conversation = new Conversation();
        $conversation->setType('university_student');
        $conversation->setSubject('allowed');

        $actor = (new User())->setEmail('student@example.test');
        $participant = new \App\Entity\ConversationParticipant();
        $participant->setUser($actor);
        $conversation->addParticipant($participant);

        $voter = new ConversationVoter(
            $this->createMock(PartnershipRepository::class),
            $this->createMock(UniversityRepository::class),
            $this->createMock(StudentProfileRepository::class),
            $this->createMock(CompanyRepository::class),
        );

        $token = new UsernamePasswordToken($actor, 'main', $actor->getRoles());
        $this->assertTrue($voter->vote($token, $conversation, [ConversationVoter::READ]) === 1);
    }
}
