<?php

namespace App\Repository;

use App\Entity\Admin;
use App\Entity\UserColumnConfig;
use App\Entity\Ruestzeit;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<UserColumnConfig>
 *
 * @method UserColumnConfig|null find($id, $lockMode = null, $lockVersion = null)
 * @method UserColumnConfig|null findOneBy(array $criteria, array $orderBy = null)
 * @method UserColumnConfig[]    findAll()
 * @method UserColumnConfig[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserColumnConfigRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, UserColumnConfig::class);
    }

    public function findForUserAndRuestzeit(Admin $user, Ruestzeit $ruestzeit): ?UserColumnConfig
    {
        return $this->createQueryBuilder('ucc')
            ->andWhere('ucc.user = :user')
            ->andWhere('ucc.ruestzeit = :ruestzeit')
            ->setParameter('user', $user)
            ->setParameter('ruestzeit', $ruestzeit)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function save(UserColumnConfig $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(UserColumnConfig $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
}