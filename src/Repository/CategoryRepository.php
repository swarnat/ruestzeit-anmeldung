<?php

namespace App\Repository;

use App\Entity\Category;
use App\Entity\Ruestzeit;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Category>
 */
class CategoryRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Category::class);
    }

    /**
     * Find categories by Ruestzeit
     * 
     * @param Ruestzeit $ruestzeit
     * @return Category[] Returns an array of Category objects
     */
    public function findByRuestzeit(Ruestzeit $ruestzeit): array
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.ruestzeit = :ruestzeit')
            ->setParameter('ruestzeit', $ruestzeit)
            ->orderBy('c.title', 'ASC')
            ->getQuery()
            ->getResult()
        ;
    }
}
