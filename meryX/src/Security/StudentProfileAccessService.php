<?php

namespace App\Security;

use App\Entity\StudentDocuments;
use App\Entity\StudentProfile;
use App\Entity\University;
use App\Entity\User;
use App\Repository\UniversityRepository;

class StudentProfileAccessService
{
    public function __construct(private readonly UniversityRepository $universityRepository)
    {
    }

    public function canViewProfile(User $actor, StudentProfile $profile): bool
    {
        if ($this->hasRole($actor, 'ROLE_ADMIN')) {
            return true;
        }

        if ($this->hasRole($actor, 'ROLE_UNIVERSITY')) {
            return $this->isInActorsUniversityScope($actor, $profile);
        }

        if ($this->hasRole($actor, 'ROLE_STUDENT')) {
            // students may always view their own profile, even while pending approval
            return $profile->getUserId()?->getId() === $actor->getId();
        }

        return false;
    }

    public function canEditProfile(User $actor, StudentProfile $profile): bool
    {
        if ($this->hasRole($actor, 'ROLE_ADMIN')) {
            return true;
        }

        if ($this->hasRole($actor, 'ROLE_UNIVERSITY')) {
            return $this->isInActorsUniversityScope($actor, $profile);
        }

        if ($this->hasRole($actor, 'ROLE_STUDENT')) {
            return $profile->getUserId()?->getId() === $actor->getId()
                && $profile->isApproved() === true
                && strtolower((string) $profile->getStatus()) === 'approved';
        }

        return false;
    }

    public function canDeleteProfile(User $actor, StudentProfile $profile): bool
    {
        if ($this->hasRole($actor, 'ROLE_ADMIN')) {
            return true;
        }

        if ($this->hasRole($actor, 'ROLE_UNIVERSITY')) {
            return $this->isInActorsUniversityScope($actor, $profile);
        }

        return false;
    }

    /**
     * @return string|null a human-readable reason the edit was denied, or null if it's actually allowed
     */
    public function explainEditDenial(User $actor, StudentProfile $profile): ?string
    {
        if ($this->canEditProfile($actor, $profile)) {
            return null;
        }

        if ($this->hasRole($actor, 'ROLE_UNIVERSITY')) {
            $actorUniversity = $this->resolveUniversityForActor($actor);
            if ($actorUniversity === null) {
                return 'Your account is not linked to a university.';
            }

            return 'This student belongs to a different university.';
        }

        if ($this->hasRole($actor, 'ROLE_STUDENT')) {
            if ($profile->getUserId()?->getId() !== $actor->getId()) {
                return 'You can only edit your own profile.';
            }

            return 'Your profile must be approved before it can be edited.';
        }

        return 'Your role is not allowed to edit student profiles.';
    }

    public function canViewDocument(User $actor, StudentDocuments $document): bool
    {
        if ($this->hasRole($actor, 'ROLE_ADMIN')) {
            return true;
        }

        if ($document->isPublic() === true) {
            return true;
        }

        $profile = $document->getStudentProfileId();
        if ($profile === null) {
            return false;
        }

        return $this->canViewProfile($actor, $profile);
    }

    public function canEditDocument(User $actor, StudentDocuments $document): bool
    {
        if ($this->hasRole($actor, 'ROLE_ADMIN')) {
            return true;
        }

        $profile = $document->getStudentProfileId();
        if ($profile === null) {
            return false;
        }

        return $this->canEditProfile($actor, $profile);
    }

    public function hasRole(User $actor, string $targetRole): bool
    {
        $target = strtoupper($targetRole);
        $aliases = [$target, str_replace('ROLE_', '', $target)];

        foreach ($actor->getRoles() as $role) {
            $normalized = strtoupper((string) $role);
            if (in_array($normalized, $aliases, true) || in_array(str_replace('ROLE_', '', $normalized), $aliases, true)) {
                return true;
            }
        }

        return false;
    }

    public function resolveUniversityForActor(User $actor): ?University
    {
        return $this->universityRepository->findOneBy(['userId' => $actor]);
    }

    private function isInActorsUniversityScope(User $actor, StudentProfile $profile): bool
    {
        $actorUniversity = $this->resolveUniversityForActor($actor);
        if ($actorUniversity === null) {
            return false;
        }

        return $profile->getUniversityId()?->getId() === $actorUniversity->getId();
    }
}
