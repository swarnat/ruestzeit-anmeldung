<?php

namespace App\Repository;

use App\Entity\MailAttachmentClick;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<MailAttachmentClick>
 *
 * @method MailAttachmentClick|null find($id, $lockMode = null, $lockVersion = null)
 * @method MailAttachmentClick|null findOneBy(array $criteria, array $orderBy = null)
 * @method MailAttachmentClick[]    findAll()
 * @method MailAttachmentClick[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MailAttachmentClickRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, MailAttachmentClick::class);
    }

    /**
     * Find a click record for a specific mail and attachment
     */
    public function findByMailAndAttachment(int $mailId, int $attachmentId): ?MailAttachmentClick
    {
        return $this->createQueryBuilder('mac')
            ->andWhere('mac.mail = :mailId')
            ->andWhere('mac.attachment = :attachmentId')
            ->setParameter('mailId', $mailId)
            ->setParameter('attachmentId', $attachmentId)
            ->getQuery()
            ->getOneOrNullResult();
    }
}