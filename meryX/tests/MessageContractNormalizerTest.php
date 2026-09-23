<?php

namespace App\Tests;

use App\Entity\Conversation;
use App\Entity\Message;
use App\Entity\User;
use App\Service\Messaging\MessageContractNormalizer;
use PHPUnit\Framework\TestCase;

final class MessageContractNormalizerTest extends TestCase
{
    public function testPayloadParsingAcceptsIriAndAlternateContentFields(): void
    {
        $normalizer = new MessageContractNormalizer();

        $payload = [
            'conversationId' => '/api/conversations/42',
            'senderId' => '/api/users/7',
            'message' => 'hello from the frontend',
        ];

        $this->assertSame([
            'conversationId' => 42,
            'senderId' => 7,
            'content' => 'hello from the frontend',
        ], $normalizer->parseCreatePayload($payload));

        $this->assertSame([
            'conversationId' => 99,
            'senderId' => 3,
            'content' => 'second body',
        ], $normalizer->parseCreatePayload([
            'conversationId' => '99',
            'senderId' => '3',
            'body' => 'second body',
        ]));

        $this->assertSame([
            'conversationId' => 5,
            'senderId' => 7,
            'content' => 'object forms',
        ], $normalizer->parseCreatePayload([
            'conversation' => ['@id' => '/api/conversations/5'],
            'sender' => ['id' => '7'],
            'content' => 'object forms',
        ]));

        $this->assertSame([
            'conversationId' => null,
            'senderId' => null,
            'content' => 'still valid',
        ], $normalizer->parseCreatePayload([
            'conversationId' => 'undefined',
            'senderId' => 'null',
            'content' => 'still valid',
        ]));
    }

    public function testCanonicalResponseSchemaContainsRequiredFields(): void
    {
        $conversation = new Conversation();
        $conversation->setId(12);
        $conversation->setType('university_student');

        $sender = (new User())->setEmail('student@example.test');
        $reflection = new \ReflectionProperty(User::class, 'id');
        $reflection->setAccessible(true);
        $reflection->setValue($sender, 17);

        $message = new Message();
        $message->setConversationId($conversation);
        $message->setSenderId($sender);
        $message->setBody('hello there');
        $message->setCreatedAt(new \DateTimeImmutable('2026-01-02T03:04:05+00:00'));
        $message->setDeliveryState('sent');

        $normalizer = new MessageContractNormalizer();
        $response = $normalizer->normalizeMessage($message);

        $this->assertSame(['id', 'conversationId', 'senderId', 'channelType', 'encrypted', 'contentPreview', 'createdAt', 'deliveryState', 'unreadCountForCurrentUser'], array_keys($response));
        $this->assertSame(12, $response['conversationId']);
        $this->assertSame(17, $response['senderId']);
        $this->assertSame('university_student', $response['channelType']);
        $this->assertTrue($response['encrypted']);
        $this->assertSame('[encrypted content]', $response['contentPreview']);
        $this->assertSame('2026-01-02T03:04:05+00:00', $response['createdAt']);
        $this->assertSame('sent', $response['deliveryState']);
        $this->assertSame(0, $response['unreadCountForCurrentUser']);
    }
}
