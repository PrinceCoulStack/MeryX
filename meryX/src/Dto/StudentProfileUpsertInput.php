<?php

namespace App\Dto;

use Symfony\Component\HttpFoundation\Request;

class StudentProfileUpsertInput
{
    public ?array $personal = null;
    public ?array $academic = null;
    public ?array $skills = null;
    public ?array $languages = null;
    public ?array $projects = null;
    public ?array $academicRecords = null;

    public ?int $userId = null;
    public mixed $universityId = null;
    public ?string $profileUrl = null;
    public mixed $bio = null;
    public ?int $profileCompletion = null;
    public ?string $fullName = null;
    public ?string $gpa = null;
    public ?string $gender = null;
    public ?string $program = null;
    public ?string $level = null;
    public ?string $academicYear = null;
    public ?string $faculty = null;
    public ?string $department = null;
    public ?string $studentId = null;
    public ?string $nationality = null;
    public ?array $address = null;
    public ?string $summary = null;
    public ?string $email = null;
    public ?string $phone = null;
    public ?string $status = null;
    public ?bool $isApproved = null;
    public ?string $reviewReason = null;
    public ?string $reviewNote = null;

    public static function fromArray(array $payload): self
    {
        $input = new self();
        $payload = self::unwrapStudentProfilesPayload($payload);

        $input->personal = self::arrayOrJsonValue($payload, 'personal');
        $input->academic = self::arrayOrJsonValue($payload, 'academic');
        $verificationData = self::arrayOrJsonValue($payload, 'verificationData') ?? [];
        $input->skills = self::arrayOrJsonValue($payload, 'skills');
        // languages/projects/academicRecords are only auto-mapped to their strict relations when
        // sent at the top level; bio-nested copies of these (wizard "bio" blob) are intentionally
        // left alone here since their shape does not match the relation validation contract and
        // are preserved verbatim via StudentProfileUpsertService::normalizeBioForPersistence instead
        $input->languages = self::arrayOrJsonValue($payload, 'languages');
        $input->projects = self::arrayOrJsonValue($payload, 'projects');
        $input->academicRecords = self::arrayOrJsonValue($payload, 'academicRecords');

        $input->userId = self::intValue($payload, 'userId');
        $input->universityId = $payload['universityId'] ?? null;
        $input->profileUrl = self::stringValue($payload, 'profileUrl');
        $input->bio = self::nullableMixedValue($payload, 'bio');
        $bioPayload = is_array($input->bio) ? $input->bio : [];
        $bioPersonal = isset($bioPayload['personal']) && is_array($bioPayload['personal']) ? $bioPayload['personal'] : [];
        $bioAcademic = isset($bioPayload['academic']) && is_array($bioPayload['academic']) ? $bioPayload['academic'] : [];
        $input->profileCompletion = self::nullableIntValue($payload, 'profileCompletion');
        $input->fullName = self::nullableStringValue($payload, 'fullName');
        $input->gpa = self::nullableStringValue($payload, 'gpa');
        $input->gender = self::firstNonEmptyString([
            self::nullableStringValue($payload, 'gender'),
            self::mixedStringValue($input->personal['gender'] ?? null),
            self::mixedStringValue($bioPersonal['gender'] ?? null),
        ]);
        $input->program = self::firstNonEmptyString([
            self::nullableStringValue($payload, 'program'),
            self::mixedStringValue($input->academic['program'] ?? null),
            self::mixedStringValue($verificationData['program'] ?? null),
            self::mixedStringValue($bioAcademic['program'] ?? null),
        ]);
        $input->level = self::firstNonEmptyString([
            self::nullableStringValue($payload, 'level'),
            self::mixedStringValue($input->academic['level'] ?? null),
            self::mixedStringValue($verificationData['level'] ?? null),
            self::mixedStringValue($bioAcademic['level'] ?? null),
        ]);
        $input->academicYear = self::firstNonEmptyString([
            self::nullableStringValue($payload, 'academicYear'),
            self::mixedStringValue($input->academic['academicYear'] ?? null),
            self::mixedStringValue($verificationData['academicYear'] ?? null),
            self::mixedStringValue($bioAcademic['academicYear'] ?? null),
        ]);
        $input->faculty = self::firstNonEmptyString([
            self::nullableStringValue($payload, 'faculty'),
            self::mixedStringValue($input->academic['faculty'] ?? null),
            self::mixedStringValue($verificationData['faculty'] ?? null),
            self::mixedStringValue($bioAcademic['faculty'] ?? null),
        ]);
        $input->department = self::firstNonEmptyString([
            self::nullableStringValue($payload, 'department'),
            self::mixedStringValue($input->academic['department'] ?? null),
            self::mixedStringValue($bioAcademic['department'] ?? null),
            self::mixedStringValue($bioPersonal['department'] ?? null),
            self::mixedStringValue($verificationData['department'] ?? null),
        ]);
        $input->studentId = self::firstNonEmptyString([
            self::nullableStringValue($payload, 'studentId'),
            self::mixedStringValue($input->academic['studentId'] ?? null),
            self::mixedStringValue($bioAcademic['studentId'] ?? null),
            self::mixedStringValue($verificationData['studentId'] ?? null),
        ]);
        $input->nationality = self::firstNonEmptyString([
            self::nullableStringValue($payload, 'nationality'),
            self::mixedStringValue($input->personal['nationality'] ?? null),
            self::mixedStringValue($bioPersonal['nationality'] ?? null),
            self::mixedStringValue($verificationData['nationality'] ?? null),
        ]);
        $input->summary = self::firstNonEmptyString([
            self::nullableStringValue($payload, 'summary'),
            self::mixedStringValue($input->personal['summary'] ?? null),
            self::mixedStringValue($bioPersonal['summary'] ?? null),
            self::mixedStringValue($verificationData['summary'] ?? null),
        ]);
        $input->email = self::firstNonEmptyString([
            self::nullableStringValue($payload, 'email'),
            self::mixedStringValue($input->personal['email'] ?? null),
            self::mixedStringValue($bioPersonal['email'] ?? null),
        ]);
        $input->phone = self::firstNonEmptyString([
            self::nullableStringValue($payload, 'phone'),
            self::mixedStringValue($input->personal['phone'] ?? null),
            self::mixedStringValue($bioPersonal['phone'] ?? null),
        ]);
        $input->address = self::extractAddress($payload, $input->personal, $input->academic, $bioPersonal, $bioAcademic);
        $input->status = self::nullableStringValue($payload, 'status');
        $input->isApproved = self::nullableBoolValue($payload, 'isApproved');
        $input->reviewReason = self::nullableStringValue($payload, 'reviewReason');
        $input->reviewNote = self::nullableStringValue($payload, 'reviewNote');

        return $input;
    }

