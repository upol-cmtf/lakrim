<?php
namespace App\Http;

trait XlsxHeaders
{
    /**
     * @return string[]
     */
    public function getXlsxHeaders(string $fileName): array
    {
        return [
            'Content-type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => 'attachment; filename=' . $fileName,
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ];
    }
}