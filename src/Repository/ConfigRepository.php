<?php

namespace App\Repository;

use App\Entity\Config;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Config>
 *
 * @method Config|null find($id, $lockMode = null, $lockVersion = null)
 * @method Config|null findOneBy(array $criteria, array $orderBy = null)
 * @method Config[]    findAll()
 * @method Config[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ConfigRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Config::class);
    }

    /**
     * Get a configuration value by key
     */
    public function getValue(string $key, string $defaultValue = ''): ?string
    {
        $config = $this->findOneBy(['key' => $key]);
        
        return $config ? $config->getValue() : $defaultValue;
    }

    /**
     * Set a configuration value
     */
    public function setValue(string $key, string $value): Config
    {
        // Use DQL with parameter binding to avoid SQL injection and keyword issues
        $qb = $this->createQueryBuilder('c')
            ->where('c.key = :key')
            ->setParameter('key', $key);
            
        $config = $qb->getQuery()->getOneOrNullResult();
        
        if (!$config) {
            $config = new Config();
            $config->setKey($key);
        }
        
        $config->setValue($value);
        
        $this->getEntityManager()->persist($config);
        $this->getEntityManager()->flush();
        
        return $config;
    }
}