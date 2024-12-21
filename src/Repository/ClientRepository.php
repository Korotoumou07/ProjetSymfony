<?php

namespace App\Repository;

use App\Entity\User;
use App\Entity\Client;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

/**
 * @extends ServiceEntityRepository<Client>
 */
class ClientRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Client::class);
    }

//    /**
//     * @return Client[] Returns an array of Client objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('c')
//            ->andWhere('c.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('c.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Client
//    {
//        return $this->createQueryBuilder('c')
//            ->andWhere('c.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }




          /**
 * @return Client[] Returns an array of Client objects
 */
public function findByClientWithOrUser(string $statut): array
{
    $query = $this->createQueryBuilder('c');

    if ($statut === "Oui") {
        $query->where('c.User IS NOT NULL');
    } elseif ($statut === "Non") {
        $query->where('c.User IS NULL');
    }

    return $query->getQuery()->getResult();
}

public function findByUser(User $user): ?Client
{
    return $this->createQueryBuilder('c')
        ->where('c.User = :user')
        ->setParameter('user', $user)
        ->getQuery()
        ->getOneOrNullResult();
}



/**
        * @return Paginator 
        */

public function findAllClients(int $page=1,int $limit=3): Paginator
{
    $query=$this->createQueryBuilder('c')

      ->orderBy('c.id', 'ASC')
      ->setFirstResult(($page-1)*$limit)
      ->setMaxResults($limit)
          ->getQuery();

    $paginator = new Paginator($query);
    return $paginator;
 }

 public function findAllClientsStatut(int $page = 1, int $limit = 3, ?string $status = null): Paginator
{
    $queryBuilder = $this->createQueryBuilder('c')
        ->leftJoin('c.User', 'u') 
        ->addSelect('u')         
        ->orderBy('c.id', 'ASC')
        ->setFirstResult(($page - 1) * $limit)
        ->setMaxResults($limit);

    if ($status === 'active') {
        $queryBuilder->andWhere('u.isActive = :isActive')
                     ->setParameter('isActive', true);
    } elseif ($status === 'inactive') {
        $queryBuilder->andWhere('u.isActive = :isActive')
                     ->setParameter('isActive', false);
    }

    $query = $queryBuilder->getQuery();

    return new Paginator($query);
}

 public function getTotalClients(): int
{
    return $this->createQueryBuilder('c')
        ->select('COUNT(c.id)')
        ->getQuery()
        ->getSingleScalarResult();
}

public function getNewClientsForYesterday(): int
{
    $yesterdayStart = new \DateTime('yesterday 00:00:00');
    $yesterdayEnd = new \DateTime('yesterday 23:59:59');

    return $this->createQueryBuilder('c')
        ->select('COUNT(c.id)')
        ->where('c.createdAt BETWEEN :start AND :end')
        ->setParameter('start', $yesterdayStart)
        ->setParameter('end', $yesterdayEnd)
        ->getQuery()
        ->getSingleScalarResult();
}


}
