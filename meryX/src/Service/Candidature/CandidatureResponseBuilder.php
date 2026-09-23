<?php

namespace App\Service\Candidature;

use App\Entity\Candidature;

class CandidatureResponseBuilder
{
    /**
     * @return array<string, mixed>
     */
    public function buildItem(Candidature $candidature): array
    {
        $opportunity = $candidature->getOpportunity();
        $student = $candidature->getStudent();
        $studentBio = $this->normalizeStudentBio($student?->getBio());

        return [
            '@context' => '/api/contexts/Candidature',
            '@type' => 'Candidature',
            '@id' => '/api/candidatures/' . $candidature->getId(),
            'id' => $candidature->getId(),
            'opportunityId' => $opportunity?->getId(),
            'opportunity' => $opportunity ? [
                '@type' => 'Opportunity',
                '@id' => '/api/opportunities/' . $opportunity->getId(),
                'id' => $opportunity->getId(),
                'title' => $opportunity->getTitle(),
                'company' => $opportunity->getCompanyId()?->getName(),
            ] : null,
            'studentId' => $student?->getId(),
            'student' => $student ? [
                '@type' => 'StudentProfile',
                '@id' => '/api/studentProfiles/' . $student->getId(),
                'id' => $student->getId(),
                'fullName' => $student->getFullName(),
                'email' => $student->getUserId()?->getEmail(),
                'phone' => $student->getUserId()?->getPhone(),
                'program' => $student->getProgram(),
                'level' => $this->resolveStudentLevel($studentBio, $student->getAcademicYear(), $student->getInternshipCycle()),
                'gpa' => $student->getGpa(),
                'skills' => array_values($student->getSkills()),
                'summary' => $this->resolveStudentSummary($studentBio),
            ] : null,
            'status' => $candidature->getStatus(),
            'appliedDate' => $candidature->getAppliedDate()?->format(\DateTimeInterface::ATOM),
            'lastUpdated' => $candidature->getLastUpdated()?->format(\DateTimeInterface::ATOM),
            'feedback' => $candidature->getFeedback(),
            'notes' => $this->normalizeNotes($candidature->getNotes()),
            'interviewDate' => $candidature->getInterviewDate()?->format(\DateTimeInterface::ATOM),
            'score' => $candidature->getScore(),
            'createdAt' => $candidature->getCreatedAt()?->format(\DateTimeInterface::ATOM),
            'updatedAt' => $candidature->getUpdatedAt()?->format(\DateTimeInterface::ATOM),
        ];
    }

    /**
     * @param array<int, Candidature> $items
     * @param array<string, mixed> $query
     * @return array<string, mixed>
     */
    public function buildCollection(array $items, int $total, int $page, int $limit, array $query = []): array
    {
        $members = [];
        foreach ($items as $item) {
            $members[] = $this->buildItemWithoutContext($item);
        }

        $lastPage = max(1, (int) ceil($total / max(1, $limit)));

        return [
            '@context' => '/api/contexts/Candidature',
            '@type' => 'hydra:Collection',
            '@id' => $this->buildCollectionIri('/api/candidatures', $query + ['page' => $page]),
            'hydra:member' => $members,
            'hydra:totalItems' => $total,
            'hydra:view' => [
                '@id' => $this->buildCollectionIri('/api/candidatures', $query + ['page' => $page]),
                '@type' => 'hydra:PartialCollectionView',
                'hydra:first' => $this->buildCollectionIri('/api/candidatures', $query + ['page' => 1]),
                'hydra:last' => $this->buildCollectionIri('/api/candidatures', $query + ['page' => $lastPage]),
                'hydra:next' => $page < $lastPage ? $this->buildCollectionIri('/api/candidatures', $query + ['page' => $page + 1]) : null,
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function buildItemWithoutContext(Candidature $candidature): array
    {
        $payload = $this->buildItem($candidature);
        unset($payload['@context']);

        return $payload;
    }

    /**
     * @param array<string, mixed> $query
     */
    private function buildCollectionIri(string $basePath, array $query): string
    {
        $filtered = [];
        foreach ($query as $key => $value) {
            if ($value === null || $value === '') {
                continue;
            }
            $filtered[$key] = $value;
        }

        $queryString = http_build_query($filtered);

        return $queryString === '' ? $basePath : $basePath . '?' . $queryString;
    }

    /**
     * @return array<string, mixed>
     */
    private function normalizeStudentBio(?string $bio): array
    {
        if ($bio === null || trim($bio) === '') {
            return [];
        }

        $decoded = json_decode($bio, true);

        return is_array($decoded) ? $decoded : [];
    }

    private function resolveStudentLevel(array $bio, ?string $academicYear, ?string $internshipCycle): ?string
    {
        $fromPersonal = $bio['personal']['level'] ?? null;
        if (is_string($fromPersonal) && trim($fromPersonal) !== '') {
            return trim($fromPersonal);
        }

        $fromAcademic = $bio['academic']['level'] ?? null;
        if (is_string($fromAcademic) && trim($fromAcademic) !== '') {
            return trim($fromAcademic);
        }

        if (is_string($academicYear) && trim($academicYear) !== '') {
            return trim($academicYear);
        }

        if (is_string($internshipCycle) && trim($internshipCycle) !== '') {
            return trim($internshipCycle);
        }

        return null;
    }

    private function resolveStudentSummary(array $bio): ?string
    {
        $summary = $bio['personal']['summary'] ?? $bio['summary'] ?? null;

        if (!is_string($summary)) {
            return null;
        }

        $normalized = trim($summary);

        return $normalized !== '' ? $normalized : null;
    }

    /**
     * @return array<string, mixed>|string|null
     */
    private function normalizeNotes(?string $notes): array|string|null
    {
        if ($notes === null) {
            return null;
        }

        $decoded = json_decode($notes, true);
        if (is_array($decoded)) {
            return $decoded;
        }

        return $notes;
    }
}
