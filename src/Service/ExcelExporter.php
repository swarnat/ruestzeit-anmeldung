<?php

namespace App\Service;

use App\Entity\Ruestzeit;
use App\Enum\MealType;
use App\Enum\AnmeldungStatus;
use App\Enum\PersonenTyp;
use App\Enum\RoomType;
use App\Generator\CurrentRuestzeitGenerator;
use App\Repository\CategoryRepository;
use DateTime;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\QueryBuilder;
use EasyCorp\Bundle\EasyAdminBundle\Field\FieldTrait;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FieldCollection;
use EasyCorp\Bundle\EasyAdminBundle\Contracts\Field\FieldInterface;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use Goodby\CSV\Export\Standard\Exporter;
use Goodby\CSV\Export\Standard\ExporterConfig;
use PhpOffice\PhpSpreadsheet\Calculation\Calculation;
use PhpOffice\PhpSpreadsheet\Cell\Cell;
use PhpOffice\PhpSpreadsheet\Reader\Xlsx;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Table;
use PhpOffice\PhpSpreadsheet\Worksheet\Table\TableStyle;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx as WriterXlsx;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\HeaderUtils;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\Translation\Translator;
use Symfony\Contracts\Translation\TranslatorInterface;

class ExcelExporter
{
    
    public function __construct(
        private TranslatorInterface $translator,
        private CategoryRepository $categoryRepository,
        protected CurrentRuestzeitGenerator $currentRuestzeitGenerator
    )
    {}

    function num2column($n)
    {
        return \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($n);
    }

    public function createResponseFromQueryBuilder(QueryBuilder $queryBuilder, FieldCollection $fields, string $filename): Response
    {
        $result = $queryBuilder->getQuery()->getArrayResult();

        $currentRuestzeit = $this->currentRuestzeitGenerator->get();

        // echo "<pre>";

        /** @var FieldTrait[] $fieldObjects */
        $fieldObjects = [];

        $columnData = [];

        foreach($fields as $field) {
            /** @var FieldTrait $field */
            $fieldObjects[$field->getProperty()] = $field;
        }

        $categories = $this->categoryRepository->findAll();

        $categoryMapping = [];
        foreach($categories as $i => $category) {
            $conditional = new \PhpOffice\PhpSpreadsheet\Style\Conditional();
            $conditional->setConditionType(\PhpOffice\PhpSpreadsheet\Style\Conditional::CONDITION_NOTCONTAINSBLANKS);
            // $conditional->setOperatorType(\PhpOffice\PhpSpreadsheet\Style\Conditional::OPERATOR_GREATERTHAN);
            // $conditional->addCondition(80);
            // $conditional->getStyle()->getFont()->getColor()->setARGB(\PhpOffice\PhpSpreadsheet\Style\Color::COLOR_DARKGREEN);

            $conditional->getStyle()->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID);
            $conditional->getStyle()->getFill()->getEndColor()->setRGB(substr($category->getColor(), 1));

            $conditional->getStyle('B2')->getFont()->getColor()->setRGB(substr($category->getTextcolor(), 1));
            
            $conditionalStyles = [];
            $conditionalStyles[] = $conditional;

            $columnData["category_" . $i] = $categoryMapping[$category->getTitle()] = [
                "index" => "category_" . $i,
                "conditionalStyles" => $conditionalStyles,
                "countnotempty" => true,
                "wrap" => false,
                "width" => 15
            ];
        }

        $hiddenFields = [];
        if($currentRuestzeit->isShowRoomRequest() === false) {
            $hiddenFields[] = "roomRequest";
        }
        if($currentRuestzeit->isShowMealtype() === false) {
            $hiddenFields[] = "mealtype";
        }
        if($currentRuestzeit->isShowReferer() === false) {
            $hiddenFields[] = "referer";
        }
        if($currentRuestzeit->isShowRoommate() === false) {
            $hiddenFields[] = "roommate";
        }

        $customFields = $currentRuestzeit->getCustomFields();

        // Convert DateTime objects into strings
        $data = [];
        $columns = [];
        foreach ($result as $index => $row) {
            foreach ($row as $columnKey => $columnValue) {
                if(in_array($columnKey, $hiddenFields)) {
                    continue;
                }

                if(is_object($columnValue)) {

                    if( $columnValue instanceof MealType || 
                        $columnValue instanceof AnmeldungStatus || 
                        $columnValue instanceof PersonenTyp ||
                        $columnValue instanceof RoomType
                    ) {
                        $columnValue = \Symfony\Component\Translation\t($columnValue->value, [], 'messages')->trans($this->translator);
                    } elseif(
                        $columnValue instanceof \DateTimeInterface || 
                        $columnValue instanceof \DateTimeImmutable
                    ) {

                        if(array_key_exists($columnKey, $fieldObjects)) {
                            if($fieldObjects[$columnKey]->getFieldFqcn() == DateField::class) {
                                $columnValue = $columnValue->format('d.m.Y');
                            } elseif($fieldObjects[$columnKey]->getFieldFqcn() == DateTimeField::class) {
                                $columnValue = $columnValue->format('d.m.Y H:i:s');
                            }
                        }

                    }

                } elseif(is_bool($columnValue)) {
                    $columnValue = $columnValue ? "Ja" : "Nein";
                } elseif(is_array($columnValue) && in_array($columnKey, ["categories"]) !== false) {
                    // ignore some columns, when they are relations
                    continue;
                }

                $data[$index][] = $columnValue;

                $columns[$columnKey] = $columnKey;
            }



            $tmpCategories = [];
            foreach($row["categories"] as $rowCategory) {
                $tmpCategories[$rowCategory["title"]] = true;
            }

            foreach($categoryMapping as $title => $colData) {
                if(isset($tmpCategories[$title])) {
                    $data[$index][] = "Ja";
                } else {
                    $data[$index][] = "";
                }
            }

        }

