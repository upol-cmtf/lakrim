<?php
namespace App\Http;

trait CsvHeaders
{
    /**
     * @return string[]
     */
    public function getCsvHeaders(string $fileName): array
    {
        return [
            'Content-type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename=' . $fileName,
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ];
    }
}
