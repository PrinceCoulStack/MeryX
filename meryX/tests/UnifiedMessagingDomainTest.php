<?php

namespace App\Tests;

use App\Entity\Conversation;
use App\Entity\ConversationParticipant;
use App\Entity\Message;
use App\Entity\MessageReceipt;
use App\Entity\User;
use PHPUnit\Framework\TestCase;

final class UnifiedMessagingDomainTest extends TestCase
{
    public function testConversationTracksMultipleParticipantsAndUnreadState(): void
    {
        $conversation = new Conversation();
        $conversation->setSubject('Admissions follow-up');
		$conversation->setType('university_student');

        $participant = new ConversationParticipant();
        $participant->setUser((new User())->setEmail('student@example.test'));
        $conversation->addParticipant($participant);

        $this->assertCount(1, $conversation->getParticipants());
        $this->assertSame($conversation, $participant->getConversation());
        $this->assertSame('Admissions follow-up', $conversation->getSubject());

        $participant->setLastReadAt(new \DateTimeImmutable('2025-01-20 10:00:00'));
        $this->assertInstanceOf(\DateTimeImmutable::class, $participant->getLastReadAt());
    }

    public function testMessagesCreateReceiptsForReadTracking(): void
    {
        $message = new Message();
        $message->setBody('Welcome to the university portal');
        $message->setSenderId((new User())->setEmail('university@example.test'));

        $receipt = new MessageReceipt();
        $receipt->setMessage($message);
        $receipt->setUser((new User())->setEmail('student@example.test'));
        $receipt->setReadAt(new \DateTimeImmutable('2025-01-20 10:05:00'));

        $this->assertSame($message, $receipt->getMessage());
        $this->assertSame('student@example.test', $receipt->getUser()?->getEmail());
        $this->assertInstanceOf(\DateTimeImmutable::class, $receipt->getReadAt());
    }
}
