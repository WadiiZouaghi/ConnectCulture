<?php

namespace App\Repository;

use App\Entity\Actor;
use App\Entity\Group;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Psr\Log\LoggerInterface;

class GroupRepository extends ServiceEntityRepository
{
    private LoggerInterface $logger;

    public function __construct(ManagerRegistry $registry, LoggerInterface $logger)
    {
        parent::__construct($registry, Group::class);
        $this->logger = $logger;
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

        $results = $qb->getQuery()->getResult();
        $this->logger->info('findBySearchQuery - Query: ' . $query . ', Results: ' . count($results));
        return $results;
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
            // Match only the date portion of eventDate
            $dateTime = new \DateTime($date);
            $startOfDay = $dateTime->setTime(0, 0, 0);
            $endOfDay = $dateTime->setTime(23, 59, 59);
            $qb->andWhere('g.eventDate BETWEEN :startOfDay AND :endOfDay')
               ->setParameter('startOfDay', $startOfDay)
               ->setParameter('endOfDay', $endOfDay);
        }

        $results = $qb->getQuery()->getResult();
        $this->logger->info('findBySearchQueryWithFilters - Query: ' . $query . ', Location: ' . $location . ', Visibility: ' . $visibility . ', Date: ' . $date . ', Results: ' . count($results));
        return $results;
    }

    public function findUniqueLocations(): array
    {
        $locations = $this->createQueryBuilder('g')
            ->select('DISTINCT g.location')
            ->where('g.location IS NOT NULL')
            ->andWhere('g.location != :empty')
            ->setParameter('empty', '')
            ->getQuery()
            ->getScalarResult();

        $this->logger->info('findUniqueLocations - Locations found: ' . count($locations));
        return $locations;
    }

    public function findUniqueVisibilities(): array
    {
        $visibilities = $this->createQueryBuilder('g')
            ->select('DISTINCT g.visibility')
            ->where('g.visibility IS NOT NULL')
            ->andWhere('g.visibility != :empty')
            ->setParameter('empty', '')
            ->getQuery()
            ->getScalarResult();

        $this->logger->info('findUniqueVisibilities - Visibilities found: ' . count($visibilities));
        return $visibilities;
    }

    public function findByUserParticipation(Actor $user): array
    {
        $results = $this->createQueryBuilder('g')
            ->innerJoin('g.actors', 'a')
            ->where('a.id = :userId')
            ->setParameter('userId', $user->getId())
            ->orderBy('g.eventDate', 'ASC')
            ->getQuery()
            ->getResult();

        $this->logger->info('findByUserParticipation - User ID: ' . $user->getId() . ', Results: ' . count($results));
        return $results;
    }
}