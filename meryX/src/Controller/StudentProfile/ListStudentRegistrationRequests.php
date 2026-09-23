<?php

namespace App\Controller\StudentProfile;

use App\Entity\User;
use App\Repository\StudentProfileRepository;
use App\Security\StudentProfileAccessService;
use App\Service\StudentProfile\StudentProfileResponseBuilder;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\AsController;

#[AsController]
class ListStudentRegistrationRequests extends AbstractController
{
    public function __invoke(
        Request $request,
        StudentProfileRepository $studentProfileRepository,
        StudentProfileAccessService $accessService,
        StudentProfileResponseBuilder $responseBuilder
    ) {
        $actor = $this->getUser();
        if (!$actor instanceof User) {
            return $this->json(['message' => 'Unauthorized'], 401);
        }

        $status = strtolower(trim((string) $request->query->get('status', '')));
        if ($status !== '' && !in_array($status, ['pending', 'approved', 'rejected'], true)) {
            return $this->json(['message' => 'Invalid status filter'], 400);
        }

        if ($accessService->hasRole($actor, 'ROLE_ADMIN')) {
            $profiles = $studentProfileRepository->findRegistrationRequestsForUniversity(null, $status !== '' ? $status : null);

            return $this->json($responseBuilder->buildCollection($profiles));
        }

        if (!$accessService->hasRole($actor, 'ROLE_UNIVERSITY')) {
            return $this->json(['message' => 'Forbidden'], 403);
        }

        $actorUniversity = $accessService->resolveUniversityForActor($actor);
        if ($actorUniversity === null) {
            return $this->json(['message' => 'Forbidden'], 403);
        }

        $profiles = $studentProfileRepository->findRegistrationRequestsForUniversity($actorUniversity, $status !== '' ? $status : null);

        return $this->json($responseBuilder->buildCollection($profiles));
    }
}
