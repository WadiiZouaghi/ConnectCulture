<?php

namespace App\Repository;

use App\Entity\Panier;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Panier>
 */
class PanierRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Panier::class);
    }

    /**
     * Trouver un panier d'un utilisateur spécifique.
     *
     * @param int $userId
     * @return Panier|null
     */
    public function findByUserId(int $userId): ?Panier
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.user = :userId')
            ->setParameter('userId', $userId)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * Compter le nombre d'articles dans un panier.
     *
     * @param int $panierId
     * @return int
     */
    public function countItemsInPanier(int $panierId): int
    {
        return $this->createQueryBuilder('p')
            ->select('COUNT(pi)')
            ->join('p.items', 'pi')  // Assuming you have a relationship with a 'items' entity
            ->where('p.id = :panierId')
            ->setParameter('panierId', $panierId)
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * Récupérer un panier par son ID.
     *
     * @param int $panierId
     * @return Panier|null
     */
    public function findOneById(int $panierId): ?Panier
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.id = :panierId')
            ->setParameter('panierId', $panierId)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
