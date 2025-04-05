<?php

namespace App\Generator;

use App\Entity\Ruestzeit;
use App\Repository\RuestzeitRepository;
use App\Service\RuestzeitExampleCreator;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\RequestStack;

class CurrentRuestzeitGenerator
{
    private Ruestzeit $currentRuestzeit;

    public function __construct(
        private RequestStack $requestStack,
        private RuestzeitRepository $ruestzeitRepository,
        private Security $security,
        private RuestzeitExampleCreator $ruestzeitExampleCreator
    ) {
        // Accessing the session in the constructor is *NOT* recommended, since
        // it might not be accessible yet or lead to unwanted side-effects
        // $this->session = $requestStack->getSession();
    }

    public function set(Ruestzeit $ruestzeit)
    {
        $this->currentRuestzeit = $ruestzeit;
    }

    public function get(): Ruestzeit
    {
        if (!empty($this->currentRuestzeit)) {
            return $this->currentRuestzeit;
        }

        $session = $this->requestStack->getSession();

        $currentRuestzeit = null;
        $currentRuestzeitId = $session->get("current_ruestzeit", null);
        
        # If current ruestzeit id is set, then take them
        if (!empty($currentRuestzeitId)) {
            $currentRuestzeit = $this->ruestzeitRepository->find($currentRuestzeitId);
        }

        # If there is no existing current Ruestzeit, then take one from future
        if(empty($currentRuestzeit)) {
            $user = $this->security->getUser();
            
            $currentRuestzeit = $this->ruestzeitRepository->findOneWithFutureDateFrom($user->getId());
            
            if(!empty($currentRuestzeit)) {
                $this->set($currentRuestzeit);
            }
        }

        # We have no Ruestzeit in future, take one you found
        if(empty($currentRuestzeit)) {
            $currentRuestzeit = $this->ruestzeitRepository->findOneBy(["admin" => $user->getId()]);

            if(!empty($currentRuestzeit)) {
                $this->set($currentRuestzeit);
            }
        }

        # We have no Ruestzeit, then create one Dummy Ruestzeit
        if(empty($currentRuestzeit)) {
            $currentRuestzeit = $this->ruestzeitExampleCreator->create();

            $this->set($currentRuestzeit);

            header("Location:/admin/ruestzeit");
            exit();
        }

        return $currentRuestzeit;
    }
}
