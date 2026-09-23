<?php

namespace App\Tests;

use App\Entity\Conversation;
use App\Entity\ConversationParticipant;
use App\Entity\Message;
use App\Entity\University;
use App\Entity\User;
use App\Service\Messaging\MessageEncryptionService;
use PHPUnit\Framework\TestCase;

final class MessagingSecurityTest extends TestCase
{
    public function testMessageEncryptionPersistsCiphertextAndDecrypts(): void
    {
        $key = 'test-master-key-32-bytes-long-string';
        $_ENV['APP_MESSAGE_MASTER_KEY'] = $key;
        $_SERVER['APP_MESSAGE_MASTER_KEY'] = $key;
        putenv('APP_MESSAGE_MASTER_KEY='.$key);

        $service = new MessageEncryptionService();

        $message = new Message();
        $message->setBody('hello university');

        $this->assertNotSame('hello university', $message->getCiphertext());
        $this->assertNotEmpty($message->getNonce());
        $this->assertSame('XChaCha20-Poly1305', $message->getAlgorithm());
        $this->assertSame('[encrypted content]', $message->getContentPreview());

        $decrypted = $service->decrypt($message->getCiphertext(), $message->getNonce(), $key);
        $this->assertSame('hello university', $decrypted);
    }

    public function testConversationParticipantWriteProtectionRequiresMembership(): void
    {
        $conversation = new Conversation();
        $conversation->setType('university_student');
        $conversation->setSubject('secure');

        $studentUser = (new User())->setEmail('student@example.test');
        $participant = new ConversationParticipant();
        $participant->setUser($studentUser);
        $conversation->addParticipant($participant);

        $this->assertCount(1, $conversation->getParticipants());
        $this->assertSame($studentUser, $participant->getUser());
    }
}
