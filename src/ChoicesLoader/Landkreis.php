<?php
// src/AppBundle/ChoiceLoader.php

namespace App\ChoicesLoader;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\ChoiceList\ArrayChoiceList;
use Symfony\Component\Form\ChoiceList\ChoiceListInterface;
use Symfony\Component\Form\ChoiceList\Loader\ChoiceLoaderInterface;


class Landkreis implements ChoiceLoaderInterface
{
    public function __construct(
        protected EntityManagerInterface $entityManager,
        protected int $ruestzeitId
    ) {
    }

    protected $choices =
    [
    ];

    private function initChoices() {
        if(!empty($this->choices)) return;

        $query = $this->entityManager->createQuery('SELECT a FROM App\Entity\Anmeldung a WHERE a.ruestzeit = ' . $this->ruestzeitId . " GROUP BY a.landkreis");
        $anmeldungen = $query->getResult();

        $this->choices = [];
        foreach ($anmeldungen as $anmeldung) {
            $landkreis = $anmeldung->getLandkreis();

            if(!empty($landkreis)) {
                $this->choices[$anmeldung->getLandkreis()] = $anmeldung->getLandkreis();
            }
        }

    }

    public function loadChoiceList($value = null): ChoiceListInterface
    {
        $this->initChoices();
        
        return new ArrayChoiceList($this->choices);
    }

    public function loadChoicesForValues(array $values, $value = null): array
    {
        $this->initChoices();
        $result = [];

        foreach ($values as $val) {
            $key = array_search($val, $this->choices, true);

            if ($key !== false)
                $result[] = $key;
        }

        return $result;
    }

    public function loadValuesForChoices(array $choices, $value = null): array
    {
        $this->initChoices();
        $result = [];

        foreach ($choices as $label) {
            if (isset($this->choices[$label]))
                $result[] = $this->choices[$label];
        }

        return $result;
    }
}
