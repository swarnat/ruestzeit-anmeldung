<?php

namespace App\Filter;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;

class CurrentUserFilterConfigurator
{
    public function __construct(
        private EntityManagerInterface $em,
        private Security $security
    ) {}

    public function onKernelRequest(): void
    {
        $filter = $this->em->getFilters()->enable('currentuser');
        if ($filter instanceof CurrentUserQueryFilter) {
            $filter->setSecurity($this->security);
        }
    }
}
