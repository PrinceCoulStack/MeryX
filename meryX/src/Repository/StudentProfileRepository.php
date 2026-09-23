<?php

namespace App\Repository;

use App\Entity\StudentProfile;
use App\Entity\University;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<StudentProfile>
 */
class StudentProfileRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, StudentProfile::class);
    }

    /**
     * @return array<int, StudentProfile>
     */
    public function findRegistrationRequestsForUniversity(?University $university, ?string $status = null): array
    {
        $qb = $this->createQueryBuilder('sp')
            ->orderBy('sp.createdAt', 'DESC');

        if ($university !== null) {
            $qb
                ->andWhere('sp.universityId = :university')
                ->setParameter('university', $university);
        }

        if ($status !== null && $status !== '') {
            $qb
                ->andWhere('sp.status = :status')
                ->setParameter('status', strtolower(trim($status)));
        }

        return $qb->getQuery()->getResult();
    }

    //    /**
    //     * @return StudentProfile[] Returns an array of StudentProfile objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('s')
    //            ->andWhere('s.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('s.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?StudentProfile
    //    {
    //        return $this->createQueryBuilder('s')
    //            ->andWhere('s.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
