<?php

namespace App\EntityListener;

use App\Entity\Ruestzeit;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsEntityListener;
use Doctrine\ORM\Events;
use Doctrine\Persistence\Event\LifecycleEventArgs;
use Symfony\Component\String\Slugger\SluggerInterface;

#[AsEntityListener(event: Events::prePersist, entity: Ruestzeit::class)]
#[AsEntityListener(event: Events::preUpdate, entity: Ruestzeit::class)]
class RuestzeitEntityListener
{
    public function __construct(
        private SluggerInterface $slugger,
    ) {
    }

    public function prePersist(Ruestzeit $conference, LifecycleEventArgs $event)
    {
        $conference->computeSlug($this->slugger);
    }

    public function preUpdate(Ruestzeit $conference, LifecycleEventArgs $event)
    {

        $conference->setPassword(preg_replace("/[^a-zA-Z0-9-_]/", '', $conference->getPassword()));

        // Do not update slug after creation
        // $conference->computeSlug($this->slugger);
    }
}