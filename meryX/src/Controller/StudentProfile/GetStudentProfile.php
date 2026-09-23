<?php

namespace App\Controller\StudentProfile;

use App\Entity\StudentProfile;
use App\Entity\User;
use App\Security\StudentProfileAccessService;
use App\Service\StudentProfile\StudentProfileResponseBuilder;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpKernel\Attribute\AsController;

#[AsController]
class GetStudentProfile extends AbstractController
{
    public function __invoke(StudentProfile $data, StudentProfileAccessService $accessService, StudentProfileResponseBuilder $responseBuilder)
    {
        $actor = $this->getUser();
        if (!$actor instanceof User) {
            return $this->json(['message' => 'Unauthorized'], 401);
        }

        if (!$accessService->canViewProfile($actor, $data)) {
            return $this->json(['message' => 'Forbidden'], 403);
        }

        return $this->json($responseBuilder->buildItem($data));
    }
}
