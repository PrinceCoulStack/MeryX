<?php

namespace App\Service\Messaging;

use App\Entity\Message;

final class MessageContractNormalizer
{
    /**
     * @return array{conversationId:int|null, senderId:int|null, content:string|null}
     */
    public function parseCreatePayload(array $payload): array
    {
        return [
            'conversationId' => $this->normalizeResourceId(
                $payload['conversationId']
                    ?? $payload['conversation_id']
                    ?? $payload['conversation']
                    ?? null
            ),
            'senderId' => $this->normalizeResourceId(
                $payload['senderId']
                    ?? $payload['sender_id']
                    ?? $payload['sender']
                    ?? null
            ),
            'content' => $this->extractContent($payload),
        ];
    }

    /**
     * @return array{
     *   id:int|null,
     *   conversationId:int,
     *   senderId:int,
     *   channelType:string,
     *   encrypted:bool,
     *   contentPreview:string,
     *   createdAt:string,
     *   deliveryState:string,
     *   unreadCountForCurrentUser:int
     * }
     */
    public function normalizeMessage(Message $message): array
    {
        $conversation = $message->getConversationId();
        $sender = $message->getSenderId();

        return [
            'id' => $message->getId(),
            'conversationId' => $conversation?->getId() ?? 0,
            'senderId' => $sender?->getId() ?? 0,
            'channelType' => $conversation?->getType() ?? 'direct',
            'encrypted' => !empty($message->getCiphertext()),
            'contentPreview' => $message->getContentPreview() ?: '[encrypted content]',
            'createdAt' => $message->getCreatedAt()?->format(DATE_ATOM) ?? (new \DateTimeImmutable())->format(DATE_ATOM),
            'deliveryState' => $message->getDeliveryState() ?? 'sent',
            'unreadCountForCurrentUser' => 0,
        ];
    }

    private function normalizeResourceId(mixed $value): ?int
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (is_array($value)) {
            if (array_key_exists('id', $value)) {
                return $this->normalizeResourceId($value['id']);
            }

            if (array_key_exists('@id', $value)) {
                return $this->normalizeResourceId($value['@id']);
            }

            return null;
        }

        if (is_int($value)) {
            return $value > 0 ? $value : null;
        }

        if (is_numeric($value)) {
            $id = (int) $value;

            return $id > 0 ? $id : null;
        }

        if (is_string($value)) {
            $raw = trim($value);
            if ($raw === '' || strtolower($raw) === 'null' || strtolower($raw) === 'undefined') {
                return null;
            }

            if (preg_match('#/(?:api/)?(?:conversations|users)/(?P<id>\d+)#i', $value, $matches) === 1) {
                return (int) $matches['id'];
            }

            if (preg_match('/\d+/', $value, $matches) === 1) {
                return (int) $matches[0];
            }
        }

        return null;
    }

    private function extractContent(array $payload): ?string
    {
        foreach (['content', 'message', 'body'] as $field) {
            if (!array_key_exists($field, $payload)) {
                continue;
            }

            $value = $payload[$field];
            if (is_scalar($value)) {
                return (string) $value;
            }
        }

        return null;
    }
}
