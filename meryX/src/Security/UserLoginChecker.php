<?php

namespace App\Security;

use App\Entity\User;
use App\Repository\StudentProfileRepository;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAccountStatusException;
use Symfony\Component\Security\Core\User\UserCheckerInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class UserLoginChecker implements UserCheckerInterface
{
    public function __construct(private readonly StudentProfileRepository $studentProfileRepository)
    {
    }

    public function checkPreAuth(UserInterface $user): void
    {
        if (!$user instanceof User) {
            return;
        }

        if ($user->isActived() !== true) {
            throw new CustomUserMessageAccountStatusException('Your account is inactive. Please contact support.');
        }

        $status = strtolower((string) ($user->getStatus() ?? 'active'));
        if (in_array($status, ['inactive', 'blocked', 'suspended'], true)) {
            throw new CustomUserMessageAccountStatusException('Your account is not allowed to sign in.');
        }

        $userType = $user->getUserTypeId();
        if ($userType === null) {
            throw new CustomUserMessageAccountStatusException('Your account role is not configured.');
        }

        if ($userType->isEnabled() !== true || $userType->isDeleted() === true) {
            throw new CustomUserMessageAccountStatusException('Your account role is disabled.');
        }

        $normalizedRoleName = strtoupper(trim((string) $userType->getRoleName()));
        $isStudentRole = in_array($normalizedRoleName, ['STUDENT', 'ROLE_STUDENT'], true);

        if ($isStudentRole) {
            $studentProfile = $this->studentProfileRepository->findOneBy(['userId' => $user]);
            if ($studentProfile === null) {
                throw new CustomUserMessageAccountStatusException('Student profile is not configured yet.');
            }

            $profileStatus = strtolower((string) $studentProfile->getStatus());
            $isApproved = $studentProfile->isApproved() === true || $profileStatus === 'approved';

            if ($isApproved !== true) {
                throw new CustomUserMessageAccountStatusException('Your registration is still pending university approval.');
            }
        }
    }

    public function checkPostAuth(UserInterface $user, ?TokenInterface $token = null): void
    {
        if (!$user instanceof User) {
            return;
        }

        $this->checkPreAuth($user);
    }
}
