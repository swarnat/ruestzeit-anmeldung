<?php

namespace App\Repository;

use App\Entity\CustomFieldAnswer;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<CustomFieldAnswer>
 *
 * @method CustomFieldAnswer|null find($id, $lockMode = null, $lockVersion = null)
 * @method CustomFieldAnswer|null findOneBy(array $criteria, array $orderBy = null)
 * @method CustomFieldAnswer[]    findAll()
 * @method CustomFieldAnswer[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CustomFieldAnswerRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CustomFieldAnswer::class);
    }
}
