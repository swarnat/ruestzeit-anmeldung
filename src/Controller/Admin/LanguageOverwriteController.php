<?php

namespace App\Controller\Admin;

use App\Entity\Anmeldung;
use App\Entity\LanguageOverwrite;
use App\Enum\AnmeldungStatus;
use App\Generator\CurrentRuestzeitGenerator;
use App\Repository\LanguageOverwriteRepository;
use App\Repository\RuestzeitRepository;
use App\Service\SignaturelistExporter;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FieldCollection;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\FormField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Exception;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Worksheet\Table;
use PhpOffice\PhpSpreadsheet\Worksheet\Table\TableStyle;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\Routing\Attribute\Route;


use PhpOffice\PhpSpreadsheet\Writer\Xlsx as WriterXlsx;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\HeaderUtils;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;

class LanguageOverwriteController extends AbstractController
{
    public function __construct(
        protected CurrentRuestzeitGenerator $currentRuestzeitGenerator
    )
    {
        
    }
    
    #[Route('/ruestzeit/labels', name: 'ruestzeit_label_overwrite')]
    public function index(Request $request,  EntityManagerInterface $entityManager, LanguageOverwriteRepository $languageOverwriteRepository): Response
    {
        $currentRuestzeit = $this->currentRuestzeitGenerator->get();
        $availableStrings = [
            "Schulklasse",
            
            "Eingeladen von",
            "Eingeladen von Hilfe",

            "Wunsch der Unterbringung",
            "Wunsch der Unterbringung Hilfe",

            "Doppelzimmer mit",
            "Doppelzimmer mit Hilfe",
            
            
        ];

        if($request->isMethod("POST")) {
            $terms = $request->get("term", []);
            
            $strings = $languageOverwriteRepository->findBy(["ruestzeit" => $currentRuestzeit]);
            $fieldLabels = [];

            foreach($strings as $string) {
                $fieldLabels[$string->getTerm()] = $string;
            }

            foreach($availableStrings as $string) {
                if(empty($terms[$string])) {
                    if(array_key_exists($string, $fieldLabels)) {
                        $entityManager->remove($fieldLabels[$string]);
                    }
                } else {
                    if(array_key_exists($string, $fieldLabels)) {
                        $fieldLabels[$string]->setValue($terms[$string]);
                        $entityManager->persist($fieldLabels[$string]);
                    } else {
                        $newObj = new LanguageOverwrite();
                        $newObj->setRuestzeit($currentRuestzeit);
                        $newObj->setTerm($string);
                        $newObj->setValue($terms[$string]);
                        $entityManager->persist($newObj);
                    }
                    
                }
            }

            $entityManager->flush();
        }

        $strings = $languageOverwriteRepository->findBy(["ruestzeit" => $currentRuestzeit]);

        $fieldLabels = [];
        foreach($strings as $string) {
            $fieldLabels[$string->getTerm()] = $string->getValue();
        }

        return $this->render('labeloverwrite/index.html.twig', [
            "availableStrings" => $availableStrings,
            "fieldLabels" => $fieldLabels,
        ]);
    }

}
