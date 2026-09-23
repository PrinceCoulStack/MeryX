<?php

namespace App\Controller\ApplicationStatusHistory;

use App\Entity\ApplicationStatusHistory;
use App\Repository\ApplicationRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\AsController;

#[AsController]
class CreateApplicationStatusHistory extends AbstractController
{
    public function __invoke(EntityManagerInterface $em, Request $request, UserRepository $userRepo, ApplicationRepository $applicationRepo)
    {
        $history = new ApplicationStatusHistory();
        $data = json_decode($request->getContent(), true);

        if ($request->isMethod('POST')) {
            if (!empty($data['changedById'])) {
                $user = $userRepo->find($data['changedById']);
                if ($user) {
                    $history->setChangedById($user);
                } else {
                    return $this->json(['message' => 'Invalid user ID'], 400);
                }
            }

            if (!empty($data['applicationId'])) {
                $application = $applicationRepo->find($data['applicationId']);
                if ($application) {
                    $history->setApplicationId($application);
                } else {
                    return $this->json(['message' => 'Invalid application ID'], 400);
                }
            }

            $history->setOldStatus($data['oldStatus'] ?? '');
            $history->setNewStatus($data['newStatus'] ?? '');
            $history->setNote($data['note'] ?? '');
            $history->setCreatedAt(new \DateTimeImmutable());

            $em->persist($history);
            $em->flush();

            return $this->json([
                'message' => 'Application status history created successfully',
                'id' => $history->getId(),
            ], 201);
        }
    }
}
