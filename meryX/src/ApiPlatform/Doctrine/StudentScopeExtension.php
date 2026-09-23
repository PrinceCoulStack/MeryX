<?php

namespace App\ApiPlatform\Doctrine;

use ApiPlatform\Doctrine\Orm\Extension\QueryCollectionExtensionInterface;
use ApiPlatform\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use ApiPlatform\Metadata\Operation;
use App\Entity\StudentDocuments;
use App\Entity\StudentProfile;
use App\Entity\User;
use App\Repository\UniversityRepository;
use Doctrine\ORM\QueryBuilder;
use Symfony\Bundle\SecurityBundle\Security;

class StudentScopeExtension implements QueryCollectionExtensionInterface
{
    public function __construct(
        private readonly Security $security,
        private readonly UniversityRepository $universityRepository
    ) {
    }

    public function applyToCollection(QueryBuilder $queryBuilder, QueryNameGeneratorInterface $queryNameGenerator, string $resourceClass, ?Operation $operation = null, array $context = []): void
    {
        $user = $this->security->getUser();
        if (!$user instanceof User) {
            return;
        }

        if ($this->hasRole($user, 'ROLE_ADMIN')) {
            return;
        }

        $rootAlias = $queryBuilder->getRootAliases()[0];

        if ($resourceClass === StudentProfile::class) {
            $this->restrictStudentProfiles($queryBuilder, $queryNameGenerator, $rootAlias, $user);
        }

        if ($resourceClass === StudentDocuments::class) {
            $this->restrictStudentDocuments($queryBuilder, $queryNameGenerator, $rootAlias, $user);
        }
    }

    private function restrictStudentProfiles(QueryBuilder $queryBuilder, QueryNameGeneratorInterface $queryNameGenerator, string $rootAlias, User $user): void
    {
        if ($this->hasRole($user, 'ROLE_STUDENT')) {
            $parameter = $queryNameGenerator->generateParameterName('current_user_id');
            $queryBuilder
                ->andWhere(sprintf('%s.userId = :%s', $rootAlias, $parameter))
                ->setParameter($parameter, $user->getId());
            return;
        }

        if ($this->hasRole($user, 'ROLE_UNIVERSITY')) {
            $university = $this->universityRepository->findOneBy(['userId' => $user]);
            if ($university === null) {
                $queryBuilder->andWhere('1 = 0');
                return;
            }

            $parameter = $queryNameGenerator->generateParameterName('university_id');
            $queryBuilder
                ->andWhere(sprintf('%s.universityId = :%s', $rootAlias, $parameter))
                ->setParameter($parameter, $university->getId());
        }
    }

    private function restrictStudentDocuments(QueryBuilder $queryBuilder, QueryNameGeneratorInterface $queryNameGenerator, string $rootAlias, User $user): void
    {
        $profileAlias = $queryNameGenerator->generateJoinAlias('student_profile');
        $queryBuilder->leftJoin(sprintf('%s.studentProfileId', $rootAlias), $profileAlias);

        if ($this->hasRole($user, 'ROLE_STUDENT')) {
            $parameter = $queryNameGenerator->generateParameterName('current_user_id');
            $queryBuilder
                ->andWhere(sprintf('(%s.userId = :%s OR %s.isPublic = true)', $profileAlias, $parameter, $rootAlias))
                ->setParameter($parameter, $user->getId());
            return;
        }

        if ($this->hasRole($user, 'ROLE_UNIVERSITY')) {
            $university = $this->universityRepository->findOneBy(['userId' => $user]);
            if ($university === null) {
                $queryBuilder->andWhere('1 = 0');
                return;
            }

            $parameter = $queryNameGenerator->generateParameterName('university_id');
            $queryBuilder
                ->andWhere(sprintf('(%s.universityId = :%s OR %s.isPublic = true)', $profileAlias, $parameter, $rootAlias))
                ->setParameter($parameter, $university->getId());
        }
    }

    private function hasRole(User $actor, string $targetRole): bool
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
}
