<?php

namespace App\Repository;

use App\Entity\Dette;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

/**
 * @extends ServiceEntityRepository<Dette>
 */
class DetteRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Dette::class);
    }

    //    /**
    //     * @return Dette[] Returns an array of Dette objects
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

    //    public function findOneBySomeField($value): ?Dette
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

        public function findAllDettes(int $page=1,int $limit=3): Paginator
        {
            $query=$this->createQueryBuilder('d')
        
              ->orderBy('d.id', 'ASC')
              ->setFirstResult(($page-1)*$limit)
              ->setMaxResults($limit)
                  ->getQuery();
        
            $paginator = new Paginator($query);
            return $paginator;
         }

         public function findAllNonArchivedDettes(int $page = 1, int $limit = 3): Paginator
        {
            $query = $this->createQueryBuilder('d')
                ->where('d.isArchived = :isArchived') // Filtrer les dettes non archivées
                ->setParameter('isArchived', false)
                ->orderBy('d.id', 'ASC')
                ->setFirstResult(($page - 1) * $limit)
                ->setMaxResults($limit)
                ->getQuery();

            return new Paginator($query);
        }



         public function findNonArchivedDettes(): array
{
    return $this->createQueryBuilder('d')
        ->where('d.isArchived = :isArchived')
        ->setParameter('isArchived', false)
        ->orderBy('d.dateAt', 'DESC')
        ->getQuery()
        ->getResult();
}


//          public function findDettesByStatus(int $page, int $limit, bool $isSolded): Paginator
// {
//     $queryBuilder = $this->createQueryBuilder('d')
//         ->orderBy('d.id', 'ASC')
//         ->setFirstResult(($page - 1) * $limit)
//         ->setMaxResults($limit);

//     // Filtre selon le statut
//     if ($isSolded) {
//         $queryBuilder->where('d.montantRestant = 0');
//     } else {
//         $queryBuilder->where('d.montantRestant > 0');
//     }

//     $query = $queryBuilder->getQuery();
//     return new Paginator($query);
// }

public function findDettesByStatus(int $page, int $limit, bool $isSolded): Paginator
{
    $queryBuilder = $this->createQueryBuilder('d')
        ->where('d.isArchived = :isArchived') // Exclure les dettes archivées
        ->setParameter('isArchived', false)
        ->orderBy('d.id', 'ASC')
        ->setFirstResult(($page - 1) * $limit)
        ->setMaxResults($limit);

    // Ajouter le filtre selon le statut soldé/non soldé
    if ($isSolded) {
        $queryBuilder->andWhere('d.montantRestant = 0'); // Dettes soldées
    } else {
        $queryBuilder->andWhere('d.montantRestant > 0'); // Dettes non soldées
    }

    $query = $queryBuilder->getQuery();
    return new Paginator($query);
}



        //  public function findNonSoldesDettes(int $page, int $limit): Paginator
        //  {
        //      $query = $this->createQueryBuilder('d')
        //          ->where('d.montantRestant > 0')
        //          ->orderBy('d.id', 'ASC')
        //          ->setFirstResult(($page-1)*$limit)
        //          ->setMaxResults($limit)
        //          ->getQuery();
        //          $paginator = new Paginator($query);
        //          return $paginator;
         
           
        //  }
        public function findNonSoldesDettes(): array
{
    return $this->createQueryBuilder('d')
        ->where('d.montantRestant > 0')
        ->orderBy('d.id', 'ASC')
        ->getQuery()
        ->getResult();
}
public function findSoldesDettes(): array
{
    return $this->createQueryBuilder('d')
        ->where('d.montantRestant = 0')
        ->orderBy('d.id', 'ASC')
        ->getQuery()
        ->getResult();
}

       
         public function getTotalDettes(): float
{
    return $this->createQueryBuilder('d')
        ->select('SUM(d.montant) as total')
        ->getQuery()
        ->getSingleScalarResult();
}
public function getTotalDettesForYesterday(): float
{
    $yesterdayStart = new \DateTime('yesterday 00:00:00');
    $yesterdayEnd = new \DateTime('yesterday 23:59:59');

    return $this->createQueryBuilder('d')
        ->select('SUM(d.montant) as total')
        ->where('d.dateAt BETWEEN :start AND :end')
        ->setParameter('start', $yesterdayStart)
        ->setParameter('end', $yesterdayEnd)
        ->getQuery()
        ->getSingleScalarResult() ?? 0;
}
public function findClientDettes($client, int $page = 1, int $limit = 3): Paginator
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

public function findAllDettesForClient($client): array
{
    return $this->createQueryBuilder('d')
        ->where('d.Client = :client')
        ->setParameter('client', $client)
        ->getQuery()
        ->getResult();
}


public function findNonSoldesDettesForClient( $client, int $page, int $limit): Paginator
{
    $query = $this->createQueryBuilder('d')
        ->where('d.Client = :client')
        ->andWhere('d.montantRestant > 0')
        ->setParameter('client', $client)
        ->orderBy('d.id', 'ASC')
        ->setFirstResult(($page - 1) * $limit)
        ->setMaxResults($limit)
        ->getQuery();

    return new Paginator($query);
}

public function findAllNonSoldesDettesForClient( $client): array
{
    return $this->createQueryBuilder('d')
        ->where('d.Client = :client')
        ->andWhere('d.montantRestant > 0')
        ->setParameter('client', $client)
        ->orderBy('d.id', 'ASC')
        ->getQuery()
        ->getResult();
}
public function findSoldesDettesForClient( $client, int $page, int $limit): Paginator
{
    $query = $this->createQueryBuilder('d')
        ->where('d.Client = :client')
        ->andWhere('d.montantRestant = 0')
        ->setParameter('client', $client)
        ->orderBy('d.id', 'ASC')
        ->setFirstResult(($page - 1) * $limit)
        ->setMaxResults($limit)
        ->getQuery();

    return new Paginator($query);
}

public function findAllSoldesDettesForClient( $client): array
{
    return $this->createQueryBuilder('d')
        ->where('d.Client = :client')
        ->andWhere('d.montantRestant = 0')
        ->setParameter('client', $client)
        ->orderBy('d.id', 'ASC')
        ->getQuery()
        ->getResult();
}
  

}
