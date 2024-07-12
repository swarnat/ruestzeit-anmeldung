<?php

namespace App\Service;

use App\Entity\Ruestzeit;
use App\Enum\MealType;
use App\Enum\AnmeldungStatus;
use App\SignaturelistExporter\Base;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use EasyCorp\Bundle\EasyAdminBundle\Field\FieldTrait;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FieldCollection;
use EasyCorp\Bundle\EasyAdminBundle\Contracts\Field\FieldInterface;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use Goodby\CSV\Export\Standard\Exporter;
use Goodby\CSV\Export\Standard\ExporterConfig;
use PhpOffice\PhpSpreadsheet\Reader\Xlsx;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Worksheet\PageSetup;
use PhpOffice\PhpSpreadsheet\Worksheet\Table;
use PhpOffice\PhpSpreadsheet\Worksheet\Table\TableStyle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx as WriterXlsx;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\HeaderUtils;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\Translation\Translator;
use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\Environment;

class SignaturelistExporter
{

    public function __construct(
        private TranslatorInterface $translator,
        private Environment $twig,
        protected EntityManagerInterface $entityManager
    ) {
    }

    function num2column($n)
    {
        return \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($n);
    }

    /**
     * Get exporter for SignatureList
     *
     * @param string $presetName
     * @return Base
     */
    public function getSignaturelistExporter($presetName, Ruestzeit $ruestzeit) {
        switch($presetName) {
            case "preset1":
                $className = '\App\SignaturelistExporter\Preset1';
                break;
            case "lkerz":
                $className = '\App\SignaturelistExporter\LKErz';
                break;
            case "lkzwickau":
                $className = '\App\SignaturelistExporter\LKZwickau';
                break;
        }

        if(empty($className)) {
            throw new \Exception("Requested signaturelist not found");
        }
        return new $className($ruestzeit, $this->entityManager, $this->translator);
    }

    public function generateXLS(Ruestzeit $ruestzeit, array $fields, string $filename, array $options)
    {
        $query = $this->entityManager->createQuery('SELECT a FROM App\Entity\Anmeldung a WHERE a.ruestzeit = ' . $ruestzeit->getId() . " AND a.status = '" . AnmeldungStatus::ACTIVE->value . "' ORDER BY a.lastname, a.firstname");
        $anmeldungen = $query->getResult();

        $anmeldeListe = $this->getGroups($anmeldungen, $options);

        $spreadsheet = new Spreadsheet();
        $spreadsheet->removeSheetByIndex(0);

        $headers = [];
        foreach ($fields as $field) {
            $headers[] = $field->getLabel();
        }
        $headers[] = "Unterschrift";

        foreach ($anmeldeListe as $groupTitle => $anmeldungen) {
            $activeWorksheet = new Worksheet($spreadsheet, substr($groupTitle, 0, 30));
            $activeWorksheet->getDefaultRowDimension()->setRowHeight(25);
            
            foreach($fields as $colId => $field) {
                $colName = $this->num2column($colId + 1);
                $activeWorksheet->getColumnDimension($colName)->setWidth(20);
            }

            $colName = $this->num2column(count($headers));
            $activeWorksheet->getColumnDimension($colName)->setWidth(35);

            $activeWorksheet->getHeaderFooter()
                ->setOddHeader($groupTitle)
                ->setEvenHeader($groupTitle);

            $activeWorksheet
                ->fromArray($headers, NULL, 'A1');

            foreach ($anmeldungen as $rowId => $rowData) {
                $colIndex = 0;

                foreach ($fields as $colIndex => $field) {
                    $colName = $this->num2column($colIndex + 1);
    
                    $functionName = "get" . ucfirst($field->getProperty());
                    $activeWorksheet->setCellValueExplicit($colName . ($rowId + 2), $rowData->$functionName(), \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
                    
                    $colIndex++;
                }
            }

            $maxColumn = $this->num2column(count($headers));

            $table = new Table();
            $tableStyle = new TableStyle();
            $tableStyle->setTheme(TableStyle::TABLE_STYLE_LIGHT8);
            $tableStyle->setShowRowStripes(true);
            $table->setStyle($tableStyle);   
            // $table->setShowTotalsRow(true);
            $table->setRange('A1:' . $maxColumn . count($anmeldungen) + 1);
            $activeWorksheet->addTable($table);

            $activeWorksheet = $spreadsheet->addSheet($activeWorksheet);

            $maxColName = $this->num2column(count($headers));
            $activeWorksheet->getStyle("A1:" . $maxColName . count($anmeldungen))->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);

            $activeWorksheet->getPageSetup()->setOrientation(PageSetup::ORIENTATION_LANDSCAPE);
            $activeWorksheet->getPageSetup()->setFitToWidth(1);
            
            $activeWorksheet->getPageMargins()->setTop(1);
            $activeWorksheet->getPageMargins()->setRight(0.5);
            $activeWorksheet->getPageMargins()->setLeft(0.5);
            $activeWorksheet->getPageMargins()->setBottom(1);


        }

        $writer = new WriterXlsx($spreadsheet);


        header('Content-type: application/ms-excel');
        header('Content-Disposition: attachment; filename='.$filename);

        $writer->save('php://output');
        exit();
    }

    private function getGroups($anmeldungen, $options): array
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

    public function generatePDF(Ruestzeit $ruestzeit, array $fields, string $filename, array $options)
    {
        $query = $this->entityManager->createQuery('SELECT a FROM App\Entity\Anmeldung a WHERE a.ruestzeit = ' . $ruestzeit->getId() . " AND a.status = '" . AnmeldungStatus::ACTIVE->value . "' ORDER BY a.lastname, a.firstname");
        $anmeldungen = $query->getResult();

        $anmeldeListe = $this->getGroups($anmeldungen, $options);

        $template = $this->twig->render('unterschriften/signaturelist.html.twig', [
            'fields' => $fields,
            'groups' => $anmeldeListe,
            'ruestzeit' => $ruestzeit
        ]);

        $mpdf = new \Mpdf\Mpdf([
            'margin_left' => 10,
            'margin_top' => 15,
            'margin_right' => 10,
            'margin_bottom' => 15,
            'margin_header' => 7,
            'margin_footer' => 5,
            'default_font_size' => 10,
            'orientation' => 'L'
        ]);
        $mpdf->SetHTMLHeader('<table style="width:100%;" cellspacing=0 cellpadding=0><tr><td>' . $ruestzeit->getInternalTitle() . '</td><td align="right">' . $ruestzeit->getDateFrom()->format("d.m.Y") . ' - ' . $ruestzeit->getDateTo()->format("d.m.Y") . '</td></tr></table>');
        $mpdf->SetHTMLFooter('<table style="width:100%;" cellspacing=0 cellpadding=0><tr><td>{DATE j.m.Y H:m:s}</td><td align="right">Seite {PAGENO} / {nbpg}</td></tr></table>');

        $mpdf->WriteHTML($template);

        $mpdf->Output();

        exit();
    }
}
