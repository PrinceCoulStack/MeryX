<?php

namespace App\Controller\Candidature;

use App\Entity\Candidature;
use App\Entity\User;
use App\Repository\CandidatureRepository;
use App\Repository\OpportunitiesRepository;
use App\Repository\StudentProfileRepository;
use App\Security\Authorization\ActorContextResolver;
use App\Service\Api\HydraErrorResponseFactory;
use App\Service\Candidature\CandidatureResponseBuilder;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\AsController;

#[AsController]
class CreateCandidature extends AbstractController
{
    public function __invoke(
        Request $request,
        EntityManagerInterface $entityManager,
        CandidatureRepository $candidatureRepository,
        OpportunitiesRepository $opportunitiesRepository,
        StudentProfileRepository $studentProfileRepository,
        ActorContextResolver $actorResolver,
        CandidatureResponseBuilder $responseBuilder,
        HydraErrorResponseFactory $errorFactory,
    ) {
        $actor = $this->getUser();
        if (!$actor instanceof User) {
            return $errorFactory->create('Unauthorized', 'Missing or invalid JWT token.', 401);
        }

        if (!$actorResolver->hasRole($actor, 'ROLE_STUDENT')) {
            return $errorFactory->create('Forbidden', 'Only students can create candidatures.', 403);
        }

        $payload = json_decode($request->getContent(), true);
        if (!is_array($payload)) {
            return $errorFactory->create('Bad Request', 'Invalid candidature payload.', 400);
        }

        $opportunityId = $this->resolveEntityId($payload['opportunityId'] ?? null);
        $studentId = $this->resolveEntityId($payload['studentId'] ?? null);

        if ($opportunityId === null || $studentId === null) {
            return $errorFactory->create('Bad Request', 'opportunityId and studentId are required.', 400);
        }

        $actorStudentProfile = $actorResolver->resolveStudentProfile($actor);
        if ($actorStudentProfile === null || $actorStudentProfile->getId() === null) {
            return $errorFactory->create('Forbidden', 'Student profile not found for authenticated user.', 403);
        }

        if ($studentId !== $actorStudentProfile->getId()) {
            return $errorFactory->create('Forbidden', 'Students can only apply for their own profile.', 403);
        }

        $student = $studentProfileRepository->find($studentId);
        $opportunity = $opportunitiesRepository->find($opportunityId);

        if ($student === null || $opportunity === null) {
            return $errorFactory->create('Bad Request', 'Invalid studentId or opportunityId.', 400);
        }

        if (!$actorResolver->isStudentApproved($student)) {
            return $errorFactory->create('Forbidden', 'Student profile must be approved before applying.', 403);
        }

        $existing = $candidatureRepository->findOneBy([
            'student' => $student,
            'opportunity' => $opportunity,
        ]);

        if ($existing instanceof Candidature) {
            return $errorFactory->create('Conflict', 'Candidature already exists for this student and opportunity.', 409);
        }

        $now = new \DateTimeImmutable('now', new \DateTimeZone('UTC'));

        $candidature = new Candidature();
        $candidature->setOpportunity($opportunity);
        $candidature->setStudent($student);
        $candidature->setStatus(Candidature::STATUS_APPLIED);
        $candidature->setAppliedDate($now);
        $candidature->setLastUpdated($now);
        $candidature->setFeedback(null);
        if (array_key_exists('notes', $payload)) {
            $candidature->setNotes($this->normalizeNotesForStorage($payload['notes']));
        } else {
            $candidature->setNotes(null);
        }
        $candidature->setInterviewDate(null);
        $candidature->setScore(0);
        $candidature->setCreatedAt($now);
        $candidature->setUpdatedAt($now);

        try {
            $entityManager->persist($candidature);
            $entityManager->flush();
        } catch (UniqueConstraintViolationException) {
            return $errorFactory->create('Conflict', 'Candidature already exists for this student and opportunity.', 409);
        }

        return $this->json($responseBuilder->buildItem($candidature), 201);
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
