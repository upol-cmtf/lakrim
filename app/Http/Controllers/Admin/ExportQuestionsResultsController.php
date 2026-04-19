<?php
namespace App\Http\Controllers\Admin;

use App\Enums\Sex;
use App\Http\XlsxHeaders;
use App\Models\Question;
use App\Models\Respondent;
use App\Services\SpreadSheetFactory;
use App\Services\XlsxWriterFactory;
use Illuminate\Http\Response;
use Illuminate\Support\Collection;
use PhpOffice\PhpSpreadsheet\Style\Color;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Symfony\Component\HttpFoundation\StreamedResponse;

final class ExportQuestionsResultsController
{
    use XlsxHeaders;

    /** @var array<int, string> */
    private array $columnQuestionTimeMap = [];

    /** @var array<int, string> */
    private array $columnQuestionOptionMap = [];

    public function __construct(
        private readonly SpreadSheetFactory $spreadSheetFactory,
        private readonly XlsxWriterFactory $xlsxWriterFactory,
    ) {
    }

    public function index(): StreamedResponse
    {
        $questions = Question::get();

        $spreadSheet = $this->spreadSheetFactory->create();

        $sheet = $spreadSheet->getActiveSheet();

        $this->writeHeaders($sheet, $questions);
        $sheet->freezePane('A3');

        $this->writeData(Respondent::get(), $sheet);

        $writer = $this->xlsxWriterFactory->create($spreadSheet);

        return response()->stream(
            fn() => $writer->save('php://output'),
            Response::HTTP_OK,
            $this->getXlsxHeaders('export.xlsx'),
        );
    }

    private function writeHeaders(Worksheet $sheet, Collection $questions): void
    {
        $nextColumn = function (string $column, int $increment): string {
            /** @phpstan-var non-empty-string $column */
            for ($i = 0; $i <= $increment; $i++) {
                $column = str_increment($column);
            }

            return $column;
        };

        $column = 'G';
        foreach ($questions as $key => $question) {
            assert($question instanceof Question);

            $optionCount = $question->options->count();

            $actualColumn = $column;
            $column = $nextColumn($column, $optionCount);

            $sheet->mergeCells($actualColumn . '1:' . $column . '1');
            $sheet->setCellValue($actualColumn . '1', ($key + 1) . '. ' . strip_tags($question->description));

            $sheet->setCellValue($actualColumn . '2', 'Čas odpovědi (s)');
            $this->columnQuestionTimeMap[$question->id] = $actualColumn;

            foreach ($question->options as $option) {
                $actualColumn = str_increment($actualColumn);

                $this->columnQuestionOptionMap[$option->id] = $actualColumn;

                $sheet->setCellValue(
                    $actualColumn . '2',
                    $option->name . ' (' . ($option->right ? 'správně' : 'chyba') . ')',
                );
            }

            $column = str_increment($column);
        }

        $sheet->setCellValue('A2', 'ID');
        $sheet->setCellValue('B2', 'Student ID');
        $sheet->setCellValue('C2', 'Dokončeno');
        $sheet->setCellValue('D2', 'Pohlaví');
        $sheet->setCellValue('E2', 'Věk');
        $sheet->setCellValue('F2', 'Úspěšnost (správně/chybně)');
    }

    private function writeData(Collection $respondents, Worksheet $sheet): void
    {
        $line = 3;

        foreach ($respondents as $respondent) {
            assert($respondent instanceof Respondent);

            $sheet->setCellValue('A' . $line, $respondent->id);
            $sheet->setCellValue('B' . $line, $respondent->student_id);
            $sheet->setCellValue('C' . $line, $respondent->finished ? 'ano' : 'ne');
            $sheet->setCellValue('D' . $line, $this->getSex($respondent));
            $sheet->setCellValue('E' . $line, $respondent->age->name ?? 'nezadáno');

            $finalSummary = function (Respondent $respondent) {
                $right = $wrong = 0;
                foreach ($respondent->answers as $answer) {
                    if ($answer->option->right) {
                        $right++;
                    } else {
                        $wrong++;
                    }
                }

                return $right . '/' . $wrong;
            };

            $sheet->setCellValue('F' . $line, $finalSummary($respondent));

            foreach ($respondent->answers as $answer) {
                $questionId = $answer->option->question_id;

                $sheet->setCellValue(
                    $this->columnQuestionTimeMap[$questionId] . $line,
                    $answer->seconds,
                );

                $cellCoordinate = $this->columnQuestionOptionMap[$answer->option->id] . $line;
                $cellColor = $answer->option->right ? Color::COLOR_DARKGREEN : Color::COLOR_DARKRED;

                $sheet->getStyle($cellCoordinate)->getFont()->getColor()->setARGB($cellColor);
                $sheet->getStyle($cellCoordinate)->getAlignment()->setHorizontal('center');
                $sheet->setCellValue($cellCoordinate, 'X');
            }

            $line++;
        }
    }

    private function getSex(Respondent $respondent): string
    {
        if (!$respondent->sex) {
            return 'nezadáno';
        }

        return $respondent->sex === Sex::Male->value ? 'Muž' : 'Žena';
    }
}
