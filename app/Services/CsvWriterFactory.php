<?php
namespace App\Services;

use League\Csv\Bom;
use League\Csv\Writer;

class CsvWriterFactory
{
    public function create(): Writer
    {
        $csv = Writer::createFromString();
        $csv->setOutputBOM(Bom::Utf8);
        $csv->setDelimiter(';');

        return $csv;
    }
}
