<?php

namespace App\Generator;

use App\Entity\Ruestzeit;
use App\Repository\RuestzeitRepository;
use Symfony\Component\HttpFoundation\RequestStack;

class CurrentRuestzeitGenerator
{
    private Ruestzeit $currentRuestzeit;

    public function __construct(
        private RequestStack $requestStack,
        private RuestzeitRepository $ruestzeitRepository
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

        $currentRuestzeitId = $session->get("current_ruestzeit", null);

        if (!empty($currentRuestzeitId)) {
            return $this->ruestzeitRepository->find($currentRuestzeitId);
        }
    }
}
