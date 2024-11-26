<?php

namespace App\EntityListener;

use App\Entity\Landkreis;
use App\Entity\Ruestzeit;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsEntityListener;
use Doctrine\ORM\Events;
use Doctrine\Persistence\Event\LifecycleEventArgs;
use Symfony\Component\String\Slugger\SluggerInterface;

#[AsEntityListener(event: Events::prePersist, entity: Landkreis::class)]
class LandkreisEntityListener
{
    public function __construct(
        private SluggerInterface $slugger,
    ) {
    }

    public function prePersist(Landkreis $conference, LifecycleEventArgs $event)
    {
        $conference->computeSlug($this->slugger);
    }

}