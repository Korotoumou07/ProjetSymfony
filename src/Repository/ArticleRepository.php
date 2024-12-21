<?php

namespace App\Repository;

use App\Entity\Article;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\Tools\Pagination\Paginator;

/**
 * @extends ServiceEntityRepository<Article>
 */
class ArticleRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Article::class);
    }


/**
 * @param string|null 
 * @param int 
 * @param int 
 * @return Paginator 
 */
public function findByStatut(?string $statut, int $page = 1, int $limit = 2): Paginator
{
    $queryBuilder = $this->createQueryBuilder('a');

    if ($statut === "DIS") {
        $queryBuilder->where('a.qteStock > 0');
    } elseif ($statut === "RUP") {
        $queryBuilder->where('a.qteStock = 0');
    }

    $query = $queryBuilder
        ->orderBy('a.id', 'ASC') 
        ->setFirstResult(($page - 1) * $limit) 
        ->setMaxResults($limit) 
        ->getQuery();

    return new Paginator($query);
}


/**
        * @return Paginator 
        */


public function findByDette($idDette, int $page = 1, int $limit = 3): Paginator
{
    $query = $this->createQueryBuilder('a')
        ->innerJoin('a.dettes', 'd')
        ->andWhere('d.id = :idDette')
        ->setParameter('idDette', $idDette)
        ->orderBy('a.id', 'ASC')
        ->setFirstResult(($page - 1) * $limit)
        ->setMaxResults($limit)
        ->getQuery();

    return new Paginator($query);
}


public function findPrixByDette($idDette, int $page = 1, int $limit = 3): Paginator
{
    $query = $this->createQueryBuilder('de')
        ->innerJoin('de.Article', 'a')
        ->andWhere('d.id = :idDette')
        ->setParameter('idDette', $idDette)
        ->orderBy('a.id', 'ASC')
        ->setFirstResult(($page - 1) * $limit)
        ->setMaxResults($limit)
        ->getQuery();

    return new Paginator($query);
}
/**
        * @return Paginator 
        */
        public function findDIS(int $page=1,int $limit=3): Paginator
        {
            $query = $this->createQueryBuilder('a')
                ->where('a.qteStock > 0')
                ->orderBy('a.id', 'ASC')
                ->setFirstResult(($page-1)*$limit)
               ->setMaxResults($limit)
                ->getQuery();
                $paginator = new Paginator($query);
                return $paginator;
        
        }
         /**
        * @return Paginator 
        */
public function findRUP(int $page=1,int $limit=3): Paginator
         {
             $query = $this->createQueryBuilder('a')
                 ->where('a.qteStock = 0')
                 ->orderBy('a.id', 'ASC')
                 ->setFirstResult(($page-1)*$limit)
                ->setMaxResults($limit)
                 ->getQuery();
                 $paginator = new Paginator($query);
                 return $paginator;
         
         }

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
 public function findAllArticles(int $page=1,int $limit=3): Paginator
{
    $query=$this->createQueryBuilder('a')

      ->orderBy('a.id', 'ASC')
      ->setFirstResult(($page-1)*$limit)
      ->setMaxResults($limit)
          ->getQuery();

    $paginator = new Paginator($query);
    return $paginator;
 }



/**
        * @return Paginator 
        */

public function findAllArtticles(int $page=1,int $limit=3): Paginator
{
    $query=$this->createQueryBuilder('a')

      ->orderBy('a.id', 'ASC')
      ->setFirstResult(($page-1)*$limit)
      ->setMaxResults($limit)
          ->getQuery();

    $paginator = new Paginator($query);
    return $paginator;
 }

 public function getQuantiteTotaleArticles(): int
{
    return $this->createQueryBuilder('a')
        ->select('SUM(a.qteStock) as totalQuantite')
        ->getQuery()
        ->getSingleScalarResult();
}

}
