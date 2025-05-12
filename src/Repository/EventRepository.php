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
            ->andWhere('e.userId = :userId')
            ->setParameter('userId', $user->getId())
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
     * Search events by criteria
     */
    public function searchEvents(?string $query, ?string $eventType = null, ?User $user = null): array
    {
        $qb = $this->createQueryBuilder('e');

        if ($query) {
            $qb->andWhere('e.name LIKE :query OR e.destination LIKE :query OR e.Description LIKE :query')
                ->setParameter('query', '%' . $query . '%');
        }

        if ($eventType) {
            $qb->andWhere('e.eventtype = :eventType')
                ->setParameter('eventType', $eventType);
        }

        if ($user) {
            $qb->andWhere('e.userId = :userId')
                ->setParameter('userId', $user->getId());
        }

        return $qb->orderBy('e.id', 'DESC')
            ->getQuery()
            ->getResult();
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
            $qb->andWhere('e.userId = :userId')
                ->setParameter('userId', $user->getId());
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
            $qb->andWhere('e.userId = :userId')
                ->setParameter('userId', $user->getId());
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
            $qb->andWhere('e.userId = :userId')
                ->setParameter('userId', $user->getId());
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
}