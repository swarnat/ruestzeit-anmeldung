<?php

namespace App\EntityListener;

use App\Entity\Anmeldung;
use App\Entity\Ruestzeit;
use App\Enum\AnmeldungStatus;
use App\Service\PostalcodeService;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsEntityListener;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Events;
use Doctrine\Persistence\Event\LifecycleEventArgs;
use Symfony\Component\String\Slugger\SluggerInterface;

#[AsEntityListener(event: Events::prePersist, entity: Anmeldung::class)]
#[AsEntityListener(event: Events::preUpdate, entity: Anmeldung::class)]
class AnmeldungEntityListener
{
    public function __construct(
        private SluggerInterface $slugger,
        private PostalcodeService $postalcodeService,
        private EntityManagerInterface $entityManager
    ) {
    }

    private function setRegion($anmeldung) {
        $landkreis = $anmeldung->getLandkreis();

        if(empty($landkreis)) {
            $postalcodeData = $this->postalcodeService->getPostalcodeData("DE", $anmeldung->getPostalcode());

            if(!empty($postalcodeData)) {
                $anmeldung->setLandkreis($postalcodeData["region"]);
            }
        }

    }

    public function prePersist(Anmeldung $anmeldung, LifecycleEventArgs $event)
    {
        $this->setRegion($anmeldung);
        $anmeldung->setCreatedAt(new \DateTimeImmutable());

        if($anmeldung->getRegistrationPosition() == 0) {
            $this->setRegistrationPosition($anmeldung);
        }
                
        $status = $anmeldung->getStatus();

        if($status == AnmeldungStatus::OPEN) {
            $ruestzeit = $anmeldung->getRuestzeit();

            if ($ruestzeit->isFull()) {
                $anmeldung->setStatus(AnmeldungStatus::WAITLIST);
            } else {
                $anmeldung->setStatus(AnmeldungStatus::ACTIVE);
            }

        }

    }

    public function preUpdate(Anmeldung $anmeldung, LifecycleEventArgs $event)
    {

        if($anmeldung->getRegistrationPosition() == 0) {
            $this->setRegistrationPosition($anmeldung);
        }
        
        $this->setRegion($anmeldung);

    }

    private function setRegistrationPosition(Anmeldung $anmeldung) {
        
            $ruestzeit = $anmeldung->getRuestzeit();
            
            $query = $this->entityManager->createQueryBuilder('anmeldung');
            $query->from(Anmeldung::class, 'anmeldung');
            $query->select('MAX(anmeldung.registrationPosition) + 1 max_position');
            $query->where('anmeldung.ruestzeit = :ruestzeit')->setParameter('ruestzeit', $ruestzeit);

            $result = $query->getQuery()->getSingleResult();

            if (empty($result) || empty($result['max_position'])) {
                $maxPosition = 1;
            } else {
                $maxPosition = $result['max_position'];
            }

            $anmeldung->setRegistrationPosition($maxPosition);
    }
}