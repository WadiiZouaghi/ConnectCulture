<?php

namespace App\Repository;

use App\Entity\Eventtype;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Eventtype>
 */
class EventtypeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Eventtype::class);
    }

    /**
     * Trouver un type d'événement par son nom.
     *
     * @param string $name
     * @return Eventtype|null
     */
    public function findByName(string $name): ?Eventtype
    {
        return $this->createQueryBuilder('e')
            ->andWhere('e.name = :name')
            ->setParameter('name', $name)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * Récupérer tous les types d'événements triés par ordre alphabétique.
     *
     * @return Eventtype[]
     */
    public function findAllOrderedByName(): array
    {
        return $this->createQueryBuilder('e')
            ->orderBy('e.name', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Compter le nombre d'événements d'un type spécifique.
     *
     * @param int $typeId
     * @return int
     */
    public function countEventsByType(int $typeId): int
    {
        return $this->createQueryBuilder('e')
            ->select('COUNT(e)')
            ->andWhere('e.id = :typeId')
            ->setParameter('typeId', $typeId)
            ->getQuery()
            ->getSingleScalarResult();
    }
}
