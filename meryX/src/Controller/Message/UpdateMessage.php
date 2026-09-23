<?php

namespace App\Controller\Message;

use App\Entity\User;
use App\Repository\ConversationRepository;
use App\Repository\MessageRepository;
use App\Repository\UserRepository;
use App\Security\Voter\ConversationVoter;
use App\Service\Messaging\InboxThreadResponseBuilder;
use App\Service\Messaging\MessageContractNormalizer;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\AsController;

#[AsController]
class UpdateMessage extends AbstractController
{
    public function __invoke(
        EntityManagerInterface $em,
        Request $request,
        MessageRepository $repository,
        ConversationRepository $conversationRepo,
        UserRepository $userRepo,
        MessageContractNormalizer $normalizer,
        InboxThreadResponseBuilder $threadBuilder,
        int $id,
    ) {
        $actor = $this->getUser();
        if (!$actor instanceof User) {
            return $this->json(['message' => 'Unauthorized'], 401);
        }

        $message = $repository->find($id);
        if (!$message) {
            return $this->json(['message' => 'Message not found'], 404);
        }

        $existingConversation = $message->getConversationId();
        if ($existingConversation === null || !$this->isGranted(ConversationVoter::WRITE, $existingConversation)) {
            return $this->json(['message' => 'Forbidden'], 403);
        }

        $data = json_decode((string) $request->getContent(), true);
        if (!is_array($data)) {
            return $this->json(['message' => 'Invalid message payload'], 400);
        }

        $parsed = $normalizer->parseCreatePayload($data);

        if ($parsed['conversationId'] !== null) {
            $conversation = $conversationRepo->find($parsed['conversationId']);
            if (!$conversation) {
                return $this->json(['message' => 'Conversation not found'], 404);
            }

            if (!$this->isGranted(ConversationVoter::WRITE, $conversation)) {
                return $this->json(['message' => 'Forbidden'], 403);
            }

            $message->setConversationId($conversation);
        }

        if ($parsed['senderId'] !== null) {
            $sender = $userRepo->find($parsed['senderId']);
            if (!$sender) {
                return $this->json(['message' => 'User not found'], 404);
            }

            if ($sender->getId() !== $actor->getId()) {
                return $this->json(['message' => 'Forbidden'], 403);
            }

            $message->setSenderId($sender);
        }

        $content = $parsed['content'];
        if ($content !== null && trim($content) !== '') {
            try {
                $message->setBody($content);
            } catch (\Throwable $exception) {
                return $this->json([
                    'message' => 'Message encryption failed',
                    'error' => 'Unable to process encrypted payload',
                ], 422);
            }
        }

        $em->flush();

        return $this->json($threadBuilder->buildMessagePayload($message), 200);
    }
}
