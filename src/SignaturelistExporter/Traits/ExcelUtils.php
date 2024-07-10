<?php
namespace App\SignaturelistExporter\Traits;

trait ExcelUtils {
    function num2column($n)
    {
        return \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($n);
    }
}