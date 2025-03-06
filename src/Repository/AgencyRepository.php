<?php

namespace App\Repository;

use App\Entity\Agency;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Agency>
 */
class AgencyRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Agency::class);
    }
    // In AgencyRepository
    // Method to search agencies by name
public function findBySearch(?string $search): array
{
    $queryBuilder = $this->createQueryBuilder('a');

    if ($search) {
        $queryBuilder
            ->andWhere('a.name LIKE :search')
            ->setParameter('search', '%' . $search . '%');
    }

    return $queryBuilder->getQuery()->getResult();
}

// Method to sort agencies by name (ASC or DESC)
public function findBySort(array $agencies, string $sort): array
{
    if ($sort === 'desc') {
        usort($agencies, function($a, $b) {
            return strcmp($b->getName(), $a->getName()); // Sort descending
        });
    } else {
        usort($agencies, function($a, $b) {
            return strcmp($a->getName(), $b->getName()); // Sort ascending
        });
    }

    return $agencies;
}


//    /**
//     * @return Agency[] Returns an array of Agency objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('a')
//            ->andWhere('a.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('a.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Agency
//    {
//        return $this->createQueryBuilder('a')
//            ->andWhere('a.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
