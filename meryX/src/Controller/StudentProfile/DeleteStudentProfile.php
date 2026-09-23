<?php

namespace App\Controller\StudentProfile;

use App\Entity\StudentProfile;
use App\Entity\User;
use App\Security\StudentProfileAccessService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpKernel\Attribute\AsController;

#[AsController]
class DeleteStudentProfile extends AbstractController
{
    public function __invoke(StudentProfile $data, StudentProfileAccessService $accessService, EntityManagerInterface $entityManager)
    {
        $actor = $this->getUser();
        if (!$actor instanceof User) {
            return $this->json(['message' => 'Unauthorized'], 401);
        }

        if (!$accessService->canDeleteProfile($actor, $data)) {
            return $this->json(['message' => 'Forbidden'], 403);
        }

        $id = $data->getId();
        $entityManager->remove($data);
        $entityManager->flush();

        return $this->json([
            'id' => $id,
            'deleted' => true,
        ]);
    }
}
