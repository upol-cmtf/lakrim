<?php
namespace App\Services\Export;

use App\Enums\Sex;
use App\Models\Question;
use App\Models\QuizEvent;
use App\Models\Respondent;
use App\Models\RespondentAnswer;
use App\Services\SpreadSheetFactory;
use App\Services\XlsxWriterFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Str;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Color;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

/**
 * Export výsledků dle otázek do XLSX: řádek = respondent, sloupce = otázky a jejich
 * možnosti (čas odpovědi a označení zvolené možnosti). Volitelně jen respondenti
 * z událostí (kurzů) daného názvu.
 *
 * Sdílí ho webový export v administraci i konzolový příkaz export:questions-results;
 * data načítá s eager loadingem a po dávkách, aby export zvládl i velký počet respondentů.
 */
final class QuestionsResultsExporter
{
    /** Kolik respondentů se načítá z databáze najednou. */
    private const CHUNK = 200;

    /** @var array<int, string> */
    private array $columnQuestionTimeMap = [];

    /** @var array<int, string> */
    private array $columnQuestionOptionMap = [];

    public function __construct(
        private readonly SpreadSheetFactory $spreadSheetFactory,
        private readonly XlsxWriterFactory $xlsxWriterFactory,
    ) {
    }

    /**
     * Sestaví sešit v paměti (pro streamování do prohlížeče).
     */
    public function build(?string $eventName = null): Spreadsheet
    {
        $this->columnQuestionTimeMap = [];
        $this->columnQuestionOptionMap = [];

        /** @var Collection<int, Question> $questions */
        $questions = Question::query()->with('options')->orderBy('id')->get();

        $spreadSheet = $this->spreadSheetFactory->create();
        $sheet = $spreadSheet->getActiveSheet();

        $this->writeHeaders($sheet, $questions);
        $sheet->freezePane('A3');
        $this->writeData($sheet, $eventName);

        return $spreadSheet;
    }

    /**
     * Sestaví sešit a uloží ho do souboru v dané složce. Vrátí cestu k souboru.
     */
    public function saveToDirectory(string $directory, ?string $eventName = null): string
    {
        if (!is_dir($directory) && !mkdir($directory, 0775, true) && !is_dir($directory)) {
            throw new \RuntimeException(sprintf('Složku %s nelze vytvořit.', $directory));
        }

        $path = rtrim($directory, DIRECTORY_SEPARATOR)
            . DIRECTORY_SEPARATOR
            . $this->fileName($eventName, withTimestamp: true);

        $this->xlsxWriterFactory->create($this->build($eventName))->save($path);

        return $path;
    }

    /**
     * Název souboru exportu, např. export.xlsx nebo export-studenti-2026-2027.xlsx.
     */
    public function fileName(?string $eventName = null, bool $withTimestamp = false): string
    {
        $parts = ['export'];

        if ($eventName !== null) {
            // lomítko v názvu („2026/2027“) nahradíme pomlčkou, slug by ho jinak vypustil
            $parts[] = Str::slug(str_replace('/', '-', $eventName));
        }

        if ($withTimestamp) {
            $parts[] = now()->format('Ymd-His');
        }

        return implode('-', $parts) . '.xlsx';
    }

    public function eventNameExists(string $eventName): bool
    {
        return QuizEvent::query()->where('name', $eventName)->exists();
    }

    /**
     * @param Collection<int, Question> $questions
     */
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
        foreach ($questions->values() as $key => $question) {
            $optionCount = $question->options->count();

            /** @phpstan-var non-falsy-string $actualColumn */
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

    private function writeData(Worksheet $sheet, ?string $eventName): void
    {
        $line = 3;

        foreach ($this->respondentsQuery($eventName)->lazyById(self::CHUNK) as $respondent) {
            $sheet->setCellValue('A' . $line, $respondent->id);
            $sheet->setCellValue('B' . $line, $respondent->student_id);
            $sheet->setCellValue('C' . $line, $respondent->finished ? 'ano' : 'ne');
            $sheet->setCellValue('D' . $line, $this->getSex($respondent));
            $sheet->setCellValue('E' . $line, $respondent->age->name ?? 'nezadáno');
            $sheet->setCellValue('F' . $line, $this->finalSummary($respondent));

            foreach ($respondent->answers as $answer) {
                $this->writeAnswer($sheet, $answer, $line);
            }

            $line++;
        }
    }

    /**
     * @return Builder<Respondent>
     */
    private function respondentsQuery(?string $eventName): Builder
    {
        return Respondent::query()
            ->with(['age', 'answers.option'])
            ->when(
                $eventName !== null,
                fn(Builder $query) => $query->whereHas(
                    'event',
                    fn(Builder $eventQuery) => $eventQuery->where('name', $eventName),
                ),
            );
    }

    private function writeAnswer(Worksheet $sheet, RespondentAnswer $answer, int $line): void
    {
        $questionId = $answer->option->question_id;

        // odpověď na otázku, která už v bance není (smazaná při reimportu obsahu) – nemá sloupec
        if (!isset($this->columnQuestionTimeMap[$questionId], $this->columnQuestionOptionMap[$answer->option->id])) {
            return;
        }

        $sheet->setCellValue($this->columnQuestionTimeMap[$questionId] . $line, $answer->seconds);

        $cellCoordinate = $this->columnQuestionOptionMap[$answer->option->id] . $line;
        $cellColor = $answer->option->right ? Color::COLOR_DARKGREEN : Color::COLOR_DARKRED;

        $sheet->getStyle($cellCoordinate)->getFont()->getColor()->setARGB($cellColor);
        $sheet->getStyle($cellCoordinate)->getAlignment()->setHorizontal('center');
        $sheet->setCellValue($cellCoordinate, 'X');
    }

    private function finalSummary(Respondent $respondent): string
    {
        $right = $wrong = 0;
        foreach ($respondent->answers as $answer) {
            if ($answer->option->right) {
                $right++;
            } else {
                $wrong++;
            }
        }

        return $right . '/' . $wrong;
    }

    private function getSex(Respondent $respondent): string
    {
        if (!$respondent->sex) {
            return 'nezadáno';
        }

        return $respondent->sex === Sex::Male->value ? 'Muž' : 'Žena';
    }
}
