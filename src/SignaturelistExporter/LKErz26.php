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


class LKErz26 extends Base
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
            'borders' => array(
                'outline' => array(
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                    'color' => array('argb' => 'FF000000'),
                ),
            ),
        );

        $anmeldeListe = $this->getGroups($anmeldungen, $options);
        $reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReader("Xlsx");
        $spreadsheet = $reader->load(__DIR__ . "/../../assets/signaturepresets/lk-erz-2026.xlsx");

        $baseSheet = clone $spreadsheet->getSheet(0);
        $spreadsheet->removeSheetByIndex(0);
        
        foreach ($anmeldeListe as $groupTitle => $anmeldungen) {
            $rowIndex = 9;            
            $activeWorksheet = $spreadsheet->addSheet(clone $baseSheet);
            if(!empty($groupTitle)) {
                $activeWorksheet->setTitle(substr($groupTitle, 0, 30));
            }

            // $activeWorksheet->setCellValue("C7", $this->ruestzeit->getInternalTitle());
            // $activeWorksheet->setCellValue("B9", $this->ruestzeit->getLocation()->getCity());

            $activeWorksheet->setCellValue("E6", $this->ruestzeit->getDateFrom()->format("d.m.Y"));
            $activeWorksheet->setCellValue("G6", $this->ruestzeit->getDateTo()->format("d.m.Y"));

            // $activeWorksheet->removeRow($rowIndex, 100);        
            foreach ($anmeldungen as $index => $anmeldung) {

                $activeWorksheet->mergeCells("B" . $rowIndex . ":C" . $rowIndex);
                $activeWorksheet->mergeCells("D" . $rowIndex . ":F" . $rowIndex);

                $activeWorksheet->setCellValue("B" . $rowIndex, $anmeldung->getLastname() . ", " . $anmeldung->getFirstname());    
                $activeWorksheet->setCellValue("D" . $rowIndex, $anmeldung->getPostalcode() . " " . $anmeldung->getCity());
                $activeWorksheet->setCellValue("G" . $rowIndex, $anmeldung->getAge());

                if($anmeldung->getPersonenTyp() == PersonenTyp::MITARBEITER) {
                    $activeWorksheet->setCellValue("H" . $rowIndex, "Betreuer");
                } elseif($anmeldung->getPersonenTyp() == PersonenTyp::REFERENT) {
                    $activeWorksheet->setCellValue("H" . $rowIndex, "Referent");
                } else {
                    $activeWorksheet->setCellValue("H" . $rowIndex, "Teilnehmer");
                }
                
                if($rowIndex == 21) $rowIndex = 27;
                if($rowIndex == 44) $rowIndex = 48;
                if($rowIndex == 65) $rowIndex = 69;
    
                $rowIndex++;
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
