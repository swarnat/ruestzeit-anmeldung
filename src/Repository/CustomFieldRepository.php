<?php

namespace App\Repository;

use App\Entity\CustomField;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<CustomField>
 *
 * @method CustomField|null find($id, $lockMode = null, $lockVersion = null)
 * @method CustomField|null findOneBy(array $criteria, array $orderBy = null)
 * @method CustomField[]    findAll()
 * @method CustomField[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CustomFieldRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CustomField::class);
    }
}
