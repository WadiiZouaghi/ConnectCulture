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

    /**
     * @param string|null $query
     * @param string|null $location
     * @param string|null $visibility
     * @param string|array|null $date Either a single date (string) or a range (array of [startDate, endDate])
     * @param int $limit
     * @param int $offset
     * @param int|null $categoryId
     * @return array
     */
    public function findBySearchQueryWithFilters(?string $query = '', ?string $location = '', ?string $visibility = '', $date = null, int $limit = 9, int $offset = 0, ?int $categoryId = null): array
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
            // Check if $date is a range (e.g., ['2025-03-03', '2025-03-04'])
            if (is_array($date) && count($date) === 2) {
                $startDate = new \DateTime($date[0]);
                $endDate = new \DateTime($date[1]);
                $qb->andWhere('g.eventDate BETWEEN :startDate AND :endDate')
                   ->setParameter('startDate', $startDate->setTime(0, 0, 0))
                   ->setParameter('endDate', $endDate->setTime(23, 59, 59));
            } elseif (is_string($date)) {
                // Single date
                $dateTime = new \DateTime($date);
                $startOfDay = $dateTime->setTime(0, 0, 0);
                $endOfDay = $dateTime->setTime(23, 59, 59);
                $qb->andWhere('g.eventDate BETWEEN :startOfDay AND :endOfDay')
                   ->setParameter('startOfDay', $startOfDay)
                   ->setParameter('endOfDay', $endOfDay);
            }
        }

        if ($categoryId) {
            $qb->andWhere('g.groupType = :categoryId')
               ->setParameter('categoryId', $categoryId);
        }

        $qb->orderBy('g.eventDate', 'ASC')
           ->setFirstResult($offset)
           ->setMaxResults($limit);

        $results = $qb->getQuery()->getResult();
        $this->logger->info('findBySearchQueryWithFilters - Query: ' . $query . ', Location: ' . $location . ', Visibility: ' . $visibility . ', Date: ' . (is_array($date) ? implode(' to ', $date) : $date) . ', Category ID: ' . ($categoryId ?? 'None') . ', Limit: ' . $limit . ', Offset: ' . $offset . ', Results: ' . count($results));
        return $results;
    }

    /**
     * @param string|null $query
     * @param string|null $location
     * @param string|null $visibility
     * @param string|array|null $date Either a single date (string) or a range (array of [startDate, endDate])
     * @param int|null $categoryId
     * @return int
     */
    public function countBySearchQueryWithFilters(?string $query = '', ?string $location = '', ?string $visibility = '', $date = null, ?int $categoryId = null): int
    {
        $this->logger->info("Counting groups with search query: {$query}, location: {$location}, visibility: {$visibility}, date: " . (is_array($date) ? implode(' to ', $date) : $date) . ", category ID: " . ($categoryId ?? 'None'));

        $qb = $this->createQueryBuilder('g')
                   ->select('COUNT(g.id)');

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
            if (is_array($date) && count($date) === 2) {
                $startDate = new \DateTime($date[0]);
                $endDate = new \DateTime($date[1]);
                $qb->andWhere('g.eventDate BETWEEN :startDate AND :endDate')
                   ->setParameter('startDate', $startDate->setTime(0, 0, 0))
                   ->setParameter('endDate', $endDate->setTime(23, 59, 59));
            } elseif (is_string($date)) {
                $dateTime = new \DateTime($date);
                $startOfDay = $dateTime->setTime(0, 0, 0);
                $endOfDay = $dateTime->setTime(23, 59, 59);
                $qb->andWhere('g.eventDate BETWEEN :startOfDay AND :endOfDay')
                   ->setParameter('startOfDay', $startOfDay)
                   ->setParameter('endOfDay', $endOfDay);
            }
        }

        if ($categoryId) {
            $qb->andWhere('g.groupType = :categoryId')
               ->setParameter('categoryId', $categoryId);
        }

        $count = $qb->getQuery()->getSingleScalarResult();
        $this->logger->info("Total groups matching criteria: {$count}");
        return $count;
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

    public function findUpcomingEvents(int $daysAhead = 7, int $limit = 5): array
    {
        $this->logger->info("Fetching upcoming events within {$daysAhead} days, limit: {$limit}");

        $startDate = new \DateTime();
        $endDate = (clone $startDate)->modify("+{$daysAhead} days")->setTime(23, 59, 59);

        $qb = $this->createQueryBuilder('g')
                   ->where('g.eventDate BETWEEN :startDate AND :endDate')
                   ->setParameter('startDate', $startDate)
                   ->setParameter('endDate', $endDate)
                   ->orderBy('g.eventDate', 'ASC')
                   ->setMaxResults($limit);

        $results = $qb->getQuery()->getResult();
        $this->logger->info("Found " . count($results) . " upcoming events.");
        return $results;
    }
}