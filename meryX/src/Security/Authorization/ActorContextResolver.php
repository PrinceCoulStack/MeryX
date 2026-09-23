<?php

namespace App\Security\Authorization;

use App\Entity\Company;
use App\Entity\StudentProfile;
use App\Entity\User;
use App\Repository\CompanyRepository;
use App\Repository\StudentProfileRepository;

class ActorContextResolver
{
    /**
     * @var array<string, array<int, string>>
     */
    private const ROLE_EQUIVALENCE = [
        'ROLE_ADMIN' => ['ROLE_ADMIN', 'ADMIN', 'ROLE_SUPER_ADMIN', 'SUPER_ADMIN'],
        'ADMIN' => ['ROLE_ADMIN', 'ADMIN', 'ROLE_SUPER_ADMIN', 'SUPER_ADMIN'],
        'ROLE_SUPER_ADMIN' => ['ROLE_SUPER_ADMIN', 'SUPER_ADMIN'],
        'SUPER_ADMIN' => ['ROLE_SUPER_ADMIN', 'SUPER_ADMIN'],
    ];

    public function __construct(
        private readonly StudentProfileRepository $studentProfileRepository,
        private readonly CompanyRepository $companyRepository,
    ) {
    }

    public function hasRole(User $actor, string $targetRole): bool
    {
        $target = strtoupper($targetRole);
        $aliases = [$target, str_replace('ROLE_', '', $target)];

        if (array_key_exists($target, self::ROLE_EQUIVALENCE)) {
            $aliases = array_values(array_unique(array_merge($aliases, self::ROLE_EQUIVALENCE[$target])));
        }

        foreach ($actor->getRoles() as $role) {
            $normalized = strtoupper((string) $role);
            if (in_array($normalized, $aliases, true) || in_array(str_replace('ROLE_', '', $normalized), $aliases, true)) {
                return true;
            }
        }

        return false;
    }

    public function resolveStudentProfile(User $actor): ?StudentProfile
    {
        return $this->studentProfileRepository->findOneBy(['userId' => $actor]);
    }

    public function resolveCompany(User $actor): ?Company
    {
        return $this->companyRepository->findOneBy(['userId' => $actor]);
    }

    public function isStudentApproved(StudentProfile $student): bool
    {
        return $student->isApproved() === true && strtolower((string) $student->getStatus()) === 'approved';
    }
}
