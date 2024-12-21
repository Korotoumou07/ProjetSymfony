<?php

namespace App\Repository;

use App\Entity\User;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Symfony\Component\Security\Core\User\PasswordUpgraderInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;

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



/**
        * @return Paginator Returns an array of Dette objects
        */

        public function findAllUsers(int $page=1,int $limit=3): Paginator
        {
            $query=$this->createQueryBuilder('u')
        
              ->orderBy('u.id', 'ASC')
              ->setFirstResult(($page-1)*$limit)
              ->setMaxResults($limit)
                  ->getQuery();
        
            $paginator = new Paginator($query);
            return $paginator;
         }
        

        public function findByRole(string $role, int $page, int $limit, ?string $status = null): array
        {
            $queryBuilder = $this->createQueryBuilder('u')
                ->where('u.roles LIKE :role')
                ->setParameter('role', '%"' . $role . '"%')
                ->setFirstResult(($page - 1) * $limit)
                ->setMaxResults($limit);

            if ($status === 'active') {
                $queryBuilder->andWhere('u.isActive = :isActive')
                            ->setParameter('isActive', true);
            } elseif ($status === 'inactive') {
                $queryBuilder->andWhere('u.isActive = :isActive')
                            ->setParameter('isActive', false);
            }

            return $queryBuilder->getQuery()->getResult();
        }

         
        

        public function countByRole(string $role, ?string $status = null): int
        {
            $queryBuilder = $this->createQueryBuilder('u')
                ->select('COUNT(u.id)')
                ->where('u.roles LIKE :role')
                ->setParameter('role', '%"' . $role . '"%');

            if ($status === 'active') {
                $queryBuilder->andWhere('u.isActive = :isActive')
                            ->setParameter('isActive', true);
            } elseif ($status === 'inactive') {
                $queryBuilder->andWhere('u.isActive = :isActive')
                            ->setParameter('isActive', false);
            }

            return (int) $queryBuilder->getQuery()->getSingleScalarResult();
        }

         
         public function findByRoleHierarchy(string $role, int $page, int $limit)
         {
             return $this->createQueryBuilder('u')
                 ->where('u.roles LIKE :role')
                 ->setParameter('roles', '["'.$role.'"]')
                 ->setFirstResult(($page - 1) * $limit)
                 ->setMaxResults($limit)
                 ->getQuery()
                 ->getResult();
         }
         public function findOneByIdentifier(string $identifier): ?User
    {
        return $this->createQueryBuilder('u')
            ->where('u.id = :identifier OR u.login = :identifier')
            ->setParameter('identifier', $identifier)
            ->getQuery()
            ->getOneOrNullResult();
    }
       
                    

}

