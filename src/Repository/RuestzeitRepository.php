<?php

namespace App\Repository;

use App\Entity\Ruestzeit;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Ruestzeit>
 *
 * @method Ruestzeit|null find($id, $lockMode = null, $lockVersion = null)
 * @method Ruestzeit|null findOneBy(array $criteria, array $orderBy = null)
 * @method Ruestzeit[]    findAll()
 * @method Ruestzeit[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class RuestzeitRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Ruestzeit::class);
    }

    //    /**
    //     * @return Ruestzeit[] Returns an array of Ruestzeit objects
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

    //    public function findOneBySomeField($value): ?Ruestzeit
    //    {
    //        return $this->createQueryBuilder('r')
    //            ->andWhere('r.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
