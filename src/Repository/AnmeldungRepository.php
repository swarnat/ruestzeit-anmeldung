<?php

namespace App\Repository;

use App\Entity\Anmeldung;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Anmeldung>
 *
 * @method Anmeldung|null find($id, $lockMode = null, $lockVersion = null)
 * @method Anmeldung|null findOneBy(array $criteria, array $orderBy = null)
 * @method Anmeldung[]    findAll()
 * @method Anmeldung[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class AnmeldungRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Anmeldung::class);
    }

    //    /**
    //     * @return Anmeldung[] Returns an array of Anmeldung objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('a')
    //            ->andWhere('a.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('a.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Anmeldung
    //    {
    //        return $this->createQueryBuilder('a')
    //            ->andWhere('a.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
