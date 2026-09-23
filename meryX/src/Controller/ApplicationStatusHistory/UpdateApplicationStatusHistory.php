<?php

namespace App\Controller\ApplicationStatusHistory;

use App\Repository\ApplicationRepository;
use App\Repository\ApplicationStatusHistoryRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\AsController;

#[AsController]
class UpdateApplicationStatusHistory extends AbstractController
{
    public function __invoke(EntityManagerInterface $em, Request $request, ApplicationStatusHistoryRepository $repository, UserRepository $userRepo, ApplicationRepository $applicationRepo, int $id)
    {
        $history = $repository->find($id);
        if (!$history) {
            return $this->json(['message' => 'History not found'], 404);
        }

        $data = json_decode($request->getContent(), true);

        if ($request->isMethod('PUT') || $request->isMethod('PATCH') || $request->isMethod('POST')) {
            if (isset($data['changedById'])) {
                $user = $userRepo->find($data['changedById']);
                if ($user) {
                    $history->setChangedById($user);
                } else {
                    return $this->json(['message' => 'Invalid user ID'], 400);
                }
            }

            if (isset($data['applicationId'])) {
                $application = $applicationRepo->find($data['applicationId']);
                if ($application) {
                    $history->setApplicationId($application);
                } else {
                    return $this->json(['message' => 'Invalid application ID'], 400);
                }
            }

            foreach (['oldStatus', 'newStatus', 'note'] as $field) {
                if (array_key_exists($field, $data)) {
                    $setter = 'set' . ucfirst($field);
                    $history->$setter($data[$field]);
                }
            }

            $em->flush();

            return $this->json(['message' => 'Application status history updated successfully'], 200);
        }
    }
}
