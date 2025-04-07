<?php
namespace App\Services;

use PhpOffice\PhpSpreadsheet\Spreadsheet;

class SpreadSheetFactory
{
    public function create(): Spreadsheet
    {
        return new Spreadsheet;
    }
}
