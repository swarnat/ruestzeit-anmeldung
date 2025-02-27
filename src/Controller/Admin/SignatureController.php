<?php

namespace App\Controller\Admin;

use App\Entity\Anmeldung;
use App\Enum\AnmeldungStatus;
use App\Generator\CurrentRuestzeitGenerator;
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

class SignatureController extends AbstractController
{
    public function __construct(
        protected CurrentRuestzeitGenerator $currentRuestzeitGenerator
    )
    {
        
    }
    
    #[Route('/anmeldung/unterschriften', name: 'app_anmeldung_unterschriften')]
    public function index( EntityManagerInterface $entityManager, AnmeldungCrudController $controller, RuestzeitRepository $ruestzeitRepository): Response
    {
        $fields = FieldCollection::new($controller->configureFields(Crud::PAGE_DETAIL));

        $ruestzeit = $this->currentRuestzeitGenerator->get();

        foreach ($fields as $field) {
            /** @var FieldTrait $field */
            $fieldObjects[$field->getProperty()] = $field;

            if ($field->getProperty() && $field->getFieldFqcn() != FormField::class) {
                $fieldLabels[$field->getLabel()] = $field->getProperty();
            }
        }

        $query = $entityManager->createQuery('SELECT a FROM App\Entity\Anmeldung a WHERE a.ruestzeit = ' . $ruestzeit->getId() . " AND a.status = '" . AnmeldungStatus::ACTIVE->value . "' GROUP BY a.landkreis");
        $anmeldungen = $query->getResult();
        $landkreise = array_map(function($value) { return $value->getLandkreis(); }, $anmeldungen);

        $preselectedFields = ["lastname", "firstname", "address", "postalcode", "city", "age"];

        return $this->render('unterschriften/index.html.twig', [
            "fields" => $fieldLabels,
            "preselected_fields" => $preselectedFields,
            "landkreise" => $landkreise,
        ]);
    }

    #[Route('/anmeldung/unterschriften/run', name: 'app_anmeldung_unterschriften_run')]
    public function run(Request $request, AnmeldungCrudController $controller, SignaturelistExporter $exporter, EntityManagerInterface $entityManager, RuestzeitRepository $ruestzeitRepository, AdminUrlGenerator $adminUrlGenerator)
    {
        $fields = FieldCollection::new($controller->configureFields(Crud::PAGE_DETAIL));

        $selectedFields = $request->get("fields");
        $options = $request->get("options", []);
        $format = $request->get("format", []);

        $reportFields = [];

        foreach ($selectedFields as $selField) {
            foreach ($fields as $field) {
                if ($field->getProperty() == $selField) {
                    $reportFields[] = $field;
                }

                if (!empty($options["split"]) && $options["split"] == $field->getProperty()) {
                    $options["group_prefix"] = $field->getLabel();
                }
            }
        }

        $ruestzeit = $this->currentRuestzeitGenerator->get();

        if ($format == "xls") {
            $exporter->generateXLS($ruestzeit, $reportFields, 'Unterschriften.xlsx', $options);
        } elseif ($format == "xls-preset1") {
            $signatureExporter = $exporter->getSignaturelistExporter("preset1", $ruestzeit); // ($ruestzeit, $reportFields, 'Unterschriften.pdf', $options);
            $signatureExporter->generateExport($reportFields, 'Unterschriften.xlsx', $options);
        } elseif ($format == "xls-lkerz") {
            $signatureExporter = $exporter->getSignaturelistExporter("lkerz", $ruestzeit); // ($ruestzeit, $reportFields, 'Unterschriften.pdf', $options);
            $signatureExporter->generateExport($reportFields, 'Unterschriften.xlsx', $options);
        } elseif ($format == "xls-lkzwickau") {
            $signatureExporter = $exporter->getSignaturelistExporter("lkzwickau", $ruestzeit); // ($ruestzeit, $reportFields, 'Unterschriften.pdf', $options);
            $signatureExporter->generateExport($reportFields, 'Unterschriften.xlsx', $options);
        } elseif ($format == "pdf") {
            $exporter->generatePDF($ruestzeit, $reportFields, 'Unterschriften.pdf', $options);
        }

        return new Response("invalid_request");
    }
}
