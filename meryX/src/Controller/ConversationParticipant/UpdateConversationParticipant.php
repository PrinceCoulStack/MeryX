<?php

namespace App\Controller\ConversationParticipant;

use App\Repository\ConversationParticipantRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\AsController;

#[AsController]
class UpdateConversationParticipant extends AbstractController
{
    public function __invoke(EntityManagerInterface $em, Request $request, ConversationParticipantRepository $repository, UserRepository $userRepo, int $id)
    {
        $participant = $repository->find($id);
        if (!$participant) {
            return $this->json(['message' => 'Conversation participant not found'], 404);
        }

        $data = json_decode($request->getContent(), true);

        if ($request->isMethod('PUT') || $request->isMethod('PATCH') || $request->isMethod('POST')) {
            if (!empty($data['user'])) {
                $user = $userRepo->find($data['user']);
                if ($user) {
                    $participant->setUser($user);
                } else {
                    return $this->json(['message' => 'Invalid user ID'], 400);
                }
            }

            if (isset($data['joinedAt'])) {
                $participant->setJoinedAt(new \DateTimeImmutable($data['joinedAt']));
            }

            if (isset($data['lastReadAt'])) {
                $participant->setLastReadAt(new \DateTimeImmutable($data['lastReadAt']));
            }

            $em->flush();

            return $this->json(['message' => 'Conversation participant updated successfully'], 200);
        }
    }
}
