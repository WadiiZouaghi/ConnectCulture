<?php

namespace App\Repository;

use App\Entity\Group;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class GroupRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Group::class);
    }

    public function findBySearchQuery(string $query): array
    {
        $qb = $this->createQueryBuilder('g');

        $qb->where($qb->expr()->orX(
            $qb->expr()->like('g.name', ':query'),
            $qb->expr()->like('g.description', ':query'),
            $qb->expr()->like('g.location', ':query')
        ))
        ->setParameter('query', '%' . $query . '%')
        ->orderBy('g.name', 'ASC');

        return $qb->getQuery()->getResult();
    }

    public function findBySearchQueryWithFilters(?string $query = '', ?string $location = '', ?string $visibility = '', ?string $date = ''): array
    {
        $qb = $this->createQueryBuilder('g');

        if ($query) {
            $qb->andWhere('g.name LIKE :query OR g.description LIKE :query OR g.location LIKE :query')
               ->setParameter('query', '%' . $query . '%');
        }

        if ($location) {
            $qb->andWhere('g.location = :location')
               ->setParameter('location', $location);
        }

        if ($visibility) {
            $qb->andWhere('g.visibility = :visibility')
               ->setParameter('visibility', $visibility);
        }

        if ($date) {
            $qb->andWhere('g.eventDate = :date')
               ->setParameter('date', new \DateTime($date));
        }

        return $qb->getQuery()->getResult();
    }

    public function findUniqueLocations(): array
    {
        return $this->createQueryBuilder('g')
            ->select('DISTINCT g.location')
            ->where('g.location IS NOT NULL')
            ->andWhere('g.location != :empty')
            ->setParameter('empty', '')
            ->getQuery()
            ->getScalarResult();
    }

    public function findUniqueVisibilities(): array
    {
        return $this->createQueryBuilder('g')
            ->select('DISTINCT g.visibility')
            ->where('g.visibility IS NOT NULL')
            ->andWhere('g.visibility != :empty')
            ->setParameter('empty', '')
            ->getQuery()
            ->getScalarResult();
    }
}