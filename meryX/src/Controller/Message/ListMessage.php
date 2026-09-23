<?php

namespace App\Controller\Message;

use App\Entity\User;
use App\Repository\ConversationRepository;
use App\Repository\MessageRepository;
use App\Service\Messaging\InboxThreadResponseBuilder;
use App\Security\Voter\ConversationVoter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\AsController;

#[AsController]
class ListMessage extends AbstractController
{
    public function __invoke(
        Request $request,
        MessageRepository $repository,
        ConversationRepository $conversationRepository,
        InboxThreadResponseBuilder $threadBuilder,
    ): JsonResponse
    {
        $actor = $this->getUser();
        if (!$actor instanceof User) {
            return $this->json(['message' => 'Unauthorized'], 401);
        }

        $conversationId = $this->resolveEntityId($request->attributes->get('id') ?? $request->query->get('conversationId'));
        $page = max(1, (int) $request->query->get('page', 1));
        $limit = min(200, max(1, (int) $request->query->get('limit', 50)));
        $hydra = filter_var((string) $request->query->get('hydra', 'false'), FILTER_VALIDATE_BOOL);

        if ($conversationId !== null) {
            $conversation = $conversationRepository->find($conversationId);
            if ($conversation === null) {
                return $this->json(['message' => 'Conversation not found'], 404);
            }

            if (!$this->isGranted(ConversationVoter::READ, $conversation)) {
                return $this->json(['message' => 'Forbidden'], 403);
            }

            $items = $repository->findByConversationOrdered($conversationId, $page, $limit);

            $items = array_map(
                fn ($message) => $threadBuilder->buildMessagePayload($message),
                $items
            );

            if (!$hydra) {
                return $this->json($items);
            }

            return $this->json([
                '@context' => '/api/contexts/Message',
                '@id' => '/api/conversations/' . $conversationId . '/messages',
                '@type' => 'hydra:Collection',
                'hydra:member' => $items,
                'hydra:totalItems' => count($items),
            ]);
        }

        $items = $repository->findAll();
        $visible = [];
        foreach ($items as $message) {
            $conversation = $message->getConversationId();
            if ($conversation === null || !$this->isGranted(ConversationVoter::READ, $conversation)) {
                continue;
            }

            $visible[] = $threadBuilder->buildMessagePayload($message);
        }

        if (!$hydra) {
            return $this->json($visible);
        }

        return $this->json([
            '@context' => '/api/contexts/Message',
            '@id' => '/api/messages',
            '@type' => 'hydra:Collection',
            'hydra:member' => $visible,
            'hydra:totalItems' => count($visible),
        ]);
    }

    private function resolveEntityId(mixed $value): ?int
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (is_array($value) && isset($value['id'])) {
            $value = $value['id'];
        } elseif (is_array($value) && isset($value['@id'])) {
            $value = $value['@id'];
        }

        if (is_int($value)) {
            return $value > 0 ? $value : null;
        }

        if (is_string($value) && preg_match('/\/(\d+)$/', $value, $matches) === 1) {
            return (int) $matches[1];
        }

        if (is_scalar($value)) {
            $raw = trim((string) $value);
            if ($raw === '' || $raw === 'null' || $raw === 'undefined') {
                return null;
            }

            $id = (int) $raw;

            return $id > 0 ? $id : null;
        }

        return null;
    }
}
