<?php

namespace App\Service\Messaging;

use App\Entity\Conversation;
use App\Entity\StudentProfile;
use App\Entity\User;
use App\Repository\StudentProfileRepository;
use App\Security\StudentProfileAccessService;

final class UniversityStudentConversationPolicy
{
    public function __construct(
        private readonly StudentProfileAccessService $accessService,
        private readonly StudentProfileRepository $studentProfileRepository,
    ) {
    }

    public function canCreateConversation(User $actor, User $student): array
    {
        if (!$this->accessService->hasRole($actor, 'ROLE_UNIVERSITY') && !$this->accessService->hasRole($actor, 'ROLE_ADMIN')) {
            return ['allowed' => false, 'reason' => 'role_not_authorized'];
        }

        $studentProfile = $this->resolveStudentProfile($student);
        if ($studentProfile === null) {
            return ['allowed' => false, 'reason' => 'student_profile_missing'];
        }

        if ($this->accessService->hasRole($actor, 'ROLE_UNIVERSITY')) {
            $actorUniversity = $this->accessService->resolveUniversityForActor($actor);
            if ($actorUniversity === null || $studentProfile->getUniversityId()?->getId() !== $actorUniversity->getId()) {
                return ['allowed' => false, 'reason' => 'university_mismatch'];
            }
        }

        $status = strtolower((string) $studentProfile->getStatus());
        if (in_array($status, ['pending', 'rejected'], true)) {
            return ['allowed' => true, 'reason' => 'restricted_by_status', 'readOnly' => true];
        }

        return ['allowed' => true, 'reason' => 'allowed', 'readOnly' => false];
    }

    public function canSendMessage(User $actor, User $student, ?string $messageText = null): array
    {
        $result = $this->canCreateConversation($actor, $student);
        if (!$result['allowed']) {
            return $result;
        }

        if (($result['readOnly'] ?? false) === true) {
            return ['allowed' => false, 'reason' => 'student_status_restricted', 'readOnly' => true];
        }

        if ($messageText !== null && trim($messageText) === '') {
            return ['allowed' => false, 'reason' => 'empty_message'];
        }

        return ['allowed' => true, 'reason' => 'allowed', 'readOnly' => false];
    }

    public function canAuditConversation(User $actor, Conversation $conversation): bool
    {
        if ($this->accessService->hasRole($actor, 'ROLE_ADMIN')) {
            return true;
        }

        if (!$this->accessService->hasRole($actor, 'ROLE_UNIVERSITY')) {
            return false;
        }

        $actorUniversity = $this->accessService->resolveUniversityForActor($actor);
        if ($actorUniversity === null) {
            return false;
        }

        foreach ($conversation->getParticipants() as $participant) {
            $student = $participant->getUser();
            if ($student === null) {
                continue;
            }

            $profile = $this->resolveStudentProfile($student);
            if ($profile !== null && $profile->getUniversityId()?->getId() === $actorUniversity->getId()) {
                return true;
            }
        }

        return false;
    }

    public function applyAcademicContext(Conversation $conversation, StudentProfile $studentProfile): Conversation
    {
        $conversation->setProgram($studentProfile->getProgram());
        $conversation->setFaculty($studentProfile->getFaculty());
        $conversation->setInternshipCycle($studentProfile->getInternshipCycle());
        $conversation->setAcademicYear($studentProfile->getAcademicYear());

        return $conversation;
    }

    public function markInterventionRequired(Conversation $conversation, ?string $reason = null): Conversation
    {
        $conversation->setInterventionRequired(true);
        $conversation->setModeratorVisible(true);
        $conversation->setModeratorNote($reason ?? 'Escalated for university intervention');

        return $conversation;
    }

    public function requiresIntervention(User $actor, Conversation $conversation, ?StudentProfile $studentProfile = null): bool
    {
        if ($this->accessService->hasRole($actor, 'ROLE_ADMIN')) {
            return false;
        }

        $studentProfile ??= $this->resolveStudentProfileFromConversation($conversation);
        if ($studentProfile === null) {
            return false;
        }

        $status = strtolower((string) $studentProfile->getStatus());
        return in_array($status, ['pending', 'rejected'], true)
            || $conversation->isInterventionRequired();
    }

    public function buildRestrictedTemplate(StudentProfile $studentProfile): array
    {
        $status = strtolower((string) $studentProfile->getStatus());

        return match ($status) {
            'pending' => ['template' => 'Your registration is pending university review. Please wait for approval before continuing.', 'mode' => 'read_only'],
            'rejected' => ['template' => 'Your university onboarding is currently restricted. Please contact the admissions office for guidance.', 'mode' => 'read_only'],
            default => ['template' => '', 'mode' => 'standard'],
        };
    }

    public function notificationTargetRole(): string
    {
        return 'ROLE_UNIVERSITY_ADMIN';
    }

    private function resolveStudentProfile(User $student): ?StudentProfile
    {
        return $this->studentProfileRepository->findOneBy(['userId' => $student]);
    }

    private function resolveStudentProfileFromConversation(Conversation $conversation): ?StudentProfile
    {
        foreach ($conversation->getParticipants() as $participant) {
            $user = $participant->getUser();
            if ($user === null) {
                continue;
            }

            $profile = $this->resolveStudentProfile($user);
            if ($profile instanceof StudentProfile) {
                return $profile;
            }
        }

        return null;
    }
}
