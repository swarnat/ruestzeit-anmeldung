<?php

// src/EventListener/RequestListener.php
namespace App\EventListener;

use App\Entity\Anmeldung;
use App\Service\PostalcodeService;
use EasyCorp\Bundle\EasyAdminBundle\Event\BeforeCrudActionEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;

class AnmeldungLIstener implements EventSubscriberInterface
{
    public function __construct(
        private PostalcodeService $postalcodeService
    ) {
    }    

    public static function getSubscribedEvents(): array
    {
        return [
            BeforeCrudActionEvent::class => 'onCrudAction',
        ];
    }

    public function onCrudAction(BeforeCrudActionEvent $event): void
    {

        $context = $event->getAdminContext();

        $entityFqcn = $context->getEntity()->getFqcn();

        if($entityFqcn == Anmeldung::class) {
            $entity = $context->getEntity()->getInstance();

            if(!empty($entity)) {
                $landkreis = $entity->getLandkreis();

                if(empty($landkreis)) {
                    $postalcodeData = $this->postalcodeService->getPostalcodeData("DE", $entity->getPostalcode());

                    if(!empty($postalcodeData)) {
                        $entity->setLandkreis($postalcodeData["region"]);
                    }
                }
            }
        }

    }
}
