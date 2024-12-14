<?php

namespace App\Controller\Admin;

use App\Entity\Anmeldung;
use App\Enum\AnmeldungStatus;
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

class AnmeldungImportController extends AbstractController
{
    public function __construct(
        protected CurrentRuestzeitGenerator $currentRuestzeitGenerator
    )
    {
        
    }
    
    #[Route('/admin/anmeldungen/import', name: 'app_anmeldung_import')]
    public function index(): Response
    {
        return $this->render('anmeldung_import/index.html.twig', [
            'controller_name' => 'AnmeldungImportController',
        ]);
    }

    #[Route('/admin/anmeldungen/import/run', name: 'app_anmeldung_import_run')]
    public function run(Request $request, AnmeldungCrudController $controller, EntityManagerInterface $entityManager, RuestzeitRepository $ruestzeitRepository, AdminUrlGenerator $adminUrlGenerator)
    {
        // $entityManager = $this->container->get('doctrine')->getManagerForClass(Anmeldung::class);

        $ruestzeit = $this->currentRuestzeitGenerator->get();

        /** @var UploadedFile $file */
        $file = $request->files->get('import');

        $reader = new \PhpOffice\PhpSpreadsheet\Reader\Xlsx();
        $reader->setReadDataOnly(true);
        $spreadsheet = $reader->load($file->getRealPath());
        $rows = $spreadsheet->getActiveSheet()->toArray();

        $alphabet = "ABCDEFGHIJKLMNOPQRSTUVWXYZ";

        $fields = FieldCollection::new($controller->configureFields(Crud::PAGE_EDIT));

        /** @var FieldTrait[] $fieldObjects */
        $fieldObjects = [];
        $fieldLabels = [];

        foreach ($fields as $field) {
            /** @var FieldTrait $field */
            $fieldObjects[$field->getProperty()] = $field;
            if ($field->getCustomOption('generated')) {
                continue;
            }

            if ($field->getProperty() && $field->getFieldFqcn() != FormField::class) {
                $fieldLabels[$field->getLabel()] = $field->getProperty();
            }
        }

        $fieldSequence = [];

        foreach ($rows[0] as $label) {
            if (!isset($fieldLabels[$label])) {
                throw new Exception('Not compatible. Missing ' . htmlentities($label));
            }

            $fieldSequence[] = $fieldLabels[$label];
        }

        array_shift($rows);

        foreach ($rows as $rowPosition => $row) {
            if (empty($row[0]) && empty($row[1])) {
                // without name, ignore
                continue;
            }

            $anmeldung = new Anmeldung();
            $anmeldung->setRuestzeit($ruestzeit);

            if($ruestzeit->isFull()) {
                $anmeldung->setStatus(AnmeldungStatus::WAITLIST);
            } else {
                $anmeldung->setStatus(AnmeldungStatus::ACTIVE);
            }

            $query = $entityManager->createQueryBuilder('anmeldung');
            $query->from(Anmeldung::class, 'anmeldung');
            $query->select('MAX(anmeldung.registrationPosition) + 1 max_position');
            $query->where('anmeldung.ruestzeit = :ruestzeit')->setParameter('ruestzeit', $ruestzeit);

            $result = $query->getQuery()->getSingleResult();

            if (empty($result) || empty($result['max_position'])) {
                $maxPosition = 0;
            } else {
                $maxPosition = $result['max_position'];
            }

            $anmeldung->setRegistrationPosition($maxPosition + $rowPosition);            

            foreach ($fieldSequence as $index => $field) {
                $property = ucfirst(str_replace('_', '', ucwords($field, '_')));

                $functionName = 'set' . $property;
                $value = $row[$index];

                switch ($fieldObjects[$field]->getFieldFqcn()) {
                    case ChoiceField::class:
                        foreach ($fieldObjects[$field]->getCustomOptions()->get('choices') as $choiceOption) {
                            if ($choiceOption->value == $value) {
                                $value = $choiceOption;
                            }
                        }

                        $anmeldung->$functionName($value);
                        break;
                    case BooleanField::class:
                        $value = strtolower($value);
                        if ($value == 'ja' || $value == 'yes' || $value == 'true' || $value == '1') {
                            $anmeldung->$functionName(true);
                        } else {
                            $anmeldung->$functionName(false);
                        }
                        break;
                    case DateField::class:
                        $date = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($value);

                        $anmeldung->$functionName($date);
                        break;
                    default:

                        $anmeldung->$functionName($value);
                }
            }


            $entityManager->persist($anmeldung);
        }

        $entityManager->flush();

        $this->addFlash('success', 'Import erfolgreich durchgeführt');

        $url = $adminUrlGenerator
            ->setController(AnmeldungCrudController::class)
            ->setAction(Action::INDEX)
            ->generateUrl();
        return new RedirectResponse($url);    }

