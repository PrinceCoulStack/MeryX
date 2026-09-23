<?php

namespace App\Controller\Candidature;

use App\Entity\User;
use App\Repository\CandidatureRepository;
use App\Security\Authorization\ActorContextResolver;
use App\Service\Api\HydraErrorResponseFactory;
use App\Service\Candidature\CandidatureResponseBuilder;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\AsController;

#[AsController]
class ListCandidature extends AbstractController
{
    public function __invoke(
        Request $request,
        CandidatureRepository $repository,
        ActorContextResolver $actorResolver,
        CandidatureResponseBuilder $responseBuilder,
        HydraErrorResponseFactory $errorFactory,
    ) {
        $actor = $this->getUser();
        if (!$actor instanceof User) {
            return $errorFactory->create('Unauthorized', 'Missing or invalid JWT token.', 401);
        }

        $isAdmin = $actorResolver->hasRole($actor, 'ROLE_ADMIN');
        $isCompany = $actorResolver->hasRole($actor, 'ROLE_COMPANY');
        $isStudent = $actorResolver->hasRole($actor, 'ROLE_STUDENT');

        if (!$isAdmin && !$isCompany && !$isStudent) {
            return $errorFactory->create('Forbidden', 'You do not have permission to access candidatures.', 403);
        }

        $filters = [];

        if ($request->query->has('studentId')) {
            $studentId = $this->resolveEntityId($request->query->get('studentId'));
            if ($studentId === null) {
                return $errorFactory->create('Bad Request', 'Invalid studentId filter.', 400);
            }
            $filters['studentId'] = $studentId;
        }

        if ($request->query->has('opportunityId')) {
            $opportunityId = $this->resolveEntityId($request->query->get('opportunityId'));
            if ($opportunityId === null) {
                return $errorFactory->create('Bad Request', 'Invalid opportunityId filter.', 400);
            }
            $filters['opportunityId'] = $opportunityId;
        }

        if ($request->query->has('status')) {
            $filters['status'] = (string) $request->query->get('status');
        }

        if ($request->query->has('createdAfter')) {
            try {
                $filters['createdAfter'] = new \DateTimeImmutable((string) $request->query->get('createdAfter'));
            } catch (\Throwable) {
                return $errorFactory->create('Bad Request', 'Invalid createdAfter value.', 400);
            }
        }

        if ($request->query->has('createdBefore')) {
            try {
                $filters['createdBefore'] = new \DateTimeImmutable((string) $request->query->get('createdBefore'));
            } catch (\Throwable) {
                return $errorFactory->create('Bad Request', 'Invalid createdBefore value.', 400);
            }
        }

        if ($request->query->has('sortBy')) {
            $filters['sortBy'] = (string) $request->query->get('sortBy');
        }

        if ($request->query->has('order')) {
            $filters['order'] = (string) $request->query->get('order');
        }

        if ($isStudent) {
            $profile = $actorResolver->resolveStudentProfile($actor);
            if ($profile === null || $profile->getId() === null) {
                return $errorFactory->create('Forbidden', 'Student profile not found for authenticated user.', 403);
            }

            if (isset($filters['studentId']) && $filters['studentId'] !== $profile->getId()) {
                return $errorFactory->create('Forbidden', 'Students can only view their own candidatures.', 403);
            }

            $filters['visibleStudentId'] = $profile->getId();
        }

        if ($isCompany) {
            $company = $actorResolver->resolveCompany($actor);
            if ($company === null || $company->getId() === null) {
                return $errorFactory->create('Forbidden', 'Company profile not found for authenticated user.', 403);
            }

            $filters['visibleCompanyId'] = $company->getId();
        }

        $page = max(1, (int) $request->query->get('page', 1));
        $limit = min(100, max(1, (int) $request->query->get('limit', 20)));

        [$items, $total] = $repository->search($filters, $page, $limit);

        $query = [
            'studentId' => $request->query->get('studentId'),
            'status' => $request->query->get('status'),
            'opportunityId' => $request->query->get('opportunityId'),
            'createdAfter' => $request->query->get('createdAfter'),
            'createdBefore' => $request->query->get('createdBefore'),
            'sortBy' => $request->query->get('sortBy'),
            'order' => $request->query->get('order'),
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
