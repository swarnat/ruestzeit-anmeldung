<?php

namespace App\SignaturelistExporter;

use App\Entity\Anmeldung;
use App\Entity\Ruestzeit;
use App\Enum\AnmeldungStatus;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

abstract class Base {

    public function __construct(
        protected Ruestzeit $ruestzeit, 
        protected EntityManagerInterface $entityManager,
        protected TranslatorInterface $translator,
    )
    {}
    
    abstract public function getFileExtension();
    abstract public function generateExport(array $fields, string $filename, array $options);

    /**
     * Group Anmeldungen by field
     *
     * @param Anmeldung[]   $anmeldungen
     * @param array $options
     * @return Anmeldung[][]
     */
    protected function getGroups($anmeldungen, $options): array
    {
        $anmeldeListe = [];

        foreach ($anmeldungen as $anmeldung) {
            if (!empty($options["split"]) && $options["split"] != "none") {
                $field = "get" . ucfirst($options["split"]);
                $value = $anmeldung->$field();

                if (is_object($anmeldung->$field())) {
                    $value = \Symfony\Component\Translation\t($anmeldung->$field()->value, [], 'messages')->trans($this->translator);
                }

                $groupTitle = $options["group_prefix"] . " " . $value;
                if (empty($groupTitle)) continue;
            } else {
                $groupTitle = "";
            }

            if (!isset($anmeldeListe[$groupTitle])) {
                $anmeldeListe[$groupTitle] = [];
            }

            $anmeldeListe[$groupTitle][] = $anmeldung;
        }

        return $anmeldeListe;
    }

    /**
     * Get aktive Teilnehmer
     *
     * @param array $options
     * @return Anmeldung[]
     */
    public function getAnmeldungen($options) {
        $query = $this->entityManager->createQuery(
            'SELECT a FROM App\Entity\Anmeldung a WHERE a.ruestzeit = ' . $this->ruestzeit->getId() .
                " AND a.status = '" . AnmeldungStatus::ACTIVE->value . "'" . ( !empty($options["filter_landkreis"]) ? " AND a.landkreis = '" . $options["filter_landkreis"] . "'":"") .
                " ORDER BY a.lastname, a.firstname"
        );
        
        /**
         * @var Anmeldung[] $anmeldungen
         */
        $anmeldungen = $query->getResult();

        return $anmeldungen;
    }

}