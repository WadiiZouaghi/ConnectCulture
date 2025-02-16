<?php

namespace App\Repository;

use App\Entity\Image;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Image>
 */
class ImageRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Image::class);
    }

    /**
     * Trouver une image par son nom.
     *
     * @param string $imageName
     * @return Image|null
     */
    public function findByName(string $imageName): ?Image
    {
        return $this->createQueryBuilder('i')
            ->andWhere('i.imageName = :imageName')
            ->setParameter('imageName', $imageName)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * Récupérer toutes les images associées à un événement.
     *
     * @param int $eventId
     * @return Image[]
     */
    public function findImagesByEventId(int $eventId): array
    {
        return $this->createQueryBuilder('i')
            ->join('i.events', 'e')
            ->where('e.id = :eventId')
            ->setParameter('eventId', $eventId)
            ->getQuery()
            ->getResult();
    }

    /**
     * Compter le nombre total d'images dans la base de données.
     *
     * @return int
     */
    public function countAllImages(): int
    {
        return $this->createQueryBuilder('i')
            ->select('COUNT(i)')
            ->getQuery()
            ->getSingleScalarResult();
    }
}