    public static function fromRequest(Request $request): self
    {
        return self::fromArray(self::extractPayload($request));
    }

    public static function resolveEntityId(mixed $value): ?int
    {
        return self::extractId($value);
    }

    private static function extractPayload(Request $request): array
    {
        if (!empty($request->request->all())) {
            return self::unwrapStudentProfilesPayload($request->request->all());
        }

        $content = trim((string) $request->getContent());
        if ($content === '') {
            return [];
        }

        $decoded = json_decode($content, true);
        return is_array($decoded) ? self::unwrapStudentProfilesPayload($decoded) : [];
    }

    private static function unwrapStudentProfilesPayload(array $payload): array
    {
        if (!array_key_exists('studentProfiles', $payload)) {
            return $payload;
        }

        $studentProfiles = $payload['studentProfiles'];
        unset($payload['studentProfiles']);

        if (is_array($studentProfiles)) {
            return array_replace($payload, $studentProfiles);
        }

        if (is_string($studentProfiles)) {
            $decoded = json_decode($studentProfiles, true);
            if (is_array($decoded)) {
                return array_replace($payload, $decoded);
            }
        }

        return $payload;
    }

    private static function arrayOrJsonValue(array $payload, string $key): ?array
    {
        if (!array_key_exists($key, $payload) || $payload[$key] === null || $payload[$key] === '') {
            return null;
        }

        if (is_array($payload[$key])) {
            return $payload[$key];
        }

        if (is_string($payload[$key])) {
            $decoded = json_decode($payload[$key], true);

            return is_array($decoded) ? $decoded : null;
        }

        return null;
    }

    private static function intValue(array $payload, string $key): ?int
    {
        if (!isset($payload[$key]) || $payload[$key] === '') {
            return null;
        }

        return self::extractId($payload[$key]);
    }

