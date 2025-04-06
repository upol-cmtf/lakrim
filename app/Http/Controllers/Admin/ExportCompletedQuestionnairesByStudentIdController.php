<?php
namespace App\Http\Controllers\Admin;

use App\Http\CsvHeaders;
use App\Models\Respondent;
use App\Services\CsvWriterFactory;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use League\Csv\Writer;
use Symfony\Component\HttpFoundation\StreamedResponse;

final class ExportCompletedQuestionnairesByStudentIdController
{
    use CsvHeaders;

    public function __construct(
        private readonly CsvWriterFactory $csvWriterFactory,
    ) {
    }

    public function index(): StreamedResponse
    {
        $data = Respondent::finished()
            ->select('student_id', DB::raw('COUNT(id) AS cnt'))
            ->groupBy('student_id')
            ->get();

        $csv = $this->createCsv($data);

        return response()->stream(
            fn() => $csv->download('export.csv'),
            200,
            $this->getCsvHeaders('export.csv'),
        );
    }

    private function createCsv(Collection $data): Writer
    {
        $csv = $this->csvWriterFactory->create();
        $csv->insertOne([
            'Student ID',
            'Dotazníků',
        ]);

        // @phpstan-ignore-next-line
        $data->each(function (Respondent $record) use ($csv): void {
            assert(isset($record->student_id, $record->cnt));
            $csv->insertOne([$record->student_id, $record->cnt]);
        });

        return $csv;
    }
}
