<?php

namespace App\Controller\ConversationParticipant;

use App\Entity\ConversationParticipant;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\AsController;

#[AsController]
class CreateConversationParticipant extends AbstractController
{
    public function __invoke(EntityManagerInterface $em, Request $request, UserRepository $userRepo)
    {
        $participant = new ConversationParticipant();
        $data = json_decode($request->getContent(), true);

        if ($request->isMethod('POST')) {
            if (!empty($data['user'])) {
                $user = $userRepo->find($data['user']);
                if ($user) {
                    $participant->setUser($user);
                } else {
                    return $this->json(['message' => 'Invalid user ID'], 400);
                }
            } else {
                return $this->json(['message' => 'User ID is required'], 400);
            }

            $participant->setJoinedAt(new \DateTimeImmutable($data['joinedAt'] ?? 'now'));
            $participant->setLastReadAt(new \DateTimeImmutable($data['lastReadAt'] ?? 'now'));

            $em->persist($participant);
            $em->flush();

            return $this->json([
                'message' => 'Conversation participant created successfully',
                'id' => $participant->getId(),
            ], 201);
        }
    }
}
