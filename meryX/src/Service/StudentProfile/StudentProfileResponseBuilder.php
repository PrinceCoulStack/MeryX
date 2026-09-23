<?php

namespace App\Service\StudentProfile;

use App\Entity\AcademicRecord;
use App\Entity\Address;
use App\Entity\Language;
use App\Entity\StudentDocuments;
use App\Entity\StudentProfile;

class StudentProfileResponseBuilder
{
    private const RADAR_CATEGORIES = [
        'academic',
        'certificate',
        'numerique',
        'langue',
        'stage',
    ];

    /**
     * @return array<int, array<string, mixed>>
     */
    public function buildCollection(iterable $profiles): array
    {
        $result = [];
        foreach ($profiles as $profile) {
            if (!$profile instanceof StudentProfile) {
                continue;
            }

            $result[] = $this->buildItem($profile);
        }

        return $result;
    }

    /**
     * @return array<string, mixed>
     */
    public function buildItem(StudentProfile $profile): array
    {
        $bio = $this->normalizeBio($profile->getBio());
        $bio = $this->hydrateBioFromProfile($bio, $profile);
        $user = $profile->getUserId();
        $personal = isset($bio['personal']) && is_array($bio['personal']) ? $bio['personal'] : [];
        $academic = isset($bio['academic']) && is_array($bio['academic']) ? $bio['academic'] : [];
        $verificationData = $profile->getVerificationData() ?? [];
        $address = $this->buildAddressPayload($user?->getAddressId(), $personal, $academic);

        return [
            'id' => $profile->getId(),
            'userId' => $this->buildUserReference($profile),
            'user' => $this->buildUserReference($profile),
            'universityId' => $this->buildUniversityReference($profile),
            'university' => $this->buildUniversityReference($profile),
            'status' => $profile->getStatus(),
            'isApproved' => $profile->isApproved(),
            'fullName' => $profile->getFullName(),
            'email' => $user?->getEmail(),
            'phone' => $user?->getPhone(),
            'gpa' => $profile->getGpa(),
            'gender' => $profile->getGender(),
            'program' => $profile->getProgram(),
            'level' => $this->resolveStudentLevel($academic, $personal, $verificationData, $profile),
            'academicYear' => $this->firstNonEmptyString([
                $profile->getAcademicYear(),
                $this->safeString($academic['academicYear'] ?? null),
            ]),
            'faculty' => $this->firstNonEmptyString([
                $profile->getFaculty(),
                $this->safeString($academic['faculty'] ?? null),
            ]),
            'department' => $this->firstNonEmptyString([
                $this->safeString($academic['department'] ?? null),
                $this->safeString($personal['department'] ?? null),
                $this->safeString($verificationData['department'] ?? null),
            ]),
            'studentId' => $this->firstNonEmptyString([
                $this->safeString($academic['studentId'] ?? null),
                $this->safeString($personal['studentId'] ?? null),
                $this->safeString($verificationData['studentId'] ?? null),
            ]),
            'nationality' => $this->firstNonEmptyString([
                $this->safeString($personal['nationality'] ?? null),
                $this->safeString($verificationData['nationality'] ?? null),
                $this->safeString($address['country'] ?? null),
            ]),
            'address' => $address,
            'summary' => $this->firstNonEmptyString([
                $this->safeString($personal['summary'] ?? null),
                $this->safeString($bio['summary'] ?? null),
                $this->safeString($verificationData['summary'] ?? null),
            ]),
            'profileUrl' => $this->normalizeProfileUrl($profile->getProfileUrl()),
            'profileCompletion' => $profile->getProfileCompletion(),
            'skills' => array_values($profile->getSkills()),
            'languages' => $this->buildLanguageCollection($profile),
            'studentDocuments' => $this->buildDocumentCollection($profile),
            'academicRecords' => $this->buildAcademicRecordCollection($profile),
            'bio' => $bio,
            'personal' => $personal,
            'academic' => $academic,
            'verificationData' => $verificationData,
            'reviewReason' => $profile->getReviewReason(),
            'reviewNote' => $profile->getReviewNote(),
            'reviewedAt' => $profile->getReviewedAt()?->format(\DateTimeInterface::ATOM),
            'createdAt' => $profile->getCreatedAt()?->format(\DateTimeInterface::ATOM),
            'updatedAt' => $profile->getUpdatedAt()?->format(\DateTimeInterface::ATOM),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function normalizeBio(?string $bio): array
    {
        if ($bio === null || trim($bio) === '') {
            return $this->emptyBio();
        }

        $decoded = json_decode($bio, true);
        if (!is_array($decoded)) {
            $empty = $this->emptyBio();
            $empty['personal']['summary'] = $bio;

            return $empty;
        }

        $decoded['personal'] = isset($decoded['personal']) && is_array($decoded['personal']) ? $decoded['personal'] : [];
        $decoded['academic'] = isset($decoded['academic']) && is_array($decoded['academic']) ? $decoded['academic'] : [];
        $decoded['radar'] = isset($decoded['radar']) && is_array($decoded['radar']) ? $decoded['radar'] : [];

        foreach (self::RADAR_CATEGORIES as $category) {
            if (!isset($decoded['radar'][$category]) || !is_array($decoded['radar'][$category])) {
                $decoded['radar'][$category] = [];
            }
            if (isset($decoded['radar'][$category]['entries']) && is_array($decoded['radar'][$category]['entries'])) {
                $decoded['radar'][$category] = $decoded['radar'][$category]['entries'];
            }
            $decoded['radar'][$category] = array_values($decoded['radar'][$category]);
        }

        return $decoded;
    }

    /**
     * @return array<string, mixed>
     */
    private function emptyBio(): array
    {
        return [
            'personal' => [],
            'academic' => [],
            'radar' => [
                'academic' => [],
                'certificate' => [],
                'numerique' => [],
                'langue' => [],
                'stage' => [],
            ],
        ];
    }

    private function normalizeProfileUrl(?string $profileUrl): ?string
    {
        if ($profileUrl === null || trim($profileUrl) === '') {
            return null;
        }

        $normalized = trim($profileUrl);

        if (str_starts_with($normalized, 'data:image/')) {
            $sanitizedDataUrl = preg_replace('/\s+/', '', $normalized);

            return is_string($sanitizedDataUrl) && $sanitizedDataUrl !== '' ? $sanitizedDataUrl : null;
        }

        if (str_starts_with($normalized, 'http://') || str_starts_with($normalized, 'https://') || str_starts_with($normalized, '/uploads/')) {
            return $normalized;
        }

        return '/uploads/student_profiles/' . ltrim($normalized, '/');
    }

    /**
     * @param array<string, mixed> $bio
     * @return array<string, mixed>
     */
    private function hydrateBioFromProfile(array $bio, StudentProfile $profile): array
    {
        $bio['personal']['fullName'] = $bio['personal']['fullName'] ?? $profile->getFullName();
        $bio['personal']['gender'] = $bio['personal']['gender'] ?? $profile->getGender();
        $bio['personal']['profileUrl'] = $bio['personal']['profileUrl'] ?? $this->normalizeProfileUrl($profile->getProfileUrl());

        $bio['academic']['gpa'] = $bio['academic']['gpa'] ?? $profile->getGpa();
        $bio['academic']['profileCompletion'] = $bio['academic']['profileCompletion'] ?? $profile->getProfileCompletion();
        $bio['academic']['universityId'] = $bio['academic']['universityId'] ?? $profile->getUniversityId()?->getId();
        $bio['academic']['program'] = $bio['academic']['program'] ?? $profile->getProgram();

        return $bio;
    }

    /**
     * @return array<string, mixed>|null
     */
    private function buildUserReference(StudentProfile $profile): ?array
    {
        $user = $profile->getUserId();
        if ($user === null) {
            return null;
        }

        return [
            'id' => $user->getId(),
            'email' => $user->getEmail(),
            'phone' => $user->getPhone(),
            'address' => $this->buildAddressPayload($user->getAddressId(), [], []),
        ];
    }

    /**
     * @return array<string, mixed>|null
     */
    private function buildUniversityReference(StudentProfile $profile): ?array
    {
        $university = $profile->getUniversityId();
        if ($university === null) {
            return null;
        }

        return [
            'id' => $university->getId(),
            'name' => $university->getName(),
            'email' => $university->getEmail(),
            'status' => $university->getStatus(),
        ];
    }

    private function resolveStudentLevel(array $academic, array $personal, array $verificationData, StudentProfile $profile): ?string
    {
        return $this->firstNonEmptyString([
            $this->safeString($academic['level'] ?? null),
            $this->safeString($personal['level'] ?? null),
            $this->safeString($verificationData['level'] ?? null),
            $profile->getInternshipCycle(),
            $profile->getAcademicYear(),
        ]);
    }

    /**
     * @return array<string, mixed>|null
     */
    private function buildAddressPayload(?Address $address, array $personal, array $academic): ?array
    {
        $city = $this->firstNonEmptyString([
            $this->safeString($personal['city'] ?? null),
            $this->safeString($academic['city'] ?? null),
            $address?->getCity(),
        ]);
        $state = $this->firstNonEmptyString([
            $this->safeString($personal['state'] ?? null),
            $this->safeString($academic['state'] ?? null),
            $address?->getState(),
        ]);
        $country = $this->firstNonEmptyString([
            $this->safeString($personal['country'] ?? null),
            $this->safeString($academic['country'] ?? null),
            $address?->getCountry(),
        ]);

        if ($city === null && $state === null && $country === null && $address === null) {
            return null;
        }

        return [
            'id' => $address?->getId(),
            'city' => $city,
            'state' => $state,
            'country' => $country,
            'latitude' => $address?->getLatitude(),
            'longitude' => $address?->getLongitude(),
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function buildLanguageCollection(StudentProfile $profile): array
    {
        $items = [];

        foreach ($profile->getLanguages() as $language) {
            if (!$language instanceof Language) {
                continue;
            }

            $items[] = [
                'id' => $language->getId(),
                'name' => $language->getName(),
                'level' => $language->getLevel(),
                'createdAt' => $language->getCreatedAt()?->format(\DateTimeInterface::ATOM),
                'updatedAt' => $language->getUpdatedAt()?->format(\DateTimeInterface::ATOM),
            ];
        }

        return $items;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function buildDocumentCollection(StudentProfile $profile): array
    {
        $items = [];

        foreach ($profile->getStudentDocuments() as $document) {
            if (!$document instanceof StudentDocuments) {
                continue;
            }

            $items[] = [
                'id' => $document->getId(),
                'type' => $document->getType(),
                'title' => $document->getTitle(),
                'fileName' => $document->getFileName(),
                'filePath' => $document->getFilePath(),
                'mimeType' => $document->getMimeType(),
                'fileSize' => $document->getFileSize(),
                'isPublic' => $document->isPublic(),
                'uploadedAt' => $document->getUploadedAt()?->format(\DateTimeInterface::ATOM),
                'updatedAt' => $document->getUpdatedAt()?->format(\DateTimeInterface::ATOM),
            ];
        }

        return $items;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function buildAcademicRecordCollection(StudentProfile $profile): array
    {
        $items = [];

        foreach ($profile->getAcademicRecords() as $record) {
            if (!$record instanceof AcademicRecord) {
                continue;
            }

            $items[] = [
                'id' => $record->getId(),
                'institutionName' => $record->getInstitutionName(),
                'degree' => $record->getDegree(),
                'fieldOfStudy' => $record->getFieldOfStudy(),
                'startDate' => $record->getStartDate()?->format('Y-m-d'),
                'endDate' => $record->getEndDate()?->format('Y-m-d'),
                'gpa' => $record->getGpa(),
                'description' => $record->getDescription(),
                'createdAt' => $record->getCreatedAt()?->format(\DateTimeInterface::ATOM),
                'updatedAt' => $record->getUpdatedAt()?->format(\DateTimeInterface::ATOM),
            ];
        }

        return $items;
    }

    /**
     * @param array<int, ?string> $values
     */
    private function firstNonEmptyString(array $values): ?string
    {
        foreach ($values as $value) {
            if (!is_string($value)) {
                continue;
            }

            $normalized = trim($value);
            if ($normalized !== '') {
                return $normalized;
            }
        }

        return null;
    }

    private function safeString(mixed $value): ?string
    {
        if ($value === null || !is_scalar($value)) {
            return null;
        }

        $normalized = trim((string) $value);

        return $normalized !== '' ? $normalized : null;
    }
}
