<?php

namespace App\Repository;

use App\Entity\Candidature;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Candidature>
 */
class CandidatureRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Candidature::class);
    }

    /**
     * @param array<string, mixed> $filters
     * @return array{0: array<int, Candidature>, 1: int}
     */
    public function search(array $filters, int $page, int $limit): array
    {
        $qb = $this->createQueryBuilder('c')
            ->distinct()
            ->leftJoin('c.opportunity', 'o')->addSelect('o')
            ->leftJoin('o.companyId', 'company')->addSelect('company')
            ->leftJoin('c.student', 'student')->addSelect('student')
            ->orderBy('c.appliedDate', 'DESC');

        if (isset($filters['studentId']) && is_int($filters['studentId'])) {
            $qb->andWhere('student.id = :studentId')->setParameter('studentId', $filters['studentId']);
        }

        if (isset($filters['visibleStudentId']) && is_int($filters['visibleStudentId'])) {
            $qb->andWhere('student.id = :visibleStudentId')->setParameter('visibleStudentId', $filters['visibleStudentId']);
        }

        if (isset($filters['visibleCompanyId']) && is_int($filters['visibleCompanyId'])) {
            $qb->andWhere('company.id = :visibleCompanyId')->setParameter('visibleCompanyId', $filters['visibleCompanyId']);
        }

        if (isset($filters['status']) && is_string($filters['status']) && trim($filters['status']) !== '') {
            $qb->andWhere('c.status = :status')->setParameter('status', strtolower(trim($filters['status'])));
        }

        if (isset($filters['opportunityId']) && is_int($filters['opportunityId'])) {
            $qb->andWhere('o.id = :opportunityId')->setParameter('opportunityId', $filters['opportunityId']);
        }

        if (isset($filters['createdAfter']) && $filters['createdAfter'] instanceof \DateTimeImmutable) {
            $qb->andWhere('c.createdAt >= :createdAfter')->setParameter('createdAfter', $filters['createdAfter']);
        }

        if (isset($filters['createdBefore']) && $filters['createdBefore'] instanceof \DateTimeImmutable) {
            $qb->andWhere('c.createdAt <= :createdBefore')->setParameter('createdBefore', $filters['createdBefore']);
        }

        $sortableFields = [
            'appliedDate' => 'c.appliedDate',
            'lastUpdated' => 'c.lastUpdated',
            'score' => 'c.score',
        ];

        $sortBy = is_string($filters['sortBy'] ?? null) ? $filters['sortBy'] : 'appliedDate';
        $orderBy = strtoupper((string) ($filters['order'] ?? 'DESC')) === 'ASC' ? 'ASC' : 'DESC';
        $qb->orderBy($sortableFields[$sortBy] ?? 'c.appliedDate', $orderBy);

        $qb->setFirstResult(($page - 1) * $limit)->setMaxResults($limit);

        $paginator = new Paginator($qb->getQuery(), true);
        $items = iterator_to_array($paginator->getIterator());

        return [$items, count($paginator)];
    }

    public function findLatestForStudentAndCompany(int $studentId, int $companyId): ?Candidature
    {
        return $this->createQueryBuilder('c')
            ->leftJoin('c.opportunity', 'o')->addSelect('o')
            ->leftJoin('o.companyId', 'company')->addSelect('company')
            ->leftJoin('c.student', 'student')->addSelect('student')
            ->andWhere('student.id = :studentId')
            ->andWhere('company.id = :companyId')
            ->setParameter('studentId', $studentId)
            ->setParameter('companyId', $companyId)
            ->orderBy('c.updatedAt', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function findLatestForStudent(int $studentId): ?Candidature
    {
        return $this->createQueryBuilder('c')
            ->leftJoin('c.opportunity', 'o')->addSelect('o')
            ->leftJoin('o.companyId', 'company')->addSelect('company')
            ->leftJoin('c.student', 'student')->addSelect('student')
            ->andWhere('student.id = :studentId')
            ->setParameter('studentId', $studentId)
            ->orderBy('c.updatedAt', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
