<?php

namespace App\SignaturelistExporter;

use App\Entity\Anmeldung;
use App\Entity\Ruestzeit;
use App\Enum\AnmeldungStatus;
use App\Enum\PersonenTyp;
use App\SignaturelistExporter\Traits\ExcelUtils;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Color;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx as WriterXlsx;


class LKZwickau extends Base
{
    use ExcelUtils;

    public function getFileExtension()
    {
        return ".xlsx";
    }

    public function generateExport(array $fields, string $filename, array $options)
    {

        $anmeldungen = $this->getAnmeldungen($options);

        $styleArray = array(
            'alignment' => array(
                'vertical' => "center",
                'horizontal' => "left"
            ),
            'borders' => array(
                'outline' => array(
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                    'color' => array('argb' => 'FF000000'),
                ),
                'inside' => array(
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                    'color' => array('argb' => 'FF000000'),
                ),
                'right' => array(
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                    'color' => array('argb' => 'FF000000'),
                ),
                'left' => array(
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                    'color' => array('argb' => 'FF000000'),
                ),
            ),
        );

        $anmeldeListe = $this->getGroups($anmeldungen, $options);
        $reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReader("Xlsx");
        $spreadsheet = $reader->load(__DIR__ . "/../../assets/signaturepresets/lk-zwickau.xlsx");

        $baseSheet = clone $spreadsheet->getSheet(0);
        $spreadsheet->removeSheetByIndex(0);

        foreach ($anmeldeListe as $groupTitle => $anmeldungen) {
            $activeWorksheet = $spreadsheet->addSheet(clone $baseSheet);
            if (!empty($groupTitle)) {
                $activeWorksheet->setTitle(substr($groupTitle, 0, 30));
            }

            $activeWorksheet->setCellValue("A5", $this->ruestzeit->getInternalTitle());
            $activeWorksheet->setCellValue("A7", $this->ruestzeit->getAktenzeichen());
            $activeWorksheet->setCellValue("G5", $this->ruestzeit->getDateFrom()->format("d.m.Y") . " - " . $this->ruestzeit->getDateTo()->format("d.m.Y"));

            $rowIndex = 11;
            $personIndex = 1;
            // $activeWorksheet->removeRow($rowIndex, 100);        
            foreach ($anmeldungen as $index => $anmeldung) {

                if ($anmeldung->getPersonenTyp() == PersonenTyp::MITARBEITER || $anmeldung->getPersonenTyp() == PersonenTyp::REFERENT) {
                    $activeWorksheet->setCellValue("A" . $rowIndex, $personIndex++);
                    $activeWorksheet->setCellValue("B" . $rowIndex, $anmeldung->getLastname() . ", " . $anmeldung->getFirstname());
                    $activeWorksheet->setCellValue("E" . $rowIndex, $anmeldung->getPostalcode() . " " . $anmeldung->getCity());
                    $activeWorksheet->setCellValue("G" . $rowIndex, $anmeldung->getAge());

                    $activeWorksheet->getRowDimension($rowIndex)->setRowHeight(30, "px");
                    $activeWorksheet->getStyle('A' . $rowIndex . ':K' . $rowIndex)->applyFromArray($styleArray);

                    $rowIndex += 1;

                    // We can only have 5 leaders
                    if ($rowIndex == 15) break;
                }
            }

            $rowIndex = 19;
            $personIndex = 1;

            foreach ($anmeldungen as $index => $anmeldung) {
                if ($anmeldung->getPersonenTyp() == PersonenTyp::TEILNEHMER) {
                    $activeWorksheet->setCellValue("A" . $rowIndex, $personIndex++);
                    $activeWorksheet->setCellValue("B" . $rowIndex, $anmeldung->getLastname() . ", " . $anmeldung->getFirstname());
                    $activeWorksheet->setCellValue("E" . $rowIndex, $anmeldung->getPostalcode() . " " . $anmeldung->getCity());
                    $activeWorksheet->setCellValue("G" . $rowIndex, $anmeldung->getAge());

                    $activeWorksheet->mergeCells('B' . $rowIndex . ':D' . $rowIndex);
                    $activeWorksheet->mergeCells('E' . $rowIndex . ':F' . $rowIndex);
                    $activeWorksheet->mergeCells('H' . $rowIndex . ':K' . $rowIndex);

                    $activeWorksheet->getStyle('A' . $rowIndex . ':K' . $rowIndex)->applyFromArray($styleArray);

                    

                    $activeWorksheet->getRowDimension($rowIndex)->setRowHeight(30, "px");
                    $rowIndex += 1;
                }



                // $activeWorksheet
                //     ->getStyle('A' . $rowIndex . ':L' . $rowIndex)
                //     ->getAlignment()
                //     ->setVertical(Alignment::VERTICAL_CENTER);

                // $activeWorksheet
                //     ->getStyle('A' . $rowIndex . ':L' . $rowIndex)
                //     ->getBorders()
                //     ->getAllBorders()
                //     // ->getOutline()
                //     ->setBorderStyle(Border::BORDER_THIN)
                //     ->setColor(new Color('FF000000'));

                // $activeWorksheet->getStyle('A' . $rowIndex . ':K' . $rowIndex)->applyFromArray($styleArray);
                // $activeWorksheet->getRowDimension($rowIndex)->setRowHeight(25.95);

                // $rowIndex += 1;

                // if (
                //     $rowIndex == 25 ||
                //     $rowIndex == 46 ||
                //     $rowIndex > 40 && ($rowIndex - 46) % 21 == 0
                // ) {
                //     // do not add on last page
                //     if ($index < count($anmeldungen) - 1) {
                //         $activeWorksheet->getRowDimension($rowIndex)->setRowHeight(25.95);
                //         $activeWorksheet->getRowDimension($rowIndex + 1)->setRowHeight(25.95);
                //         $activeWorksheet->mergeCells('A' . $rowIndex . ':L' . $rowIndex);
                //         $activeWorksheet->mergeCells('A' . $rowIndex + 1 . ':L' . $rowIndex + 1);
                //         $activeWorksheet->setCellValue("A" . $rowIndex, "Fortsetzung auf der nächsten Seite");

                //         $activeWorksheet
                //             ->getStyle("A" . $rowIndex)
                //             ->getAlignment()
                //             ->setHorizontal(Alignment::HORIZONTAL_CENTER)
                //             ->setVertical(Alignment::VERTICAL_CENTER);

                //         $rowIndex += 1;
                //         $rowIndex += 1;
                //     }
                // }
            }
        }

        // $activeWorksheet->getHeaderFooter()
        //     ->setOddFooter("{IF {PAGE } <> {NUMPAGES} \"\" {PAGE}}")
        //     ->setEvenFooter("{IF {PAGE } <> {NUMPAGES} \"\" {PAGE}}")
        //     ;


        /*
        $activeWorksheet = new Worksheet($spreadsheet, "Tabelle 1");
        
        $activeWorksheet->setCellValue("C1", "o Kinder-RZ/-bibeltage (o mit Ü; o ohne Ü),  o Konfi-/Jugend-RZ, o Familienrüstzeit o Vorb.-treffen RZ");
        $activeWorksheet->getDefaultRowDimension()->setRowHeight(25);


        $activeWorksheet->setCellValue("A1", "Teilnahmeliste");

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

        $activeWorksheet = $spreadsheet->addSheet($activeWorksheet);

        $maxColName = $this->num2column(count($headers));
        $activeWorksheet->getStyle("A1:" . $maxColName . count($anmeldungen))->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);

        $activeWorksheet->getPageSetup()->setOrientation(PageSetup::ORIENTATION_LANDSCAPE);
        $activeWorksheet->getPageSetup()->setFitToWidth(1);

        $activeWorksheet->getPageMargins()->setTop(1);
        $activeWorksheet->getPageMargins()->setRight(0.5);
        $activeWorksheet->getPageMargins()->setLeft(0.5);
        $activeWorksheet->getPageMargins()->setBottom(1);
*/

        $writer = new WriterXlsx($spreadsheet);

        header('Content-type: application/ms-excel');
        header('Content-Disposition: attachment; filename=' . $filename);

        $writer->save('php://output');
        exit();
    }
}
