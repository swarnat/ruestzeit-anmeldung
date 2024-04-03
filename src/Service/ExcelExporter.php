<?php

namespace App\Service;

use App\Enum\MealType;
use App\Enum\AnmeldungStatus;
use DateTime;
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
        private TranslatorInterface $translator
    )
    {}

    public function createResponseFromQueryBuilder(QueryBuilder $queryBuilder, FieldCollection $fields, string $filename): Response
    {
        $result = $queryBuilder->getQuery()->getArrayResult();

        /** @var FieldTrait[] $fieldObjects */
        $fieldObjects = [];

        foreach($fields as $field) {
            /** @var FieldTrait $field */
            $fieldObjects[$field->getProperty()] = $field;
        }

        // Convert DateTime objects into strings
        $data = [];
        $columns = [];
        foreach ($result as $index => $row) {
            foreach ($row as $columnKey => $columnValue) {
                if(is_object($columnValue)) {
                    if($columnValue instanceof MealType || $columnValue instanceof AnmeldungStatus) {
                        $columnValue = \Symfony\Component\Translation\t($columnValue->value, [], 'messages')->trans($this->translator);
                    } elseif($columnValue instanceof \DateTimeInterface) {

                        if(!empty($fieldObjects[$columnKey])) {
                            if($fieldObjects[$columnKey]->getFieldFqcn() == DateField::class) {
                                $columnValue = $columnValue->format('d.m.Y');
                            } elseif($fieldObjects[$columnKey]->getFieldFqcn() == DateTimeField::class) {
                                $columnValue = $columnValue->format('d.m.Y H:i:s');
                            }
                        }                        
                        
                    }
                } elseif(is_bool($columnValue)) {
                    $columnValue = $columnValue ? "Ja" : "Nein";
                }

                $data[$index][] = $columnValue;
                
                $columns[$columnKey] = $columnKey;
            }
        }

        // Humanize headers based on column labels in EA
        if (isset($data[0])) {
            $headers = [];
            $properties = array_keys($columns);
            foreach ($properties as $index => $property) {
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
            // Add headers to the final data array
            // array_unshift($data, $headers);
        }

        // var_dump($headers, $data);
        // exit();
        $response = new StreamedResponse(function () use ($headers, $data) {
            $spreadsheet = new Spreadsheet();
            $activeWorksheet = $spreadsheet->getActiveSheet();
                    
            $activeWorksheet
                ->fromArray($headers, NULL, 'A1')
                ->fromArray($data, NULL, 'A2');

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
