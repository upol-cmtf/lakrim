<?php
namespace Tests\Feature\Console;

use App\Models\QuizEvent;
use App\Models\Respondent;
use Illuminate\Testing\PendingCommand;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Tests\TestCase;

class ExportQuestionsResultsCommandTest extends TestCase
{
    private string $directory;

    protected function setUp(): void
    {
        parent::setUp();

        $this->directory = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'lakrim-export-' . uniqid();
    }

    protected function tearDown(): void
    {
        foreach (glob($this->directory . DIRECTORY_SEPARATOR . '*') ?: [] as $file) {
            unlink($file);
        }
        if (is_dir($this->directory)) {
            rmdir($this->directory);
        }

        parent::tearDown();
    }

    public function testExportsRespondentsOfEventToFile(): void
    {
        $event = QuizEvent::factory()->create(['name' => 'Studenti 2026/2027', 'hash' => 'test-cmd']);
        $inEvent = Respondent::factory()->createQuietly(['quiz_event_id' => $event->id]);
        Respondent::factory()->createQuietly();

        $this->runExport(['--event' => 'Studenti 2026/2027', '--dir' => $this->directory])->assertSuccessful();

        $files = glob($this->directory . DIRECTORY_SEPARATOR . 'export-studenti-2026-2027-*.xlsx') ?: [];
        $this->assertCount(1, $files);

        $this->assertSame([$inEvent->id], $this->exportedRespondentIds($files[0]));
    }

    public function testExportsAllRespondentsWithoutEvent(): void
    {
        $first = Respondent::factory()->createQuietly();
        $second = Respondent::factory()->createQuietly();

        $this->runExport(['--dir' => $this->directory])->assertSuccessful();

        $files = glob($this->directory . DIRECTORY_SEPARATOR . 'export-*.xlsx') ?: [];
        $this->assertCount(1, $files);

        $this->assertEqualsCanonicalizing([$first->id, $second->id], $this->exportedRespondentIds($files[0]));
    }

    public function testFailsForUnknownEvent(): void
    {
        $this->runExport(['--event' => 'neexistuje', '--dir' => $this->directory])->assertFailed();

        $this->assertDirectoryDoesNotExist($this->directory);
    }

    /**
     * @param array<string, string> $options
     */
    private function runExport(array $options): PendingCommand
    {
        $pending = $this->artisan('export:questions-results', $options);
        assert($pending instanceof PendingCommand);

        return $pending;
    }

    /**
     * @return int[]
     */
    private function exportedRespondentIds(string $path): array
    {
        $sheet = IOFactory::load($path)->getActiveSheet();

        $ids = [];
        for ($row = 3; $row <= $sheet->getHighestRow(); $row++) {
            $value = $sheet->getCell('A' . $row)->getValue();
            if (!is_numeric($value)) {
                continue;
            }
            $ids[] = (int) $value;
        }

        return $ids;
    }
}
