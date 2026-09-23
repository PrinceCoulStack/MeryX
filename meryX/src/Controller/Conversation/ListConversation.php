<?php

namespace App\Controller\Conversation;

use App\Entity\User;
use App\Repository\MessageRepository;
use App\Repository\ConversationRepository;
use App\Security\Voter\ConversationVoter;
use App\Service\Messaging\InboxThreadResponseBuilder;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\AsController;

#[AsController]
class ListConversation extends AbstractController
{
    public function __invoke(
        Request $request,
        ConversationRepository $repository,
        MessageRepository $messageRepository,
        InboxThreadResponseBuilder $threadBuilder,
    ): JsonResponse
    {
        $actor = $this->getUser();
        if (!$actor instanceof User) {
            return $this->json(['message' => 'Unauthorized'], 401);
        }

        $conversationId = $this->resolveEntityId($request->attributes->get('id') ?? $request->query->get('conversationId'));
        $withMessages = filter_var((string) $request->query->get('withMessages', 'false'), FILTER_VALIDATE_BOOL);
        $hydra = filter_var((string) $request->query->get('hydra', 'false'), FILTER_VALIDATE_BOOL);
        $page = max(1, (int) $request->query->get('page', 1));
        $limit = min(200, max(1, (int) $request->query->get('limit', 50)));

        if ($conversationId !== null) {
            $conversation = $repository->find($conversationId);
            if ($conversation === null) {
                return $this->json(['message' => 'Conversation not found'], 404);
            }

            if (!$this->isGranted(ConversationVoter::READ, $conversation)) {
                return $this->json(['message' => 'Forbidden'], 403);
            }

            $messages = $withMessages ? $messageRepository->findByConversationOrdered($conversation->getId(), $page, $limit) : [];

            return $this->json($threadBuilder->buildConversationPayload($conversation, $actor, $messages));
        }

        $conversations = $repository->findForUser($actor);
        $items = [];
        foreach ($conversations as $conversation) {
            if (!$this->isGranted(ConversationVoter::READ, $conversation)) {
                continue;
            }

            $messages = $withMessages ? $messageRepository->findByConversationOrdered($conversation->getId(), $page, $limit) : [];
            $items[] = $threadBuilder->buildConversationPayload($conversation, $actor, $messages);
        }

        if (!$hydra) {
            return $this->json($items);
        }

        return $this->json([
            '@context' => '/api/contexts/Conversation',
            '@id' => '/api/conversations',
            '@type' => 'hydra:Collection',
            'hydra:member' => $items,
            'hydra:totalItems' => count($items),
        ]);
    }

    private function resolveEntityId(mixed $value): ?int
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (is_array($value)) {
            if (isset($value['id'])) {
                $value = $value['id'];
            } elseif (isset($value['@id'])) {
                $value = $value['@id'];
            }
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
