<?php

namespace App\Controller\Admin;

use App\Entity\Anmeldung;
use App\Entity\CustomField;
use App\Entity\CustomFieldAnswer;
use App\Enum\AnmeldungStatus;
use App\Enum\CustomFieldType;
use App\Generator\CurrentRuestzeitGenerator;
use App\Repository\RuestzeitRepository;
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
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Contracts\Translation\TranslatorInterface;

class StatisticController extends AbstractController
{
    public function __construct(
        protected CurrentRuestzeitGenerator $currentRuestzeitGenerator,
        protected TranslatorInterface $translator,
        private AdminUrlGenerator $adminUrlGenerator
    ) {}

    #[Route('/admin/statistics', name: 'admin_statistics')]
    public function index(EntityManagerInterface $entityManager): Response
    {
        $metadata = $entityManager->getClassMetadata(Anmeldung::class);
        $fieldNames = $metadata->getFieldNames();

        $ruestzeit = $this->currentRuestzeitGenerator->get();
        $anmeldungen = $ruestzeit->getActiveAnmeldungen();

        $fieldNames = [
            "mealtype", "city", "prepayment_done", "payment_done", "status", "landkreis", "personenTyp", "schoolclass", "roomRequest", "age"
        ];

        foreach ($fieldNames as $fieldName) {
            $fieldType = $metadata->getTypeOfField($fieldName);

            $statistics[$fieldName] = [
                "title" => \Symfony\Component\Translation\t("FIELDS.".$fieldName, [], 'messages')->trans($this->translator),
                "table" => [],
                "chart_data" => [
                    "labels" => [],
                    "datasets" => [[
                        "label" => \Symfony\Component\Translation\t("FIELDS.".$fieldName, [], 'messages')->trans($this->translator),
                        "data" => []
                    ]]
                ]
            ];

            $property = ucfirst(str_replace('_', '', ucwords($fieldName, '_')));

            $row = [];
            $originalValue = [];

            if($fieldType =="boolean") {
                $originalValue = [
                    "Ja" => 1,
                    "Nein" => 0
                ];
            }

            foreach($anmeldungen as $anmeldung) {
                if(method_exists($anmeldung, "get" . $property)) {
                    $functionName = "get" . $property;
                    $value = $anmeldung->$functionName();
                } elseif(method_exists($anmeldung, "is" . $property)) {
                    $functionName = "is" . $property;
                    $value = $anmeldung->$functionName();
                }
                
                if(is_object($value)) {
                    $originalString = $value->value;
                    $value = \Symfony\Component\Translation\t($value->name, [], 'messages')->trans($this->translator);
                    $originalValue[$value] = $originalString;
                }

                if($fieldType =="boolean") {
                    if($value === true) {
                        $value = "Ja";
                    } else {
                        $value = "Nein";
                    }
                }

                if(!array_key_exists($value, $row)) {
                    $row[$value] = 0;
                }

                $row[$value]++;
            }
            
            ksort($row);

            foreach($row as $value => $count) {
                if($fieldName != "age") {
                    $compareValue = $value;
                    
                    if(array_key_exists($compareValue, $originalValue)) {
                        $compareValue = $originalValue[$compareValue];
                    }

                    if($fieldType == "string") {
                        $link = $this->adminUrlGenerator
                            ->setController(AnmeldungCrudController::class)
                            ->setAction(Action::INDEX)
                            ->setEntityId(null)
                            ->set('filters['.$fieldName.'][comparison]', '=')
                            ->set('filters['.$fieldName.'][value]', $compareValue)
                            ->generateUrl();
                    } elseif($fieldType == "boolean") {
                        $link = $this->adminUrlGenerator
                            ->setController(AnmeldungCrudController::class)
                            ->setAction(Action::INDEX)
                            ->setEntityId(null)
                            ->set('filters['.$fieldName.']', $compareValue)
                            ->generateUrl();
                    }
                } else {
                    // we add ~12 hours to workaround timezone issues
                    $dateTo = $ruestzeit->getDateTo()->getTimestamp() + 43000;

                    $firstDate = date('Y-m-d', strtotime('-' . ($value).' years -1 day', $dateTo));
                    $lastDate = date('Y-m-d', strtotime('-'.($value + 1).' years +1 day', $dateTo));

                    $link = $this->adminUrlGenerator
                        ->setController(AnmeldungCrudController::class)
                        ->setAction(Action::INDEX)
                        ->setEntityId(null)
                        ->set('filters[birthdate][comparison]', 'between')
                        ->set('filters[birthdate][value]', $firstDate)
                        ->set('filters[birthdate][value2]', $lastDate)
                    ->generateUrl();
        }

                $statistics[$fieldName]["table"][] = [
                    "category" => $value,
                    "count" => $count,
                    "link" => $link
                ];

                $statistics[$fieldName]["chart_data"]["labels"][] = $value;
                $statistics[$fieldName]["chart_data"]["datasets"][0]["data"][] = $count;
            }
        }
        $customFields = $ruestzeit->getCustomFields();

        $anmeldungCustomAnswers = [];
        foreach($anmeldungen as $anmeldung) {
            $customAnswers = $anmeldung->getCustomFieldAnswers();
            $anmeldungCustomAnswers[$anmeldung->getId()] = [];

            foreach($customAnswers as $customAnswer) {
                $customField = $customAnswer->getCustomField();
                
                $anmeldungCustomAnswers[$anmeldung->getId()][$customField->getId()] = $customAnswer->getValue();
            }
        }

        foreach($customFields as $customField) {
            if(
                $customField->getType() == CustomFieldType::CHECKBOX
                || $customField->getType() == CustomFieldType::RADIO
                || $customField->getType() == CustomFieldType::DATE
            ) {
                $key = $customField->getTitle();
                $statistics[$key] = [
                    "title" => $key,
                    "table" => [],
                    "chart_data" => [
                        "labels" => [],
                        "datasets" => [[
                            "label" => $key,
                            "data" => []
                        ]]
                    ]
                ];
    
                $row = [];
                foreach($anmeldungCustomAnswers as $customAnswersPerAnmeldung) {
                    if(array_key_exists($customField->getId(), $customAnswersPerAnmeldung)) {
                        if(
                            $customField->getType() == CustomFieldType::RADIO
                            || $customField->getType() == CustomFieldType::DATE                            
                        ) {
                            $valueCollection = [$customAnswersPerAnmeldung[$customField->getId()]];
                        } elseif($customField->getType() == CustomFieldType::CHECKBOX) {
                            $valueCollection = json_decode($customAnswersPerAnmeldung[$customField->getId()], true);
                        }

                        foreach($valueCollection as $value) {
                            if(!array_key_exists($value, $row)) {
                                $row[$value] = 0;
                            }
            
                            $row[$value]++;
                        }
                    }
                }

                ksort($row);

                foreach($row as $value => $count) {
                    $statistics[$key]["table"][] = [
                        "category" => $value,
                        "count" => $count,
                        "link" => ""
                    ];
    
                    $statistics[$key]["chart_data"]["labels"][] = $value;
                    $statistics[$key]["chart_data"]["datasets"][0]["data"][] = $count;
                }
    
            }
            
        }

        return $this->render('statistiken/index.html.twig', [
            'ruestzeit' => $ruestzeit,
            'controller_name' => 'StatisticController',
            'statistics' => $statistics
        ]);
    }
}
