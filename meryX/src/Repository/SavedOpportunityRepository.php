<?php

namespace App\Repository;

use App\Entity\SavedOpportunity;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<SavedOpportunity>
 */
class SavedOpportunityRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, SavedOpportunity::class);
    }

    /**
     * @param array<string, mixed> $filters
     * @return array{0: array<int, SavedOpportunity>, 1: int}
     */
    public function search(array $filters, int $page, int $limit): array
    {
        $qb = $this->createQueryBuilder('s')
            ->distinct()
            ->leftJoin('s.opportunity', 'o')->addSelect('o')
            ->leftJoin('o.companyId', 'company')->addSelect('company')
            ->leftJoin('s.student', 'student')->addSelect('student')
            ->orderBy('s.savedDate', 'DESC');

        if (isset($filters['studentId']) && is_int($filters['studentId'])) {
            $qb->andWhere('student.id = :studentId')->setParameter('studentId', $filters['studentId']);
        }

        if (isset($filters['visibleStudentId']) && is_int($filters['visibleStudentId'])) {
            $qb->andWhere('student.id = :visibleStudentId')->setParameter('visibleStudentId', $filters['visibleStudentId']);
        }

        if (isset($filters['savedAfter']) && $filters['savedAfter'] instanceof \DateTimeImmutable) {
            $qb->andWhere('s.savedDate >= :savedAfter')->setParameter('savedAfter', $filters['savedAfter']);
        }

        if (isset($filters['savedBefore']) && $filters['savedBefore'] instanceof \DateTimeImmutable) {
            $qb->andWhere('s.savedDate <= :savedBefore')->setParameter('savedBefore', $filters['savedBefore']);
        }

        if (isset($filters['search']) && is_string($filters['search']) && trim($filters['search']) !== '') {
            $search = '%' . strtolower(trim($filters['search'])) . '%';
            $qb
                ->andWhere("LOWER(o.title) LIKE :search OR LOWER(o.description) LIKE :search OR LOWER(COALESCE(s.notes, '')) LIKE :search")
                ->setParameter('search', $search);
        }

        $qb->setFirstResult(($page - 1) * $limit)->setMaxResults($limit);

        $paginator = new Paginator($qb->getQuery(), true);
        $items = iterator_to_array($paginator->getIterator());

        return [$items, count($paginator)];
    }
}
