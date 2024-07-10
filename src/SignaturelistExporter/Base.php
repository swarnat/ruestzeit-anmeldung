<?php

namespace App\SignaturelistExporter;

use App\Entity\Ruestzeit;
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

}