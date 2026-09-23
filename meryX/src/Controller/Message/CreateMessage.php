<?php

namespace App\Controller\Message;

use App\Entity\Message;
use App\Entity\User;
use App\Repository\ConversationRepository;
use App\Repository\UserRepository;
use App\Security\Voter\ConversationVoter;
use App\Service\Api\ApiErrorResponseFactory;
use App\Service\Messaging\InboxThreadResponseBuilder;
use App\Service\Messaging\MessageContractNormalizer;
use App\Service\Messaging\MessageDeliveryService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\AsController;

#[AsController]
class CreateMessage extends AbstractController
{
    public function __invoke(
        EntityManagerInterface $em,
        Request $request,
        ConversationRepository $conversationRepo,
        UserRepository $userRepo,
        MessageDeliveryService $deliveryService,
        MessageContractNormalizer $normalizer,
        InboxThreadResponseBuilder $threadBuilder,
        ApiErrorResponseFactory $errorResponseFactory,
    ): JsonResponse {
        $actor = $this->getUser();
        if (!$actor instanceof User) {
            return $errorResponseFactory->create(401, 'Unauthorized', 'Missing or invalid JWT token.');
        }

        $data = json_decode((string) $request->getContent(), true);
        if (!is_array($data)) {
            return $this->json(['message' => 'Invalid message payload'], 400);
        }

        $parsed = $normalizer->parseCreatePayload($data);
        $conversationId = $parsed['conversationId'];
        $senderId = $parsed['senderId'];
        $content = $parsed['content'];

        if ($conversationId === null) {
            return $errorResponseFactory->create(400, 'Bad Request', 'conversationId is required.');
        }

        $senderId = $senderId ?? $actor->getId();

        if ($content === null || trim($content) === '') {
            return $errorResponseFactory->create(400, 'Bad Request', 'content is required.');
        }

        $conversation = $conversationRepo->find($conversationId);
        if (!$conversation) {
            return $errorResponseFactory->create(404, 'Not Found', 'Conversation not found.');
        }

        if (!$this->isGranted(ConversationVoter::WRITE, $conversation)) {
            return $errorResponseFactory->create(403, 'Forbidden', 'You are not allowed to write in this conversation.');
        }

        $sender = $userRepo->find($senderId);
        if (!$sender) {
            return $errorResponseFactory->create(404, 'Not Found', 'User not found.');
        }

        if ($actor->getId() !== $sender->getId()) {
            return $errorResponseFactory->create(403, 'Forbidden', 'You can only send messages as yourself.');
        }

        $isParticipant = false;
        foreach ($conversation->getParticipants() as $participant) {
            if ($participant->getUser()?->getId() === $sender->getId()) {
                $isParticipant = true;
                break;
            }
        }

        if (!$isParticipant) {
            return $errorResponseFactory->create(403, 'Forbidden', 'Sender is not a participant in the conversation.');
        }

        $idempotencyKey = $data['idempotencyKey'] ?? $request->headers->get('X-Idempotency-Key');
        $message = new Message();
        $message->setConversationId($conversation);
        $message->setSenderId($sender);

        try {
            $message->setBody($content);
        } catch (\Throwable $exception) {
            return $this->json([
                'message' => 'Message encryption failed',
                'error' => 'Unable to process encrypted payload',
            ], 422);
        }

        $message->setCreatedAt(new \DateTimeImmutable());
        $message->setEditedAt(new \DateTimeImmutable());
        $message->setIsDeleted(false);
        $message->setDeletedAt(new \DateTimeImmutable());

        try {
            $message = $deliveryService->createWithIdempotency($message, $idempotencyKey !== null && $idempotencyKey !== '' ? (string) $idempotencyKey : null);
        } catch (\Throwable $exception) {
            return $this->json([
                'message' => 'Message encryption failed',
                'error' => 'Unable to process encrypted payload',
            ], 422);
        }

        if ($idempotencyKey !== null && $idempotencyKey !== '' && $message->getIdempotencyKey() === (string) $idempotencyKey && $message->getId() !== null) {
            // delivered via service, normalized payload below
        }

        return $this->json($threadBuilder->buildMessagePayload($message), 201);
    }
}
