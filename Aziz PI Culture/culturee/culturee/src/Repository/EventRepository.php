<?php

namespace App\Repository;

use App\Entity\Event;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\QueryBuilder;

/**
 * @extends ServiceEntityRepository<Event>
 *
 * @method Event|null find($id, $lockMode = null, $lockVersion = null)
 * @method Event|null findOneBy(array $criteria, array $orderBy = null)
 * @method Event[]    findAll()
 * @method Event[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class EventRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Event::class);
    }

    /**
     * Find events by user
     */
    public function findByUser(User $user): array
    {
        return $this->createQueryBuilder('e')
            ->andWhere('e.user = :user')
            ->setParameter('user', $user)
            ->orderBy('e.id', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Find events by type
     */
    public function findByEventType(string $eventType): array
    {
        return $this->createQueryBuilder('e')
            ->andWhere('e.eventtype = :type')
            ->setParameter('type', $eventType)
            ->orderBy('e.id', 'DESC')
            ->getQuery()
            ->getResult();
    }
/**
 * Search events by query
 */
public function searchEventsForUser($query, $user): array
{
    $qb = $this->createQueryBuilder('e')
        ->where('e.user = :user')
        ->setParameter('user', $user);

    if ($query) {
        $qb->andWhere(
            $qb->expr()->orX(
                // Name matches - using LOWER for case-insensitive comparison
                $qb->expr()->like('LOWER(UNACCENT(e.name))', 'LOWER(UNACCENT(:term))'),
                $qb->expr()->like('LOWER(UNACCENT(e.destination))', 'LOWER(UNACCENT(:term))'),
                $qb->expr()->like('LOWER(UNACCENT(e.description))', 'LOWER(UNACCENT(:term))'),
                $qb->expr()->like('LOWER(UNACCENT(e.eventtype))', 'LOWER(UNACCENT(:term))')
            )
        )
        ->setParameter('term', '%' . $query . '%');
    }

    // First get the SQL for debugging
    $sql = $qb->getQuery()->getSQL();
    error_log("Generated SQL: " . $sql);
    error_log("Search term: " . $query);

    return $qb->getQuery()->getResult();
}
    /**
     * Find events by destination
     */
    public function findByDestination(string $destination): array
    {
        return $this->createQueryBuilder('e')
            ->andWhere('e.destination LIKE :destination')
            ->setParameter('destination', '%' . $destination . '%')
            ->orderBy('e.id', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Find events with equipment
     */
    public function findWithEquipment(): array
    {
        return $this->createQueryBuilder('e')
            ->andWhere('e.equipment IS NOT NULL')
            ->andWhere('e.equipment != :empty')
            ->setParameter('empty', '')
            ->orderBy('e.id', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Get events with pagination
     */
    public function getPaginatedEvents(int $page = 1, int $limit = 10, ?User $user = null): array
    {
        $qb = $this->createQueryBuilder('e');

        if ($user) {
            $qb->andWhere('e.user = :user')
                ->setParameter('user', $user);
        }

        return $qb->orderBy('e.id', 'DESC')
            ->setFirstResult(($page - 1) * $limit)
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    /**
     * Count total events
     */
    public function countTotal(?User $user = null): int
    {
        $qb = $this->createQueryBuilder('e')
            ->select('COUNT(e.id)');

        if ($user) {
            $qb->andWhere('e.user = :user')
                ->setParameter('user', $user);
        }

        return $qb->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * Find latest events
     */
    public function findLatest(int $limit = 5, ?User $user = null): array
    {
        $qb = $this->createQueryBuilder('e');

        if ($user) {
            $qb->andWhere('e.user = :user')
                ->setParameter('user', $user);
        }

        return $qb->orderBy('e.id', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    /**
     * Find events by multiple types
     */
    public function findByEventTypes(array $types): array
    {
        return $this->createQueryBuilder('e')
            ->andWhere('e.eventtype IN (:types)')
            ->setParameter('types', $types)
            ->orderBy('e.id', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Create base query builder
     */
    private function getBaseQueryBuilder(): QueryBuilder
    {
        return $this->createQueryBuilder('e')
            ->orderBy('e.id', 'DESC');
    }

    public function save(Event $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Event $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * Find ongoing events by organizer
     */
    public function findByUserAndDateGreaterThanEqual(User $user, \DateTime $currentDate): array
    {
        return $this->createQueryBuilder('e')
            ->andWhere('e.user = :user')
            ->andWhere('e.date >= :currentDate')
            ->setParameter('user', $user)
            ->setParameter('currentDate', $currentDate)
            ->orderBy('e.date', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Find ended events by organizer
     */
    public function findEndedEventsByOrganizer(User $user, \DateTime $currentDate): array
    {
        return $this->createQueryBuilder('e')
            ->andWhere('e.user = :user')
            ->andWhere('e.date < :currentDate')
            ->setParameter('user', $user)
            ->setParameter('currentDate', $currentDate)
            ->orderBy('e.date', 'DESC')
            ->getQuery()
            ->getResult();
    }
    
}