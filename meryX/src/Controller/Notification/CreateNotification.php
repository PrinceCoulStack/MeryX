<?php

namespace App\Controller\Notification;

use App\Entity\Notification;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\AsController;

#[AsController]
class CreateNotification extends AbstractController
{
    public function __invoke(EntityManagerInterface $em, Request $request, UserRepository $userRepo)
    {
        $notification = new Notification();
        $data = json_decode($request->getContent(), true);

        if ($request->isMethod('POST')) {
            if (!empty($data['userId'])) {
                $user = $userRepo->find($data['userId']);
                if ($user) {
                    $notification->setUserId($user);
                } else {
                    return $this->json(['message' => 'Invalid user ID'], 400);
                }
            } else {
                return $this->json(['message' => 'User ID is required'], 400);
            }

            $notification->setType($data['type'] ?? null);
            $notification->setTitle($data['title'] ?? null);
            $notification->setBody($data['body'] ?? null);
            $notification->setData($data['data'] ?? []);
            $notification->setReadAt(isset($data['readAt']) ? new \DateTimeImmutable($data['readAt']) : new \DateTimeImmutable());
            $notification->setCreatedAt(new \DateTimeImmutable());

            $em->persist($notification);
            $em->flush();

            return $this->json([
                'message' => 'Notification created successfully',
                'id' => $notification->getId(),
            ], 201);
        }
    }
}
