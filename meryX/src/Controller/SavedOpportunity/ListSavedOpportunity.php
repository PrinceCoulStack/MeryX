<?php

namespace App\Controller\SavedOpportunity;

use App\Entity\User;
use App\Repository\SavedOpportunityRepository;
use App\Security\Authorization\ActorContextResolver;
use App\Service\Api\HydraErrorResponseFactory;
use App\Service\Candidature\SavedOpportunityResponseBuilder;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\AsController;

#[AsController]
class ListSavedOpportunity extends AbstractController
{
    public function __invoke(
        Request $request,
        SavedOpportunityRepository $repository,
        ActorContextResolver $actorResolver,
        SavedOpportunityResponseBuilder $responseBuilder,
        HydraErrorResponseFactory $errorFactory,
    ) {
        $actor = $this->getUser();
        if (!$actor instanceof User) {
            return $errorFactory->create('Unauthorized', 'Missing or invalid JWT token.', 401);
        }

        $isAdmin = $actorResolver->hasRole($actor, 'ROLE_ADMIN');
        $isStudent = $actorResolver->hasRole($actor, 'ROLE_STUDENT');
        $isCompany = $actorResolver->hasRole($actor, 'ROLE_COMPANY');

        if ($isCompany) {
            return $errorFactory->create('Forbidden', 'Company users cannot view saved opportunities.', 403);
        }

        if (!$isAdmin && !$isStudent) {
            return $errorFactory->create('Forbidden', 'You do not have permission to view saved opportunities.', 403);
        }

        $filters = [];

        if ($request->query->has('studentId')) {
            $studentId = $this->resolveEntityId($request->query->get('studentId'));
            if ($studentId === null) {
                return $errorFactory->create('Bad Request', 'Invalid studentId filter.', 400);
            }
            $filters['studentId'] = $studentId;
        }

        if ($request->query->has('savedAfter')) {
            try {
                $filters['savedAfter'] = new \DateTimeImmutable((string) $request->query->get('savedAfter'));
            } catch (\Throwable) {
                return $errorFactory->create('Bad Request', 'Invalid savedAfter value.', 400);
            }
        }

        if ($request->query->has('savedBefore')) {
            try {
                $filters['savedBefore'] = new \DateTimeImmutable((string) $request->query->get('savedBefore'));
            } catch (\Throwable) {
                return $errorFactory->create('Bad Request', 'Invalid savedBefore value.', 400);
            }
        }

        if ($request->query->has('search')) {
            $filters['search'] = (string) $request->query->get('search');
        }

        if ($isStudent) {
            $profile = $actorResolver->resolveStudentProfile($actor);
            if ($profile === null || $profile->getId() === null) {
                return $errorFactory->create('Forbidden', 'Student profile not found for authenticated user.', 403);
            }

            if (isset($filters['studentId']) && $filters['studentId'] !== $profile->getId()) {
                return $errorFactory->create('Forbidden', 'Students can only view their own saved opportunities.', 403);
            }

            $filters['visibleStudentId'] = $profile->getId();
        }

        $page = max(1, (int) $request->query->get('page', 1));
        $limit = min(100, max(1, (int) $request->query->get('limit', 20)));

        [$items, $total] = $repository->search($filters, $page, $limit);

        $query = [
            'studentId' => $request->query->get('studentId'),
            'savedAfter' => $request->query->get('savedAfter'),
            'savedBefore' => $request->query->get('savedBefore'),
            'search' => $request->query->get('search'),
            'limit' => $limit,
        ];

        $collection = $responseBuilder->buildCollection($items, $total, $page, $limit, $query);
        $plain = filter_var((string) $request->query->get('plain', 'false'), FILTER_VALIDATE_BOOL);

        if ($plain) {
            return $this->json($collection['hydra:member'] ?? [], 200);
        }

        return $this->json($collection, 200);
    }

    private function resolveEntityId(mixed $value): ?int
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (is_array($value) && isset($value['id'])) {
            $value = $value['id'];
        }

        if (is_string($value) && preg_match('/\/(\d+)$/', $value, $matches)) {
            return (int) $matches[1];
        }

        if (is_scalar($value)) {
            $raw = trim((string) $value);
            if ($raw === '' || $raw === 'null' || $raw === 'undefined') {
                return null;
            }

            $id = (int) $raw;

            return $id > 0 ? $id : null;
        }

        return null;
    }
}
