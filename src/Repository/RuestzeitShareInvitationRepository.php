<?php

namespace App\Repository;

use App\Entity\RuestzeitShareInvitation;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<RuestzeitShareInvitation>
 *
 * @method RuestzeitShareInvitation|null find($id, $lockMode = null, $lockVersion = null)
 * @method RuestzeitShareInvitation|null findOneBy(array $criteria, array $orderBy = null)
 * @method RuestzeitShareInvitation[]    findAll()
 * @method RuestzeitShareInvitation[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class RuestzeitShareInvitationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, RuestzeitShareInvitation::class);
    }

    public function findPendingByEmailAndRuestzeit(string $email, int $ruestzeitId): ?RuestzeitShareInvitation
    {
        return $this->createQueryBuilder('i')
            ->andWhere('i.email = :email')
            ->andWhere('i.ruestzeit = :ruestzeitId')
            ->andWhere('i.acceptedAt IS NULL')
            ->setParameter('email', $email)
            ->setParameter('ruestzeitId', $ruestzeitId)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function findByToken(string $token): ?RuestzeitShareInvitation
    {
        return $this->createQueryBuilder('i')
            ->andWhere('i.token = :token')
            ->setParameter('token', $token)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
