<?php

namespace App\Repository;

use App\Entity\Demande;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

/**
 * @extends ServiceEntityRepository<Demande>
 */
class DemandeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Demande::class);
    }

//    /**
//     * @return Demande[] Returns an array of Demande objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('d')
//            ->andWhere('d.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('d.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Demande
//    {
//        return $this->createQueryBuilder('d')
//            ->andWhere('d.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }


/**
        * @return Paginator Returns an array of Dette objects
        */

        public function findAllDemandes(int $page=1,int $limit=3): Paginator
        {
            $query=$this->createQueryBuilder('d')
              ->orderBy('d.dateAt', 'DESC')
              ->setFirstResult(($page-1)*$limit)
              ->setMaxResults($limit)
                  ->getQuery();
        
            $paginator = new Paginator($query);
            return $paginator;
         }
         public function findDemandesByEncours(string $statut,int $page=1,int $limit=3): Paginator
        {
            $query=$this->createQueryBuilder('d')
              ->orderBy('d.id', 'ASC')
              ->where('d.Statut = :statut')
              ->setParameter('statut', $statut)
              ->setFirstResult(($page-1)*$limit)
              ->setMaxResults($limit)
                  ->getQuery();
        
            $paginator = new Paginator($query);
            return $paginator;
         }
   
    public function findDemandesByStatut(string $statut, int $page, int $limit): array
{
    $qb = $this->createQueryBuilder('d')
        ->where('d.Statut = :statut')
        ->setParameter('statut', $statut)
        ->setFirstResult(($page - 1) * $limit)
        ->setMaxResults($limit);

    return $qb->getQuery()->getResult();
}
public function countDemandesByStatut(string $statut): int
    {
        $qb = $this->createQueryBuilder('d')
            ->select('COUNT(d.id)')
            ->where('d.Statut = :statut')
            ->setParameter('statut', $statut);

        return (int) $qb->getQuery()->getSingleScalarResult();
    }
    public function getDemandesEnCours(): int
{
    return $this->createQueryBuilder('d')
        ->select('COUNT(d.id)')
        ->where('d.Statut = :statut')
        ->setParameter('statut', 'ENCOURS')
        ->getQuery()
        ->getSingleScalarResult();
}
public function getDemandesEnCoursForYesterday(): int
{
    $yesterday = new \DateTime('yesterday');

    return $this->createQueryBuilder('d')
        ->select('COUNT(d.id)')
        ->where('d.dateAt <= :yesterday AND d.statut = :statut')
        ->setParameter('yesterday', $yesterday->format('Y-m-d'))
        ->setParameter('statut', 'EN_COURS')
        ->getQuery()
        ->getSingleScalarResult();
}

public function findClientDemandes($client, int $page = 1, int $limit = 3): Paginator
{
    $query = $this->createQueryBuilder('d')
        ->andWhere('d.Client = :client')
        ->setParameter('client', $client)
        ->orderBy('d.id', 'ASC')
        ->setFirstResult(($page - 1) * $limit)
        ->setMaxResults($limit)
        ->getQuery();

    return new Paginator($query);
}
public function findClientDemandesByStatut($client, string $statut, int $page, int $limit): Paginator
{
    $query = $this->createQueryBuilder('d')
        ->where('d.Client = :client')
        ->andWhere('d.Statut = :statut')
        ->setParameter('client', $client)
        ->setParameter('statut', $statut)
        ->orderBy('d.dateAt', 'DESC')
        ->setFirstResult(($page - 1) * $limit)
        ->setMaxResults($limit)
        ->getQuery();

    return new Paginator($query);
}

    

}
