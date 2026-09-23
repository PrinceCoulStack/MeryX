<?php

namespace App\Controller\Notification;

use App\Repository\NotificationRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\AsController;

#[AsController]
class UpdateNotification extends AbstractController
{
    public function __invoke(EntityManagerInterface $em, Request $request, NotificationRepository $repository, UserRepository $userRepo, int $id)
    {
        $notification = $repository->find($id);
        if (!$notification) {
            return $this->json(['message' => 'Notification not found'], 404);
        }

        $data = json_decode($request->getContent(), true);

        if ($request->isMethod('PUT') || $request->isMethod('PATCH') || $request->isMethod('POST')) {
            if (!empty($data['userId'])) {
                $user = $userRepo->find($data['userId']);
                if ($user) {
                    $notification->setUserId($user);
                } else {
                    return $this->json(['message' => 'Invalid user ID'], 400);
                }
            }

            foreach (['type', 'title', 'body'] as $field) {
                if (isset($data[$field])) {
                    $setter = 'set' . ucfirst($field);
                    $notification->$setter($data[$field]);
                }
            }

            if (isset($data['data'])) {
                $notification->setData($data['data']);
            }

            if (isset($data['readAt'])) {
                $notification->setReadAt(new \DateTimeImmutable($data['readAt']));
            }

            $em->flush();

            return $this->json(['message' => 'Notification updated successfully'], 200);
        }
    }
}
