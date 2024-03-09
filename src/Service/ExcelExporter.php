<?php

namespace App\Service;

use Doctrine\ORM\QueryBuilder;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FieldCollection;
use Goodby\CSV\Export\Standard\Exporter;
use Goodby\CSV\Export\Standard\ExporterConfig;
use PhpOffice\PhpSpreadsheet\Reader\Xlsx;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx as WriterXlsx;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\HeaderUtils;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ExcelExporter
{
    public function createResponseFromQueryBuilder(QueryBuilder $queryBuilder, FieldCollection $fields, string $filename): Response
    {
        $result = $queryBuilder->getQuery()->getArrayResult();
        // Convert DateTime objects into strings
        $data = [];
        $columns = [];
        foreach ($result as $index => $row) {
            foreach ($row as $columnKey => $columnValue) {
                $data[$index][] = $columnValue instanceof \DateTimeInterface
                    ? $columnValue->format('Y-m-d H:i:s')
                    : $columnValue;
                
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
