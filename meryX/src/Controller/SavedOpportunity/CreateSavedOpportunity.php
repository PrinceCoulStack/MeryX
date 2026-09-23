<?php

namespace App\Controller\SavedOpportunity;

use App\Entity\SavedOpportunity;
use App\Entity\User;
use App\Repository\OpportunitiesRepository;
use App\Repository\SavedOpportunityRepository;
use App\Repository\StudentProfileRepository;
use App\Security\Authorization\ActorContextResolver;
use App\Service\Api\HydraErrorResponseFactory;
use App\Service\Candidature\SavedOpportunityResponseBuilder;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\AsController;

#[AsController]
class CreateSavedOpportunity extends AbstractController
{
    public function __invoke(
        Request $request,
        EntityManagerInterface $entityManager,
        SavedOpportunityRepository $savedOpportunityRepository,
        OpportunitiesRepository $opportunitiesRepository,
        StudentProfileRepository $studentProfileRepository,
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

        if ($isCompany || (!$isAdmin && !$isStudent)) {
            return $errorFactory->create('Forbidden', 'Only student and admin users can save opportunities.', 403);
        }

        $payload = json_decode($request->getContent(), true);
        if (!is_array($payload)) {
            return $errorFactory->create('Bad Request', 'Invalid saved opportunity payload.', 400);
        }

        $opportunityId = $this->resolveEntityId($payload['opportunityId'] ?? null);
        $studentId = $this->resolveEntityId($payload['studentId'] ?? null);

        if ($opportunityId === null || $studentId === null) {
            return $errorFactory->create('Bad Request', 'opportunityId and studentId are required.', 400);
        }

        if ($isStudent) {
            $actorProfile = $actorResolver->resolveStudentProfile($actor);
            if ($actorProfile === null || $actorProfile->getId() === null) {
                return $errorFactory->create('Forbidden', 'Student profile not found for authenticated user.', 403);
            }

            if ($studentId !== $actorProfile->getId()) {
                return $errorFactory->create('Forbidden', 'Students can only save opportunities for their own profile.', 403);
            }
        }

        $student = $studentProfileRepository->find($studentId);
        $opportunity = $opportunitiesRepository->find($opportunityId);

        if ($student === null || $opportunity === null) {
            return $errorFactory->create('Bad Request', 'Invalid studentId or opportunityId.', 400);
        }

        $existing = $savedOpportunityRepository->findOneBy([
            'student' => $student,
            'opportunity' => $opportunity,
        ]);

        if ($existing instanceof SavedOpportunity) {
            return $this->json($responseBuilder->buildItem($existing), 200);
        }

        $now = new \DateTimeImmutable('now', new \DateTimeZone('UTC'));

        $saved = new SavedOpportunity();
        $saved->setOpportunity($opportunity);
        $saved->setStudent($student);
        if (array_key_exists('notes', $payload)) {
            $saved->setNotes($this->normalizeNotesForStorage($payload['notes']));
        } else {
            $saved->setNotes(null);
        }
        $saved->setSavedDate($now);
        $saved->setCreatedAt($now);
        $saved->setUpdatedAt($now);

        $entityManager->persist($saved);
        $entityManager->flush();

        return $this->json($responseBuilder->buildItem($saved), 201);
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
