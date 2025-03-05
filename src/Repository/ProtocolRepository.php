<?php

namespace App\Repository;

use App\Entity\Protocol;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Protocol>
 *
 * @method Protocol|null find($id, $lockMode = null, $lockVersion = null)
 * @method Protocol|null findOneBy(array $criteria, array $orderBy = null)
 * @method Protocol[]    findAll()
 * @method Protocol[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ProtocolRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Protocol::class);
    }

    /**
     * @return Protocol[] Returns an array of Protocol objects
     */
    public function findByRuestzeit($ruestzeit, $limit = 100): array
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.ruestzeit = :val')
            ->setParameter('val', $ruestzeit)
            ->orderBy('p.createdAt', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * @return Protocol[] Returns an array of Protocol objects
     */
    public function findByAnmeldung($anmeldung): array
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.anmeldung = :val')
            ->setParameter('val', $anmeldung)
            ->orderBy('p.createdAt', 'DESC')
            ->getQuery()
            ->getResult()
        ;
    }
}
