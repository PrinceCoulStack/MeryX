<?php

namespace App\Controller\Conversation;

use App\Entity\Conversation;
use App\Entity\ConversationParticipant;
use App\Entity\User;
use App\Repository\ConversationParticipantRepository;
use App\Repository\ConversationRepository;
use App\Repository\UserRepository;
use App\Security\Voter\ConversationVoter;
use App\Service\Messaging\InboxThreadResponseBuilder;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\AsController;

#[AsController]
class UpdateConversation extends AbstractController
{
    public function __invoke(
        EntityManagerInterface $em,
        Request $request,
        ConversationRepository $repository,
        ConversationParticipantRepository $participantRepo,
        UserRepository $userRepository,
        InboxThreadResponseBuilder $threadBuilder,
        int $id,
    ): JsonResponse
    {
        $actor = $this->getUser();
        if (!$actor instanceof User) {
            return $this->json(['message' => 'Unauthorized'], 401);
        }

        $conversation = $repository->find($id);
        if (!$conversation) {
            return $this->json(['message' => 'Conversation not found'], 404);
        }

        if (!$this->isGranted(ConversationVoter::WRITE, $conversation)) {
            return $this->json(['message' => 'Forbidden'], 403);
        }

        $data = json_decode((string) $request->getContent(), true);
        if (!is_array($data)) {
            return $this->json(['message' => 'Invalid conversation payload'], 400);
        }

        if (array_key_exists('subject', $data)) {
            $conversation->setSubject((string) $data['subject']);
        }

        if (array_key_exists('type', $data)) {
            $conversation->setType((string) $data['type']);
        }

        if (!empty($data['conversationParticipantId'])) {
            $participantId = $this->resolveEntityId($data['conversationParticipantId']);
            if ($participantId === null) {
                return $this->json(['message' => 'Invalid conversation participant ID'], 400);
            }

            $participant = $participantRepo->find($participantId);
            if (!$participant instanceof ConversationParticipant) {
                return $this->json(['message' => 'Invalid conversation participant ID'], 400);
            }

            $conversation->setConversationParticipantId($participant);
            $conversation->addParticipant($participant);
        }

        $participantIds = $this->resolveIdList([
            $data['conversationParticipantIds'] ?? null,
            $data['participantIds'] ?? null,
        ]);

        foreach ($participantIds as $participantId) {
            $participant = $participantRepo->find($participantId);
            if (!$participant instanceof ConversationParticipant) {
                return $this->json(['message' => 'Invalid conversation participant ID: ' . $participantId], 400);
            }

            $conversation->addParticipant($participant);
        }

        $userIds = $this->resolveIdList([
            $data['participantUserIds'] ?? null,
            $data['userIds'] ?? null,
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

        $conversation->setUpdatedAt(new \DateTimeImmutable());
        $em->flush();

        return $this->json($threadBuilder->buildConversationPayload($conversation, $actor, []), 200);
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
