<?php

namespace App\Repository;

use App\Entity\Evants;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Evants>
 */
class EvantsRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Evants::class);
    }

    /**
     * Trouver un événement par son nom.
     *
     * @param string $name
     * @return Evants|null
     */
    public function findByName(string $name): ?Evants
    {
        return $this->createQueryBuilder('e')
            ->andWhere('e.name = :name')
            ->setParameter('name', $name)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * Récupérer tous les événements triés par date.
     *
     * @return Evants[]
     */
    public function findAllOrderedByDate(): array
    {
        return $this->createQueryBuilder('e')
            ->orderBy('e.date', 'ASC')  // Assumes you have a 'date' field in the Event entity.
            ->getQuery()
            ->getResult();
    }

    /**
     * Trouver les événements par type.
     *
     * @param int $typeId
     * @return Evants[]
     */
    public function findByEventType(int $typeId): array
    {
        return $this->createQueryBuilder('e')
            ->andWhere('e.eventType = :typeId')  // Assumes you have a 'eventType' relation in the Evants entity.
            ->setParameter('typeId', $typeId)
            ->getQuery()
            ->getResult();
    }
}
