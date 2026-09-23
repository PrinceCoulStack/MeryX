<?php

namespace App\Security\Voter;

use App\Entity\StudentProfile;
use App\Entity\User;
use App\Security\StudentProfileAccessService;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class StudentProfileVoter extends Voter
{
    public const VIEW = 'STUDENT_PROFILE_VIEW';
    public const EDIT = 'STUDENT_PROFILE_EDIT';
    public const DELETE = 'STUDENT_PROFILE_DELETE';

    public function __construct(private readonly StudentProfileAccessService $accessService)
    {
    }

    protected function supports(string $attribute, mixed $subject): bool
    {
        return in_array($attribute, [self::VIEW, self::EDIT, self::DELETE], true)
            && $subject instanceof StudentProfile;
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();
        if (!$user instanceof User) {
            return false;
        }

        /** @var StudentProfile $profile */
        $profile = $subject;

        return match ($attribute) {
            self::VIEW => $this->accessService->canViewProfile($user, $profile),
            self::EDIT => $this->accessService->canEditProfile($user, $profile),
            self::DELETE => $this->accessService->canDeleteProfile($user, $profile),
            default => false,
        };
    }
}
