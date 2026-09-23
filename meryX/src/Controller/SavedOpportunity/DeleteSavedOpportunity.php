<?php

namespace App\Controller\SavedOpportunity;

use App\Entity\User;
use App\Repository\SavedOpportunityRepository;
use App\Security\Authorization\ActorContextResolver;
use App\Service\Api\HydraErrorResponseFactory;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;

#[AsController]
class DeleteSavedOpportunity extends AbstractController
{
    public function __invoke(
        int $id,
        SavedOpportunityRepository $savedOpportunityRepository,
        EntityManagerInterface $entityManager,
        ActorContextResolver $actorResolver,
        HydraErrorResponseFactory $errorFactory,
    ) {
        $actor = $this->getUser();
        if (!$actor instanceof User) {
            return $errorFactory->create('Unauthorized', 'Missing or invalid JWT token.', 401);
        }

        $isAdmin = $actorResolver->hasRole($actor, 'ROLE_ADMIN');
        $isStudent = $actorResolver->hasRole($actor, 'ROLE_STUDENT');
        $isCompany = $actorResolver->hasRole($actor, 'ROLE_COMPANY');

        if ($isCompany || (!$isAdmin && !$isStudent)) {
            return $errorFactory->create('Forbidden', 'Only student and admin users can delete saved opportunities.', 403);
        }

        $saved = $savedOpportunityRepository->find($id);
        if ($saved === null) {
            return $errorFactory->create('Not Found', 'Saved opportunity not found.', 404);
        }

        if ($isStudent) {
            $profile = $actorResolver->resolveStudentProfile($actor);
            if ($profile === null || $profile->getId() === null) {
                return $errorFactory->create('Forbidden', 'Student profile not found for authenticated user.', 403);
            }

            if ($saved->getStudent()?->getId() !== $profile->getId()) {
                return $errorFactory->create('Forbidden', 'Students can only delete their own saved opportunities.', 403);
            }
        }

        $entityManager->remove($saved);
        $entityManager->flush();

        return new Response(null, 204);
    }
}
