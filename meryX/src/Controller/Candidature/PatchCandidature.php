<?php

namespace App\Controller\Candidature;

use App\Entity\User;
use App\Repository\CandidatureRepository;
use App\Security\Authorization\ActorContextResolver;
use App\Service\Api\HydraErrorResponseFactory;
use App\Service\Candidature\CandidatureAuditLogger;
use App\Service\Candidature\CandidatureResponseBuilder;
use App\Service\Candidature\CandidatureTransitionValidator;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\AsController;

#[AsController]
class PatchCandidature extends AbstractController
{
    public function __invoke(
        Request $request,
        CandidatureRepository $candidatureRepository,
        EntityManagerInterface $entityManager,
        ActorContextResolver $actorResolver,
        CandidatureTransitionValidator $transitionValidator,
        CandidatureAuditLogger $auditLogger,
        CandidatureResponseBuilder $responseBuilder,
        HydraErrorResponseFactory $errorFactory,
    ) {
        $actor = $this->getUser();
        if (!$actor instanceof User) {
            return $errorFactory->create('Unauthorized', 'Missing or invalid JWT token.', 401);
        }

        $isAdmin = $actorResolver->hasRole($actor, 'ROLE_ADMIN');
        $isCompany = $actorResolver->hasRole($actor, 'ROLE_COMPANY');

        if (!$isAdmin && !$isCompany) {
            return $errorFactory->create('Forbidden', 'Only company and admin users can update candidature status.', 403);
        }

        $id = (int) $request->attributes->get('id');
        $candidature = $candidatureRepository->find($id);

        if ($candidature === null) {
            return $errorFactory->create('Not Found', 'Candidature not found.', 404);
        }

        if ($isCompany) {
            $company = $actorResolver->resolveCompany($actor);
            if ($company === null || $company->getId() === null) {
                return $errorFactory->create('Forbidden', 'Company profile not found for authenticated user.', 403);
            }

            $targetCompanyId = $candidature->getOpportunity()?->getCompanyId()?->getId();
            if ($targetCompanyId !== $company->getId()) {
                return $errorFactory->create('Forbidden', 'Company can only update candidatures for own opportunities.', 403);
            }
        }

        $payload = json_decode($request->getContent(), true);
        if (!is_array($payload)) {
            return $errorFactory->create('Bad Request', 'Invalid candidature payload.', 400);
        }

        $oldStatus = $candidature->getStatus();
        $newStatus = isset($payload['status']) ? strtolower(trim((string) $payload['status'])) : $oldStatus;

        try {
            $transitionValidator->assertTransition($oldStatus, $newStatus);
        } catch (\InvalidArgumentException $exception) {
            return $errorFactory->create('Bad Request', $exception->getMessage(), 400);
        }

        if (array_key_exists('status', $payload)) {
            $candidature->setStatus($newStatus);
        }

        if (array_key_exists('feedback', $payload)) {
            $candidature->setFeedback($payload['feedback'] !== null ? (string) $payload['feedback'] : null);
        }

        if (array_key_exists('notes', $payload)) {
            $candidature->setNotes($this->normalizeNotesForStorage($payload['notes']));
        }

        if (array_key_exists('interviewDate', $payload)) {
            if ($payload['interviewDate'] === null || $payload['interviewDate'] === '') {
                $candidature->setInterviewDate(null);
            } else {
                try {
                    $candidature->setInterviewDate(new \DateTimeImmutable((string) $payload['interviewDate']));
                } catch (\Throwable) {
                    return $errorFactory->create('Bad Request', 'Invalid interviewDate value.', 400);
                }
            }
        }

        $now = new \DateTimeImmutable('now', new \DateTimeZone('UTC'));
        $candidature->setLastUpdated($now);
        $candidature->setUpdatedAt($now);

        if ($newStatus !== $oldStatus) {
            $auditLogger->logStatusChange($actor, $candidature, $oldStatus, $newStatus, $request);
        }

        $entityManager->persist($candidature);
        $entityManager->flush();

        return $this->json($responseBuilder->buildItem($candidature), 200);
    }

    private function normalizeNotesForStorage(mixed $notes): ?string
    {
        if ($notes === null) {
            return null;
        }

        if (is_array($notes)) {
            return json_encode($notes, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR);
        }

        if (is_scalar($notes)) {
            return (string) $notes;
        }

        return null;
    }
}
