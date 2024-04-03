<?php

namespace App\EntityListener;

use App\Entity\Anmeldung;
use App\Entity\Ruestzeit;
use App\Service\PostalcodeService;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsEntityListener;
use Doctrine\ORM\Events;
use Doctrine\Persistence\Event\LifecycleEventArgs;
use Symfony\Component\String\Slugger\SluggerInterface;

#[AsEntityListener(event: Events::prePersist, entity: Anmeldung::class)]
#[AsEntityListener(event: Events::preUpdate, entity: Anmeldung::class)]
class AnmeldungEntityListener
{
    public function __construct(
        private SluggerInterface $slugger,
        private PostalcodeService $postalcodeService
    ) {
    }

    private function setRegion($anmeldung) {
        $postalcodeData = $this->postalcodeService->getPostalcodeData("DE", $anmeldung->getPostalcode());

        if(!empty($postalcodeData)) {
            $anmeldung->setLandkreis($postalcodeData["region"]);
        }

    }

    public function prePersist(Anmeldung $anmeldung, LifecycleEventArgs $event)
    {
        $this->setRegion($anmeldung);
        $anmeldung->setCreatedAt(new \DateTimeImmutable());
    }

    public function preUpdate(Anmeldung $anmeldung, LifecycleEventArgs $event)
    {

        $this->setRegion($anmeldung);

    }
}