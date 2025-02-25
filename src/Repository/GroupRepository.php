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
}