    #[Route('/admin/anmeldungen/import/preset', name: 'app_anmeldung_import_preset')]
    public function preset(AnmeldungCrudController $controller): Response
    {

        $headers = array(
            "A",
            "B",
            "C",
            "D",
        );

        $response = new StreamedResponse(function () use ($headers, $controller) {
            $spreadsheet = new Spreadsheet();
            $activeWorksheet = $spreadsheet->getActiveSheet();
            $activeWorksheet->getDefaultRowDimension()->setRowHeight(19);

            // $controller = $this->container->get(AnmeldungCrudController::class);

            $fields = FieldCollection::new($controller->configureFields(Crud::PAGE_EDIT));

            $alphabet = "ABCDEFGHIJKLMNOPQRSTUVWXYZ";

            /** @var FieldTrait[] $fieldObjects */
            $fieldObjects = [];
            $fieldList = $headers = [];
            foreach ($fields as $field) {
                /** @var FieldTrait $field */
                $fieldObjects[$field->getProperty()] = $field;
                if ($field->getCustomOption('generated')) {
                    continue;
                }

                if ($field->getLabel() && $field->getFieldFqcn() != FormField::class) {
                    $label = $field->getLabel();

                    $tmp = [
                        'label' => $label,
                    ];

                    $col = substr($alphabet, count($fieldList), 1);

                    if ($field->getFieldFqcn() == ChoiceField::class) {

                        $list = [];
                        foreach ($field->getCustomOptions()->get('choices') as $value) {
                            $list[] = $value->value;
                        }

                        $validation = $spreadsheet->getActiveSheet()->getCell($col . '2')
                            ->getDataValidation();
                        $validation->setType(\PhpOffice\PhpSpreadsheet\Cell\DataValidation::TYPE_LIST);
                        $validation->setErrorStyle(\PhpOffice\PhpSpreadsheet\Cell\DataValidation::STYLE_INFORMATION);
                        $validation->setAllowBlank(false);
                        $validation->setShowInputMessage(true);
                        $validation->setShowErrorMessage(true);
                        $validation->setShowDropDown(true);
                        $validation->setErrorTitle('Eingabefehler');
                        $validation->setError('Nur Werte der Auswahl können verarbeitet werden');
                        //$validation->setPromptTitle('Wähle von Auswahl');
                        $validation->setPrompt('Wähle einen Wert aus der Liste');
                        $validation->setFormula1('"' . implode(',', $list) . '"');

                        $tmp["validator"] = $validation;
                    } elseif ($field->getFieldFqcn() == BooleanField::class) {
                        $validation = $spreadsheet->getActiveSheet()->getCell($col . '2')
                            ->getDataValidation();
                        $validation->setType(\PhpOffice\PhpSpreadsheet\Cell\DataValidation::TYPE_LIST);
                        $validation->setErrorStyle(\PhpOffice\PhpSpreadsheet\Cell\DataValidation::STYLE_INFORMATION);
                        $validation->setAllowBlank(false);
                        $validation->setShowInputMessage(true);
                        $validation->setShowErrorMessage(true);
                        $validation->setShowDropDown(true);
                        $validation->setErrorTitle('Eingabefehler');
                        $validation->setError('Nur Werte der Auswahl können verarbeitet werden');
                        //$validation->setPromptTitle('Wähle von Auswahl');
                        $validation->setPrompt('Wähle einen Wert aus der Liste');
                        $validation->setFormula1('"Ja,Nein"');

                        $tmp["validator"] = $validation;
                    } elseif ($field->getFieldFqcn() == IntegerField::class) {
                        $validation = $spreadsheet->getActiveSheet()->getCell($col . '2')
                            ->getDataValidation();
                        $validation->setType(\PhpOffice\PhpSpreadsheet\Cell\DataValidation::TYPE_DECIMAL);
                        $validation->setErrorStyle(\PhpOffice\PhpSpreadsheet\Cell\DataValidation::STYLE_INFORMATION);
                        $validation->setShowErrorMessage(true);
                        $validation->setErrorTitle('Eingabefehler');
                        $validation->setError('Dieses Feld darf nur eine Zahl enthalten');


                        $tmp["validator"] = $validation;
                    } elseif ($field->getFieldFqcn() == DateField::class) {
                        $validation = $spreadsheet->getActiveSheet()->getCell($col . '2')
                            ->getDataValidation();
                        $validation->setType(\PhpOffice\PhpSpreadsheet\Cell\DataValidation::TYPE_DATE);
                        $validation->setErrorStyle(\PhpOffice\PhpSpreadsheet\Cell\DataValidation::STYLE_INFORMATION);
                        $validation->setShowErrorMessage(true);
                        $validation->setErrorTitle('Eingabefehler');
                        $validation->setError('Dieses Feld darf nur ein Datum enthalten');


                        $tmp["validator"] = $validation;
                    } elseif ($field->getFieldFqcn() == TextField::class) {
                        $tmp["formatCode"] = NumberFormat::FORMAT_TEXT;
                    }

                    if ($field->getCustomOption('xls-width')) {
                        $tmp['width'] = $field->getCustomOption('xls-width');
                    }
                    $fieldList[] = $tmp;
                }
            }

            foreach ($fieldList as $index => $field) {
                $headers[] = $field["label"];

                $col = substr($alphabet, $index, 1);
                //var_dump($col);
                if (!empty($field["width"])) {
                    $activeWorksheet->getColumnDimension($col)->setWidth($field["width"]);
                }
                if (!empty($field["validator"])) {
                    $field["validator"]->setSqref($col . '2:' . $col . '1048576');
                }
                if (!empty($field["formatCode"])) {
                    $activeWorksheet->getStyle($col)->getNumberFormat()
                        ->setFormatCode($field["formatCode"]);
                }

                $activeWorksheet->getColumnDimension($col)->setAutoSize(true);
            }
            $activeWorksheet
                ->fromArray($headers, NULL, 'A1');

            // format table

            $maxCol = substr($alphabet, count($fieldList) - 1, 1);
            $table = new Table('A1:' . $maxCol . '1048576', 'Table1');

            // Optional: apply some styling to the table
            $tableStyle = new TableStyle();
            $tableStyle->setTheme(TableStyle::TABLE_STYLE_MEDIUM9);
            $tableStyle->setShowRowStripes(true);
            $table->setStyle($tableStyle);

            // Add the table to the sheet
            $activeWorksheet->addTable($table);


            $writer = new WriterXlsx($spreadsheet);
            $writer->save('php://output');
        });


        $filename = "anmeldungen.xlsx";

        $dispositionHeader = $response->headers->makeDisposition(
            HeaderUtils::DISPOSITION_ATTACHMENT,
            $filename
        );

        $response->headers->set('Content-Disposition', $dispositionHeader);
        $response->headers->set('Content-Type', 'application/vnd.ms-excel; charset=utf-8');

        return $response;
    }
}
