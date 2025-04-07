<?php
namespace App\Services;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class XlsxWriterFactory
{
    public function create(Spreadsheet $spreadsheet): Xlsx
    {
        return new Xlsx($spreadsheet);
    }
}
