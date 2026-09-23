<?php

namespace App\Security;

use App\Entity\Company;
use App\Entity\StudentProfile;
use App\Entity\University;
use App\Entity\User;
use App\Repository\CompanyRepository;
use App\Repository\StudentProfileRepository;
use App\Repository\UniversityRepository;

class AuthenticatedUserPayloadBuilder
{
    public function __construct(
        private readonly LoginRedirectResolver $redirectResolver,
        private readonly ?CompanyRepository $companyRepository = null,
        private readonly ?UniversityRepository $universityRepository = null,
        private readonly ?StudentProfileRepository $studentProfileRepository = null
    )
    {
    }

    /**
     * @return array<string, mixed>
     */
    public function build(User $user): array
    {
        $userType = $user->getUserTypeId();
        $redirect = $this->redirectResolver->resolveForUser($user);

        return [
            'id' => $user->getId(),
            'email' => $user->getEmail(),
            'roles' => $redirect['roles'],
            'role' => $redirect['role'],
            'interface' => $redirect['interface'],
            'interfaceKey' => $redirect['interfaceKey'],
            'redirectTarget' => $redirect['redirectTarget'],
            'profile' => $this->buildConnectedProfile($user, (string) $redirect['interfaceKey']),
            'userTypeId' => $userType ? [
                'id' => $userType->getId(),
                'name' => $userType->getName(),
                'role' => $userType->getRoleName(),
                'permission' => $userType->getPermission(),
            ] : null,
            'permissions' => $redirect['permissions'],
        ];
    }

    /**
     * @return array<string, mixed>|null
     */
    private function buildConnectedProfile(User $user, string $interfaceKey): ?array
    {
        if ($interfaceKey === 'student' && $this->studentProfileRepository !== null) {
            $studentProfile = $this->studentProfileRepository->findOneBy(['userId' => $user]);

            return $studentProfile instanceof StudentProfile ? [
                'id' => $studentProfile->getId(),
                'fullName' => $studentProfile->getFullName(),
                'status' => $studentProfile->getStatus(),
                'isApproved' => $studentProfile->isApproved(),
                'university' => $studentProfile->getUniversityId() ? [
                    'id' => $studentProfile->getUniversityId()?->getId(),
                    'name' => $studentProfile->getUniversityId()?->getName(),
                ] : null,
            ] : null;
        }

        if ($interfaceKey === 'company' && $this->companyRepository !== null) {
            $company = $this->companyRepository->findOneBy(['userId' => $user]);

            return $company instanceof Company ? [
                'id' => $company->getId(),
                'name' => $company->getName(),
                'status' => $company->getStatus(),
                'isApproved' => $company->isApproved(),
                'email' => $company->getEmail(),
            ] : null;
        }

        if ($interfaceKey === 'university' && $this->universityRepository !== null) {
            $university = $this->universityRepository->findOneBy(['userId' => $user]);

            return $university instanceof University ? [
                'id' => $university->getId(),
                'name' => $university->getName(),
                'status' => $university->getStatus(),
                'isApproved' => $university->isApproved(),
                'email' => $university->getEmail(),
            ] : null;
        }

        return [
            'id' => $user->getId(),
            'email' => $user->getEmail(),
            'status' => $user->getStatus(),
            'isActived' => $user->isActived(),
        ];
    }
}
