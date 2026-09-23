<?php

namespace App\Service\StudentProfile;

use App\Dto\StudentProfileUpsertInput;
use App\Entity\AcademicRecord;
use App\Entity\Address;
use App\Entity\Language;
use App\Entity\Skills;
use App\Entity\StudentProfile;
use App\Entity\StudentProject;
use App\Exception\DomainValidationException;
use App\Repository\UniversityRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;

class StudentProfileUpsertService
{
    private const ALLOWED_GENDERS = ['male', 'female', 'other', 'prefer_not_to_say'];
    private const ALLOWED_LEVELS = ['beginner', 'intermediate', 'advanced', 'expert', 'native'];
    private const DEFAULT_AVATAR_BASE_URL = 'https://ui-avatars.com/api/?background=0D8ABC&color=fff&name=';

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly UserRepository $userRepository,
        private readonly UniversityRepository $universityRepository
    ) {
    }

    public function create(StudentProfileUpsertInput $input): StudentProfile
    {
        $studentProfile = new StudentProfile();
        $this->upsert($studentProfile, $input, true);

        return $studentProfile;
    }

    public function update(StudentProfile $studentProfile, StudentProfileUpsertInput $input): StudentProfile
    {
        $this->upsert($studentProfile, $input, false);

        return $studentProfile;
    }

    private function upsert(StudentProfile $studentProfile, StudentProfileUpsertInput $input, bool $isCreate): void
    {
        $connection = $this->entityManager->getConnection();
        $managesTransaction = !$connection->isTransactionActive();

        if ($managesTransaction) {
            $this->entityManager->beginTransaction();
        }

        try {
            $now = new \DateTimeImmutable();
            $input->skills = $this->normalizeSkillsPayload($input->skills);
            $input->bio = $this->normalizeBioForPersistence($input, $studentProfile);
            $personal = $this->buildPersonalBlock($input);
            $academic = $this->buildAcademicBlock($input);
            $errors = [];

            if (array_key_exists('fullName', $personal)) {
                if (!is_string($personal['fullName']) || trim($personal['fullName']) === '') {
                    $errors['personal.fullName'][] = 'Full name must not be empty';
                }
                $studentProfile->setFullName((string) $personal['fullName']);
            }
            if (array_key_exists('bio', $personal)) {
                $studentProfile->setBio($this->serializeBio($personal['bio']));
            }
            if (array_key_exists('gender', $personal)) {
                if (!in_array(strtolower((string) $personal['gender']), self::ALLOWED_GENDERS, true)) {
                    $errors['personal.gender'][] = 'Gender value is not allowed';
                }
                $studentProfile->setGender((string) $personal['gender']);
            }
            if (array_key_exists('profileUrl', $personal)) {
                $studentProfile->setProfileUrl($personal['profileUrl']);
            }

            if (array_key_exists('gpa', $academic)) {
                if (!$this->isValidGpa((string) $academic['gpa'])) {
                    $errors['academic.gpa'][] = 'GPA must be between 0.00 and 4.00';
                }
                $studentProfile->setGpa((string) $academic['gpa']);
            }
            if (array_key_exists('profileCompletion', $academic)) {
                if (!is_int($academic['profileCompletion']) || $academic['profileCompletion'] < 0 || $academic['profileCompletion'] > 100) {
                    $errors['academic.profileCompletion'][] = 'Profile completion must be in range 0-100';
                }
                $studentProfile->setProfileCompletion($academic['profileCompletion']);
            }
            if (array_key_exists('universityId', $academic)) {
                $university = $this->resolveUniversity($academic['universityId']);
                if ($university === null) {
                    throw new \InvalidArgumentException('Invalid university ID');
                }
                $studentProfile->setUniversityId($university);
            }
            if (array_key_exists('program', $academic)) {
                $program = is_scalar($academic['program']) ? trim((string) $academic['program']) : '';
                $studentProfile->setProgram($program !== '' ? $program : null);
            }
            if (array_key_exists('faculty', $academic)) {
                $faculty = is_scalar($academic['faculty']) ? trim((string) $academic['faculty']) : '';
                $studentProfile->setFaculty($faculty !== '' ? $faculty : null);
            }
            if (array_key_exists('academicYear', $academic)) {
                $academicYear = is_scalar($academic['academicYear']) ? trim((string) $academic['academicYear']) : '';
                $studentProfile->setAcademicYear($academicYear !== '' ? $academicYear : null);
            }
            if (array_key_exists('level', $academic)) {
                $level = is_scalar($academic['level']) ? trim((string) $academic['level']) : '';
                $studentProfile->setInternshipCycle($level !== '' ? $level : null);
            }

            if ($input->userId !== null) {
                $user = $this->userRepository->find($input->userId);
                if ($user === null) {
                    throw new \InvalidArgumentException('Invalid user ID');
                }

                $studentProfile->setUserId($user);
            }

            $user = $studentProfile->getUserId();
            if ($user !== null) {
                if ($input->email !== null && trim($input->email) !== '') {
                    $user->setEmail(trim($input->email));
                }

                if ($input->phone !== null && trim($input->phone) !== '') {
                    $user->setPhone(trim($input->phone));
                }

                if ($input->address !== null) {
                    $address = $user->getAddressId() ?? new Address();

                    if (isset($input->address['city']) && trim((string) $input->address['city']) !== '') {
                        $address->setCity(trim((string) $input->address['city']));
                    }

                    if (isset($input->address['state']) && trim((string) $input->address['state']) !== '') {
                        $address->setState(trim((string) $input->address['state']));
                    }

                    if (isset($input->address['country']) && trim((string) $input->address['country']) !== '') {
                        $address->setCountry(trim((string) $input->address['country']));
                    }

                    if ($address->getCreateAt() === null) {
                        $address->setCreateAt($now);
                    }
                    $address->setUpdatedAt($now);

                    if ($address->getLatitude() === null) {
                        $address->setLatitude('0');
                    }
                    if ($address->getLongitude() === null) {
                        $address->setLongitude('0');
                    }

                    $this->entityManager->persist($address);
                    $user->setAddressId($address);
                }
            }

            if ($isCreate) {
                if ($studentProfile->getFullName() === null || $studentProfile->getGpa() === null || $studentProfile->getGender() === null) {
                    $errors['profile'][] = 'fullName, gpa and gender are required for profile creation';
                }
            }

            $this->ensureRequiredRelations($studentProfile, $errors);
            $this->ensureProfileUrl($studentProfile);

            if ($isCreate) {
                $studentProfile->setCreatedAt($now);
                $this->entityManager->persist($studentProfile);
            }

            $this->validateCollections($input, $errors);
            if ($errors !== []) {
                throw new DomainValidationException($errors);
            }

            $studentProfile->setUpdatedAt($now);

            if ($input->skills !== null) {
                $this->replaceSkills($studentProfile, $input->skills, $now);
            }
            if ($input->languages !== null) {
                $this->replaceLanguages($studentProfile, $input->languages, $now);
            }
            if ($input->projects !== null) {
                $this->replaceProjects($studentProfile, $input->projects, $now);
            }
            if ($input->academicRecords !== null) {
                $this->replaceAcademicRecords($studentProfile, $input->academicRecords, $now);
            }

            $this->entityManager->flush();
            if ($managesTransaction) {
                $this->entityManager->commit();
            }
        } catch (\Throwable $exception) {
            if ($managesTransaction) {
                $this->entityManager->rollback();
            }
            throw $exception;
        }
    }

    /**
     * @param array<string, array<int, string>> $errors
     */
    private function validateCollections(StudentProfileUpsertInput $input, array &$errors): void
    {
        if ($input->skills !== null) {
            foreach ($input->skills as $index => $item) {
                if (is_string($item)) {
                    if (trim($item) === '') {
                        $errors[sprintf('skills.%d.name', $index)][] = 'Skill name is required';
                    }

                    continue;
                }

                if (!is_array($item) || empty($item['name'])) {
                    $errors[sprintf('skills.%d.name', $index)][] = 'Skill name is required';
                    continue;
                }

                if (array_key_exists('level', $item) && $item['level'] !== null && $item['level'] !== '' && !in_array(strtolower((string) $item['level']), self::ALLOWED_LEVELS, true)) {
                    $errors[sprintf('skills.%d.level', $index)][] = 'Skill level is invalid';
                }
            }
        }

        if ($input->languages !== null) {
            foreach ($input->languages as $index => $item) {
                if (!is_array($item) || empty($item['name'])) {
                    $errors[sprintf('languages.%d.name', $index)][] = 'Language name is required';
                    continue;
                }
                if (empty($item['level']) || !in_array(strtolower((string) $item['level']), self::ALLOWED_LEVELS, true)) {
                    $errors[sprintf('languages.%d.level', $index)][] = 'Language level is invalid';
                }
            }
        }

        if ($input->academicRecords !== null) {
            foreach ($input->academicRecords as $index => $item) {
                if (!is_array($item)) {
                    $errors[sprintf('academicRecords.%d', $index)][] = 'Academic record must be an object';
                    continue;
                }

                if (empty($item['institutionName'])) {
                    $errors[sprintf('academicRecords.%d.institutionName', $index)][] = 'Institution name is required';
                }
                if (empty($item['degree'])) {
                    $errors[sprintf('academicRecords.%d.degree', $index)][] = 'Degree is required';
                }
                if (empty($item['startDate']) || $this->parseDate($item['startDate']) === null) {
                    $errors[sprintf('academicRecords.%d.startDate', $index)][] = 'Valid startDate is required';
                }

                $start = isset($item['startDate']) ? $this->parseDate($item['startDate']) : null;
                $end = isset($item['endDate']) ? $this->parseDate($item['endDate']) : null;
                if ($start !== null && $end !== null && $end < $start) {
                    $errors[sprintf('academicRecords.%d.endDate', $index)][] = 'endDate must be after startDate';
                }
            }
        }
    }

    private function buildPersonalBlock(StudentProfileUpsertInput $input): array
    {
        $personal = $input->personal ?? [];
        $bioPayload = $this->extractBioPayload($input->bio);
        $bioPersonal = isset($bioPayload['personal']) && is_array($bioPayload['personal']) ? $bioPayload['personal'] : [];

        if ($input->fullName !== null && !array_key_exists('fullName', $personal)) {
            $personal['fullName'] = $input->fullName;
        }
        if (!array_key_exists('fullName', $personal) && array_key_exists('fullName', $bioPersonal)) {
            $personal['fullName'] = $bioPersonal['fullName'];
        }
        if ($input->bio !== null && !array_key_exists('bio', $personal)) {
            $personal['bio'] = $input->bio;
        }
        if ($input->gender !== null && !array_key_exists('gender', $personal)) {
            $personal['gender'] = $input->gender;
        }
        if (!array_key_exists('gender', $personal) && array_key_exists('gender', $bioPersonal)) {
            $personal['gender'] = $bioPersonal['gender'];
        }
        if ($input->profileUrl !== null && !array_key_exists('profileUrl', $personal)) {
            $personal['profileUrl'] = $input->profileUrl;
        }
        if (!array_key_exists('profileUrl', $personal) && array_key_exists('profileUrl', $bioPersonal)) {
            $personal['profileUrl'] = $bioPersonal['profileUrl'];
        }
        if ($input->email !== null && !array_key_exists('email', $personal)) {
            $personal['email'] = $input->email;
        }
        if (!array_key_exists('email', $personal) && array_key_exists('email', $bioPersonal)) {
            $personal['email'] = $bioPersonal['email'];
        }
        if ($input->phone !== null && !array_key_exists('phone', $personal)) {
            $personal['phone'] = $input->phone;
        }
        if (!array_key_exists('phone', $personal) && array_key_exists('phone', $bioPersonal)) {
            $personal['phone'] = $bioPersonal['phone'];
        }
        if ($input->nationality !== null && !array_key_exists('nationality', $personal)) {
            $personal['nationality'] = $input->nationality;
        }
        if (!array_key_exists('nationality', $personal) && array_key_exists('nationality', $bioPersonal)) {
            $personal['nationality'] = $bioPersonal['nationality'];
        }
        if ($input->summary !== null && !array_key_exists('summary', $personal)) {
            $personal['summary'] = $input->summary;
        }
        if (!array_key_exists('summary', $personal) && array_key_exists('summary', $bioPersonal)) {
            $personal['summary'] = $bioPersonal['summary'];
        }

        if ($input->address !== null) {
            foreach (['city', 'state', 'country'] as $field) {
                if (isset($input->address[$field]) && trim((string) $input->address[$field]) !== '') {
                    $personal[$field] = trim((string) $input->address[$field]);
                }
            }
        }

        return $personal;
    }

    private function buildAcademicBlock(StudentProfileUpsertInput $input): array
    {
        $academic = $input->academic ?? [];
        $bioPayload = $this->extractBioPayload($input->bio);
        $bioAcademic = isset($bioPayload['academic']) && is_array($bioPayload['academic']) ? $bioPayload['academic'] : [];

        if ($input->gpa !== null && !array_key_exists('gpa', $academic)) {
            $academic['gpa'] = $input->gpa;
        }
        if (!array_key_exists('gpa', $academic) && array_key_exists('gpa', $bioAcademic)) {
            $academic['gpa'] = $bioAcademic['gpa'];
        }
        if ($input->profileCompletion !== null && !array_key_exists('profileCompletion', $academic)) {
            $academic['profileCompletion'] = $input->profileCompletion;
        }
        if (!array_key_exists('profileCompletion', $academic) && array_key_exists('profileCompletion', $bioAcademic)) {
            $academic['profileCompletion'] = $bioAcademic['profileCompletion'];
        }
        if ($input->universityId !== null && !array_key_exists('universityId', $academic)) {
            $academic['universityId'] = $input->universityId;
        }
        if (!array_key_exists('universityId', $academic) && array_key_exists('universityId', $bioAcademic)) {
            $academic['universityId'] = $bioAcademic['universityId'];
        }
        if ($input->program !== null && !array_key_exists('program', $academic)) {
            $academic['program'] = $input->program;
        }
        if (!array_key_exists('program', $academic) && array_key_exists('program', $bioAcademic)) {
            $academic['program'] = $bioAcademic['program'];
        }
        if ($input->level !== null && !array_key_exists('level', $academic)) {
            $academic['level'] = $input->level;
        }
        if (!array_key_exists('level', $academic) && array_key_exists('level', $bioAcademic)) {
            $academic['level'] = $bioAcademic['level'];
        }
        if ($input->academicYear !== null && !array_key_exists('academicYear', $academic)) {
            $academic['academicYear'] = $input->academicYear;
        }
        if (!array_key_exists('academicYear', $academic) && array_key_exists('academicYear', $bioAcademic)) {
            $academic['academicYear'] = $bioAcademic['academicYear'];
        }
        if ($input->faculty !== null && !array_key_exists('faculty', $academic)) {
            $academic['faculty'] = $input->faculty;
        }
        if (!array_key_exists('faculty', $academic) && array_key_exists('faculty', $bioAcademic)) {
            $academic['faculty'] = $bioAcademic['faculty'];
        }
        if ($input->department !== null && !array_key_exists('department', $academic)) {
            $academic['department'] = $input->department;
        }
        if (!array_key_exists('department', $academic) && array_key_exists('department', $bioAcademic)) {
            $academic['department'] = $bioAcademic['department'];
        }
        if ($input->studentId !== null && !array_key_exists('studentId', $academic)) {
            $academic['studentId'] = $input->studentId;
        }
        if (!array_key_exists('studentId', $academic) && array_key_exists('studentId', $bioAcademic)) {
            $academic['studentId'] = $bioAcademic['studentId'];
        }

        return $academic;
    }

    /**
     * @param array<int, mixed>|null $skills
     * @return array<int, array{name: string, level?: string}>|null
     */
    private function normalizeSkillsPayload(?array $skills): ?array
    {
        if ($skills === null) {
            return null;
        }

        $normalized = [];
        foreach ($skills as $item) {
            if (is_string($item)) {
                $name = trim($item);
                if ($name !== '') {
                    $normalized[] = ['name' => $name];
                }

                continue;
            }

            if (is_array($item) && isset($item['name']) && is_string($item['name']) && trim($item['name']) !== '') {
                $row = ['name' => trim($item['name'])];
                if (isset($item['level']) && is_string($item['level']) && trim($item['level']) !== '') {
                    $row['level'] = trim($item['level']);
                }
                if (array_key_exists('isEnabled', $item)) {
                    $row['isEnabled'] = (bool) $item['isEnabled'];
                }
                if (array_key_exists('isDeleted', $item)) {
                    $row['isDeleted'] = (bool) $item['isDeleted'];
                }
                $normalized[] = $row;
            }
        }

        return $normalized;
    }

    /**
     * @return array<string, mixed>
     */
    private function normalizeBioForPersistence(StudentProfileUpsertInput $input, StudentProfile $studentProfile): array
    {
        $existingBio = $this->extractBioPayload($studentProfile->getBio());
        $incomingBio = $this->extractBioPayload($input->bio);

        $personal = isset($existingBio['personal']) && is_array($existingBio['personal']) ? $existingBio['personal'] : [];
        $academic = isset($existingBio['academic']) && is_array($existingBio['academic']) ? $existingBio['academic'] : [];
        $radar = isset($existingBio['radar']) && is_array($existingBio['radar']) ? $existingBio['radar'] : [];

        if (isset($incomingBio['personal']) && is_array($incomingBio['personal'])) {
            $personal = array_replace($personal, $incomingBio['personal']);
        }
        if (isset($incomingBio['academic']) && is_array($incomingBio['academic'])) {
            $academic = array_replace($academic, $incomingBio['academic']);
        }
        if (isset($incomingBio['radar']) && is_array($incomingBio['radar'])) {
            $radar = array_replace($radar, $incomingBio['radar']);
        }

        if ($input->fullName !== null && trim($input->fullName) !== '') {
            $personal['fullName'] = $input->fullName;
        }
        if ($input->gender !== null && trim($input->gender) !== '') {
            $personal['gender'] = $input->gender;
        }
        if ($input->profileUrl !== null && trim($input->profileUrl) !== '') {
            $personal['profileUrl'] = $input->profileUrl;
        }

        if ($input->gpa !== null && trim($input->gpa) !== '') {
            $academic['gpa'] = $input->gpa;
        }
        if ($input->profileCompletion !== null) {
            $academic['profileCompletion'] = $input->profileCompletion;
        }
        if ($input->universityId !== null) {
            $academic['universityId'] = $input->universityId;
        }
        if ($input->program !== null && trim($input->program) !== '') {
            $academic['program'] = $input->program;
        }
        if ($input->level !== null && trim($input->level) !== '') {
            $academic['level'] = $input->level;
        }
        if ($input->academicYear !== null && trim($input->academicYear) !== '') {
            $academic['academicYear'] = $input->academicYear;
        }
        if ($input->faculty !== null && trim($input->faculty) !== '') {
            $academic['faculty'] = $input->faculty;
        }
        if ($input->department !== null && trim($input->department) !== '') {
            $academic['department'] = $input->department;
        }
        if ($input->studentId !== null && trim($input->studentId) !== '') {
            $academic['studentId'] = $input->studentId;
        }
        if ($input->nationality !== null && trim($input->nationality) !== '') {
            $personal['nationality'] = $input->nationality;
        }
        if ($input->summary !== null && trim($input->summary) !== '') {
            $personal['summary'] = $input->summary;
        }
        if ($input->email !== null && trim($input->email) !== '') {
            $personal['email'] = $input->email;
        }
        if ($input->phone !== null && trim($input->phone) !== '') {
            $personal['phone'] = $input->phone;
        }
        if ($input->address !== null) {
            foreach (['city', 'state', 'country'] as $field) {
                if (isset($input->address[$field]) && trim((string) $input->address[$field]) !== '') {
                    $personal[$field] = trim((string) $input->address[$field]);
                    $academic[$field] = trim((string) $input->address[$field]);
                }
            }
        }

        foreach (['academic', 'certificate', 'numerique', 'langue', 'stage'] as $category) {
            if (!isset($radar[$category]) || !is_array($radar[$category])) {
                $radar[$category] = [];
            }
            $radar[$category] = array_values(array_filter($radar[$category], static fn (mixed $entry): bool => is_array($entry)));
        }

        // preserve any other top-level bio keys (e.g. academicRecords, projects, languages,
        // verificationData) so wizard data survives even when not mapped to a relation/column
        $reserved = ['personal', 'academic', 'radar'];
        $extra = array_diff_key($existingBio, array_flip($reserved));
        $extra = array_replace($extra, array_diff_key($incomingBio, array_flip($reserved)));

        return array_merge($extra, [
            'personal' => $personal,
            'academic' => $academic,
            'radar' => $radar,
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function extractBioPayload(mixed $bio): array
    {
        if (is_array($bio)) {
            return $bio;
        }

        if (is_string($bio)) {
            $decoded = json_decode($bio, true);
            return is_array($decoded) ? $decoded : [];
        }

        return [];
    }

    private function serializeBio(mixed $bio): ?string
    {
        if ($bio === null) {
            return null;
        }

        if (is_string($bio)) {
            return $bio;
        }

        if (is_array($bio)) {
            $encoded = json_encode($bio, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

            return is_string($encoded) ? $encoded : null;
        }

        return null;
    }

    private function replaceSkills(StudentProfile $studentProfile, array $skillPayload, \DateTimeImmutable $now): void
    {
        foreach ($studentProfile->getSkill()->toArray() as $existingSkill) {
            $studentProfile->removeSkill($existingSkill);
        }

        $legacySkills = [];
        foreach ($skillPayload as $row) {
            if (!is_array($row) || empty($row['name'])) {
                continue;
            }

            $legacySkills[] = (string) $row['name'];

            if (empty($row['level'])) {
                continue;
            }

            $skill = new Skills();
            $skill->setName((string) $row['name']);
            $skill->setLevel((string) $row['level']);
            $skill->setIsEnabled(isset($row['isEnabled']) ? (bool) $row['isEnabled'] : true);
            $skill->setIsDeleted(isset($row['isDeleted']) ? (bool) $row['isDeleted'] : false);
            $skill->setCreatedAt($now);
            $skill->setUpdatedAt($now);
            $studentProfile->addSkill($skill);
        }

        $studentProfile->setSkills($legacySkills);
    }

    private function replaceLanguages(StudentProfile $studentProfile, array $languagePayload, \DateTimeImmutable $now): void
    {
        foreach ($studentProfile->getLanguages()->toArray() as $existingLanguage) {
            $studentProfile->removeLanguage($existingLanguage);
        }

        foreach ($languagePayload as $row) {
            if (!is_array($row) || empty($row['name']) || empty($row['level'])) {
                continue;
            }

            $language = new Language();
            $language->setName((string) $row['name']);
            $language->setLevel((string) $row['level']);
            $language->setCreatedAt($now);
            $language->setUpdatedAt($now);
            $studentProfile->addLanguage($language);
        }
    }

    private function replaceProjects(StudentProfile $studentProfile, array $projectPayload, \DateTimeImmutable $now): void
    {
        foreach ($studentProfile->getStudentProjects()->toArray() as $existingProject) {
            $studentProfile->removeStudentProject($existingProject);
        }

        foreach ($projectPayload as $row) {
            if (!is_array($row) || empty($row['title'])) {
                continue;
            }

            $project = new StudentProject();
            $project->setTitle((string) $row['title']);
            $project->setDescription(isset($row['description']) ? (string) $row['description'] : null);
            $project->setRole(isset($row['role']) ? (string) $row['role'] : null);
            $project->setTechnologies(isset($row['technologies']) && is_array($row['technologies']) ? $row['technologies'] : []);
            $project->setProjectUrl(isset($row['projectUrl']) ? (string) $row['projectUrl'] : null);
            $project->setStartedAt($this->parseDate(isset($row['startedAt']) ? $row['startedAt'] : null));
            $project->setEndedAt($this->parseDate(isset($row['endedAt']) ? $row['endedAt'] : null));
            $project->setCreatedAt($now);
            $project->setUpdatedAt($now);

            $studentProfile->addStudentProject($project);
        }
    }

    private function replaceAcademicRecords(StudentProfile $studentProfile, array $recordPayload, \DateTimeImmutable $now): void
    {
        foreach ($studentProfile->getAcademicRecords()->toArray() as $existingRecord) {
            $studentProfile->removeAcademicRecord($existingRecord);
        }

        foreach ($recordPayload as $row) {
            if (!is_array($row) || empty($row['institutionName']) || empty($row['degree']) || empty($row['startDate'])) {
                continue;
            }

            $record = new AcademicRecord();
            $record->setInstitutionName((string) $row['institutionName']);
            $record->setDegree((string) $row['degree']);
            $record->setFieldOfStudy(isset($row['fieldOfStudy']) ? (string) $row['fieldOfStudy'] : null);
            $record->setStartDate($this->parseDate($row['startDate']) ?? $now);
            $record->setEndDate($this->parseDate(isset($row['endDate']) ? $row['endDate'] : null));
            $record->setGpa(isset($row['gpa']) ? (string) $row['gpa'] : null);
            $record->setDescription(isset($row['description']) ? (string) $row['description'] : null);
            $record->setCreatedAt($now);
            $record->setUpdatedAt($now);

            $studentProfile->addAcademicRecord($record);
        }
    }

    private function resolveUniversity(mixed $identifier): ?object
    {
        $id = $this->resolveEntityId($identifier);

        return $id === null ? null : $this->universityRepository->find($id);
    }

    private function resolveEntityId(mixed $identifier): ?int
    {
        if ($identifier === null || $identifier === '') {
            return null;
        }

        if (is_int($identifier) || (is_string($identifier) && ctype_digit($identifier))) {
            return (int) $identifier;
        }

        if (is_string($identifier) && preg_match('/\/(\d+)$/', $identifier, $matches) === 1) {
            return (int) $matches[1];
        }

        return null;
    }

    /**
     * @param array<string, array<int, string>> $errors
     */
    private function ensureRequiredRelations(StudentProfile $studentProfile, array &$errors): void
    {
        if ($studentProfile->getUserId() === null) {
            $errors['userId'][] = 'User relation is required';
        }

        if ($studentProfile->getUniversityId() === null) {
            $errors['academic.universityId'][] = 'University relation is required';
        }
    }

    private function ensureProfileUrl(StudentProfile $studentProfile): void
    {
        $currentProfileUrl = $studentProfile->getProfileUrl();
        if ($currentProfileUrl !== null && trim($currentProfileUrl) !== '') {
            return;
        }

        $fullName = trim((string) $studentProfile->getFullName());
        if ($fullName === '') {
            $fullName = (string) $studentProfile->getUserId()?->getEmail();
        }

        if ($fullName === '') {
            $fullName = 'Student';
        }

        $studentProfile->setProfileUrl(self::DEFAULT_AVATAR_BASE_URL . rawurlencode($fullName));
    }

    private function parseDate(mixed $value): ?\DateTimeImmutable
    {
        if (!is_string($value) || trim($value) === '') {
            return null;
        }

        try {
            return new \DateTimeImmutable($value);
        } catch (\Exception) {
            return null;
        }
    }

    private function isValidGpa(string $gpa): bool
    {
        return preg_match('/^(?:[0-3](?:\.\d{1,2})?|4(?:\.0{1,2})?)$/', $gpa) === 1;
    }
}
