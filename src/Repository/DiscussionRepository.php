<?php

namespace App\Repository;

use App\Entity\Discussion;
use App\Entity\Group;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class DiscussionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Discussion::class);
    }

    public function findByGroupOrderedByDate(Group $group): array
    {
        return $this->createQueryBuilder('d')
            ->where('d.group = :group')
            ->setParameter('group', $group)
            ->orderBy('d.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }
}