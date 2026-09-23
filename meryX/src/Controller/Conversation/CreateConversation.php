<?php

namespace App\Controller\Conversation;

use App\Entity\Conversation;
use App\Entity\ConversationParticipant;
use App\Entity\User;
use App\Repository\ConversationParticipantRepository;
use App\Repository\UserRepository;
use App\Service\Messaging\InboxThreadResponseBuilder;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\AsController;

#[AsController]
class CreateConversation extends AbstractController
{
    public function __invoke(
        EntityManagerInterface $em,
        Request $request,
        ConversationParticipantRepository $participantRepo,
        UserRepository $userRepository,
        InboxThreadResponseBuilder $threadBuilder,
    ): JsonResponse
    {
        $actor = $this->getUser();
        if (!$actor instanceof User) {
            return $this->json(['message' => 'Unauthorized'], 401);
        }

        $data = json_decode((string) $request->getContent(), true);
        if (!is_array($data)) {
            return $this->json(['message' => 'Invalid conversation payload'], 400);
        }

        $conversation = new Conversation();
        $conversation->setSubject((string) ($data['subject'] ?? 'Conversation'));
        $conversation->setType((string) ($data['type'] ?? 'direct'));
        $conversation->setCreatedAt(new \DateTimeImmutable());
        $conversation->setUpdatedAt(new \DateTimeImmutable());

        $participantIds = $this->resolveIdList([
            $data['conversationParticipantId'] ?? null,
            $data['conversationParticipantIds'] ?? null,
            $data['participantIds'] ?? null,
        ]);

        foreach ($participantIds as $participantId) {
            $participant = $participantRepo->find($participantId);
            if (!$participant instanceof ConversationParticipant) {
                return $this->json(['message' => 'Invalid conversation participant ID: ' . $participantId], 400);
            }

            $conversation->addParticipant($participant);
            if ($conversation->getConversationParticipantId() === null) {
                $conversation->setConversationParticipantId($participant);
            }
        }

        $userIds = $this->resolveIdList([
            $data['participantUserIds'] ?? null,
            $data['userIds'] ?? null,
            $data['participantIds'] ?? null,
        ]);

        foreach ($userIds as $userId) {
            $user = $userRepository->find($userId);
            if (!$user instanceof User) {
                return $this->json(['message' => 'Invalid user ID: ' . $userId], 400);
            }

            if ($this->hasUserParticipant($conversation, $user->getId())) {
                continue;
            }

            $participant = new ConversationParticipant();
            $participant->setUser($user);
            $participant->setJoinedAt(new \DateTimeImmutable());
            $participant->setLastReadAt(new \DateTimeImmutable());
            $conversation->addParticipant($participant);
            if ($conversation->getConversationParticipantId() === null) {
                $conversation->setConversationParticipantId($participant);
            }
        }

        if (!$this->hasUserParticipant($conversation, $actor->getId())) {
            $actorParticipant = new ConversationParticipant();
            $actorParticipant->setUser($actor);
            $actorParticipant->setJoinedAt(new \DateTimeImmutable());
            $actorParticipant->setLastReadAt(new \DateTimeImmutable());
            $conversation->addParticipant($actorParticipant);
            if ($conversation->getConversationParticipantId() === null) {
                $conversation->setConversationParticipantId($actorParticipant);
            }
        }

        $em->persist($conversation);
        $em->flush();

        return $this->json($threadBuilder->buildConversationPayload($conversation, $actor, []), 201);
    }

    /**
     * @param array<int, mixed> $sources
     * @return array<int, int>
     */
    private function resolveIdList(array $sources): array
    {
        $ids = [];
        foreach ($sources as $source) {
            if ($source === null) {
                continue;
            }

            if (is_array($source)) {
                foreach ($source as $item) {
                    $id = $this->resolveEntityId($item);
                    if ($id !== null) {
                        $ids[$id] = $id;
                    }
                }

                continue;
            }

            $id = $this->resolveEntityId($source);
            if ($id !== null) {
                $ids[$id] = $id;
            }
        }

        return array_values($ids);
    }

    private function hasUserParticipant(Conversation $conversation, ?int $userId): bool
    {
        if ($userId === null) {
            return false;
        }

        foreach ($conversation->getParticipants() as $participant) {
            if ($participant->getUser()?->getId() === $userId) {
                return true;
            }
        }

        return false;
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