        $conditionalStyles = [];
        
        // Humanize headers based on column labels in EA
        if (isset($data[0])) {
            $headers = [];
            $properties = array_keys($columns);
            foreach ($properties as $index => $property) {
                if(in_array($property, ["categories"]) !== false) {
                    continue;
                }

                $columnData[$index] = [
                    "index" => $property,
                    "countnotempty" => false,
                    "name" => $property,
                    "wrap" => $property == "notes",
                    "width" => $property == "notes" ? 70 : 15
                ];

                $headers[$index] = ucfirst($property);
                foreach ($fields as $field) {
                    // Override property name if a custom label was set
                    if ($property === $field->getProperty() && $field->getLabel()) {
                        $headers[$index] = $field->getLabel();
                        // And stop iterating
                        break;
                    }
                }
            }

            foreach($categoryMapping as $title => $colData) {
                $headers[$colData["index"]] = $title;
                $conditionalStyles[$colData["index"]] = $colData["conditionalStyles"];
            }
    
            // Add headers to the final data array
            // array_unshift($data, $headers);
        }

        $colCounter = 1;

        $sumRow = [];

        foreach($headers as $colIndex => $_notused) {

            $colId = $this->num2column($colCounter);
            
            if(isset($columnData[$colIndex])) {
                if(!empty($columnData[$colIndex]["countnotempty"]) || $colCounter == 1) {
                    $sumRow[] = '=COUNTIF('.$colId.'2:'.$colId.'' . (count($data) + 1) . ',"<>")';
                } else {
                    $sumRow[] = '';
                }
            } else {
                $sumRow[] = '';
            }
            
            $colCounter++;
        }

        // echo "<pre>";
        // var_dump($categoryMapping, $headers, $data);
        // exit();
        

        $response = new StreamedResponse(function () use ($headers, $data, $conditionalStyles, $sumRow, $columnData) {
            $spreadsheet = new Spreadsheet();
            $activeWorksheet = $spreadsheet->getActiveSheet();
            
            $activeWorksheet
                ->fromArray($headers, NULL, 'A1');

            foreach($data as $rowId => $rowData) {
                $colIndex = 0;
                foreach($headers as $colId => $_notused) {
                    $colName = $this->num2column($colIndex + 1);

                    $activeWorksheet->setCellValueExplicit($colName . ($rowId + 2), $rowData[$colIndex], \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);

                    if($columnData[$colId]["wrap"]) {
                        $activeWorksheet->getStyle($colName . ($rowId + 2))->getAlignment()->setWrapText(true);
                    }

                    $colIndex++;
                }
            }

            $alphabet = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';


            $colIndex = 1;
            foreach($headers as $index => $_unused) {
                $colId = $this->num2column($colIndex);                

                if(isset($conditionalStyles[$index])) {

                    $cellStyle = $activeWorksheet->getStyle($colId . '2:' . $colId . "" . (count($data) + 100));
                    // $cellStyle = $activeWorksheet->getStyle("A2:A100");

                    $cellStyle->setConditionalStyles($conditionalStyles[$index]);
                }
                

                if($columnData[$index]["width"]) {
                    $activeWorksheet->getColumnDimension($colId)->setWidth($columnData[$index]["width"]);
                }

                $colIndex++;
            }

            $maxColumn = $this->num2column(count($headers));

            Calculation::getInstance($spreadsheet)->disableCalculationCache();
            $table = new Table();
            $tableStyle = new TableStyle();
            $tableStyle->setTheme(TableStyle::TABLE_STYLE_MEDIUM9);
            $tableStyle->setShowRowStripes(true);
            $table->setStyle($tableStyle);   
            $table->setShowTotalsRow(true);
            $table->setRange('A1:' . $maxColumn . count($data) + 2);
            
            foreach($sumRow as $colIndex => $col) {
                $colId = $this->num2column($colIndex + 1);

                if(!empty($col)) {
                    $table->getColumn($colId)->setTotalsRowFunction('count');
                }
 
            }

            $activeWorksheet->addTable($table);
            $table->setName("Anmeldungen");
            
            Calculation::getInstance($spreadsheet)->clearCalculationCache();

            $writer = new WriterXlsx($spreadsheet);
            $writer->save('php://output');
  
        });

        $dispositionHeader = $response->headers->makeDisposition(
            HeaderUtils::DISPOSITION_ATTACHMENT,
            $filename
        );
        
        $response->headers->set('Content-Disposition', $dispositionHeader);
        $response->headers->set('Content-Type', 'application/vnd.ms-excel; charset=utf-8');

        return $response;
    }
}
