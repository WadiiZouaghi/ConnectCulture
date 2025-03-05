<?php

namespace App\Repository;

use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\PasswordUpgraderInterface;

/**
 * @extends ServiceEntityRepository<User>
 */
class UserRepository extends ServiceEntityRepository implements PasswordUpgraderInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
    }

    /**
     * Used to upgrade (rehash) the user's password automatically over time.
     */
    public function upgradePassword(PasswordAuthenticatedUserInterface $user, string $newHashedPassword): void
    {
        if (!$user instanceof User) {
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', $user::class));
        }

        $user->setPassword($newHashedPassword);
        $this->getEntityManager()->persist($user);
        $this->getEntityManager()->flush();
    }
    
    public function searchUsers(?string $term): array
    {
        $qb = $this->createQueryBuilder('u');

        if ($term && strlen(trim($term)) > 0) {
            $qb->where('u.fullName LIKE :term')
               ->orWhere('u.email LIKE :term')
               ->orWhere('u.phone LIKE :term')
               ->setParameter('term', '%' . trim($term) . '%');
        }

        return $qb->orderBy('u.fullName', 'ASC')
                 ->getQuery()
                 ->getResult();
    }
    public function findByParticipatingUser(User $user): array
    {
        return $this->createQueryBuilder('e')
            ->leftJoin('e.participants', 'p')
            ->where('p.user = :user')
            ->setParameter('user', $user)
            ->orderBy('e.date', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findByUpcomingForUser(User $user): array
    {
        $now = new \DateTime();
        
        return $this->createQueryBuilder('e')
            ->leftJoin('e.participants', 'p')
            ->where('p.user = :user')
            ->andWhere('e.date >= :now')
            ->setParameter('user', $user)
            ->setParameter('now', $now)
            ->orderBy('e.date', 'ASC')
            ->getQuery()
            ->getResult();
    }
    public function findBySearch(string $search): array
    {
        return $this->createQueryBuilder('u')
            ->where('u.fullName LIKE :search')
            ->orWhere('u.email LIKE :search')
            ->orWhere('u.phone LIKE :search')
            ->setParameter('search', '%' . $search . '%')
            ->orderBy('u.fullName', 'ASC')
            ->getQuery()
            ->getResult();
    }
    //    /**
    //     * @return User[] Returns an array of User objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('u')
    //            ->andWhere('u.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('u.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?User
    //    {
    //        return $this->createQueryBuilder('u')
    //            ->andWhere('u.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
