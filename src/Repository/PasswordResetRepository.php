<?php

namespace App\Repository;

use App\Entity\PasswordReset;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<PasswordReset>
 */
class PasswordResetRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PasswordReset::class);
    }

    public function countActiveRequestsByIp(string $ipAddress): int
    {
        $now = new \DateTimeImmutable();
        
        return $this->createQueryBuilder('pr')
            ->select('COUNT(pr.id)')
            ->where('pr.ipAddress = :ip')
            ->andWhere('pr.used = :used')
            ->andWhere('pr.expiresAt > :now')
            ->setParameter('ip', $ipAddress)
            ->setParameter('used', false)
            ->setParameter('now', $now)
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function findValidByToken(string $token): ?PasswordReset
    {
        $now = new \DateTimeImmutable();
        
        return $this->createQueryBuilder('pr')
            ->where('pr.token = :token')
            ->andWhere('pr.used = :used')
            ->andWhere('pr.expiresAt > :now')
            ->setParameter('token', $token)
            ->setParameter('used', false)
            ->setParameter('now', $now)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