    private static function nullableIntValue(array $payload, string $key): ?int
    {
        if (!array_key_exists($key, $payload) || $payload[$key] === null || $payload[$key] === '') {
            return null;
        }

        return self::extractId($payload[$key]);
    }

    private static function stringValue(array $payload, string $key): ?string
    {
        if (!isset($payload[$key]) || $payload[$key] === '') {
            return null;
        }

        return is_string($payload[$key]) ? $payload[$key] : null;
    }

    private static function nullableStringValue(array $payload, string $key): ?string
    {
        if (!array_key_exists($key, $payload) || $payload[$key] === null) {
            return null;
        }

        if (is_string($payload[$key])) {
            return $payload[$key];
        }

        return null;
    }

    private static function nullableMixedValue(array $payload, string $key): mixed
    {
        if (!array_key_exists($key, $payload) || $payload[$key] === null) {
            return null;
        }

        if (is_array($payload[$key])) {
            return $payload[$key];
        }

        if (is_string($payload[$key])) {
            $decoded = json_decode($payload[$key], true);
            if (is_array($decoded)) {
                return $decoded;
            }

            return $payload[$key];
        }

        return null;
    }

    private static function nullableBoolValue(array $payload, string $key): ?bool
    {
        if (!array_key_exists($key, $payload) || $payload[$key] === null) {
            return null;
        }

        if (is_bool($payload[$key])) {
            return $payload[$key];
        }

        if (is_int($payload[$key])) {
            return $payload[$key] === 1;
        }

        if (is_string($payload[$key])) {
            return filter_var($payload[$key], FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) ?? null;
        }

        return null;
    }

    private static function extractId(mixed $value): ?int
    {
        if (is_array($value) && array_key_exists('id', $value)) {
            $value = $value['id'];
        }

        if (is_int($value) || (is_string($value) && ctype_digit($value))) {
            return (int) $value;
        }

        if (is_string($value) && preg_match('/\/(\d+)$/', $value, $matches) === 1) {
            return (int) $matches[1];
        }

        return null;
    }

    private static function mixedStringValue(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        if (!is_scalar($value)) {
            return null;
        }

        $normalized = trim((string) $value);

        return $normalized !== '' ? $normalized : null;
    }

    /**
     * @param array<string, mixed>|null $personal
     * @param array<string, mixed>|null $academic
     * @param array<string, mixed> $bioPersonal
     * @param array<string, mixed> $bioAcademic
     * @return array<string, string>|null
     */
    private static function extractAddress(array $payload, ?array $personal, ?array $academic, array $bioPersonal, array $bioAcademic): ?array
    {
        $address = self::arrayOrJsonValue($payload, 'address');
        $city = self::firstNonEmptyString([
            self::mixedStringValue($address['city'] ?? null),
            self::nullableStringValue($payload, 'city'),
            self::mixedStringValue($personal['city'] ?? null),
            self::mixedStringValue($academic['city'] ?? null),
            self::mixedStringValue($bioPersonal['city'] ?? null),
            self::mixedStringValue($bioAcademic['city'] ?? null),
        ]);
        $state = self::firstNonEmptyString([
            self::mixedStringValue($address['state'] ?? null),
            self::nullableStringValue($payload, 'state'),
            self::mixedStringValue($personal['state'] ?? null),
            self::mixedStringValue($academic['state'] ?? null),
            self::mixedStringValue($bioPersonal['state'] ?? null),
            self::mixedStringValue($bioAcademic['state'] ?? null),
        ]);
        $country = self::firstNonEmptyString([
            self::mixedStringValue($address['country'] ?? null),
            self::nullableStringValue($payload, 'country'),
            self::mixedStringValue($personal['country'] ?? null),
            self::mixedStringValue($academic['country'] ?? null),
            self::mixedStringValue($bioPersonal['country'] ?? null),
            self::mixedStringValue($bioAcademic['country'] ?? null),
        ]);

        if ($city === null && $state === null && $country === null) {
            return null;
        }

        return array_filter([
            'city' => $city,
            'state' => $state,
            'country' => $country,
        ], static fn (mixed $value): bool => is_string($value) && $value !== '');
    }

    /**
     * @param array<int, string|null> $values
     */
    private static function firstNonEmptyString(array $values): ?string
    {
        foreach ($values as $value) {
            if ($value !== null && trim($value) !== '') {
                return $value;
            }
        }

        return null;
    }
}
