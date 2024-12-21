<?php

namespace App\Repository;

use App\Entity\Relance;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

/**
 * @extends ServiceEntityRepository<Relance>
 */
class RelanceRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Relance::class);
    }

    //    /**
    //     * @return Relance[] Returns an array of Relance objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('r')
    //            ->andWhere('r.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('r.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Relance
    //    {
    //        return $this->createQueryBuilder('r')
    //            ->andWhere('r.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }

    /**
        * @return Paginator 
        */

        public function findAllRelances(int $page=1,int $limit=3): Paginator
        {
            $query=$this->createQueryBuilder('r')
        
              ->orderBy('r.id', 'ASC')
              ->setFirstResult(($page-1)*$limit)
              ->setMaxResults($limit)
                  ->getQuery();
        
            $paginator = new Paginator($query);
            return $paginator;
         }
}
