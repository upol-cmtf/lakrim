<?php
namespace Tests\Feature\Admin;

use App\Models\QuizEvent;
use App\Models\Respondent;
use App\Models\User;
use Illuminate\Testing\TestResponse;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Tests\TestCase;

class ExportQuestionsResultsTest extends TestCase
{
    private const ROUTE_NAME = 'admin.export.questions-results';

    protected function setUp(): void
    {
        parent::setUp();

        $this->actingAs(User::factory()->createQuietly());
    }

    public function testExportsAllRespondentsWithoutFilter(): void
    {
        $event = QuizEvent::factory()->create(['name' => 'Studenti 2026/2027']);
        $inEvent = Respondent::factory()->createQuietly(['quiz_event_id' => $event->id]);
        $withoutEvent = Respondent::factory()->createQuietly();

        $response = $this->get(route(self::ROUTE_NAME))
            ->assertOk()
            ->assertHeader('Content-Disposition', 'attachment; filename=export.xlsx');

        $this->assertEqualsCanonicalizing(
            [$inEvent->id, $withoutEvent->id],
            $this->exportedRespondentIds($response),
        );
    }

    public function testFiltersRespondentsByEventName(): void
    {
        // dvě události se stejným názvem (různé kurzy) patří do jednoho exportu;
        // hashe se nesmí krýt s těmi, které zakládají datové migrace
        $eventA = QuizEvent::factory()->create(['name' => 'Studenti 2026/2027', 'hash' => 'test-a']);
        $eventB = QuizEvent::factory()->create(['name' => 'Studenti 2026/2027', 'hash' => 'test-b']);
        $other = QuizEvent::factory()->create(['name' => 'Jiná událost', 'hash' => 'test-c']);

        $inA = Respondent::factory()->createQuietly(['quiz_event_id' => $eventA->id]);
        $inB = Respondent::factory()->createQuietly(['quiz_event_id' => $eventB->id]);
        Respondent::factory()->createQuietly(['quiz_event_id' => $other->id]);
        Respondent::factory()->createQuietly();

        $response = $this->get(route(self::ROUTE_NAME, ['event' => 'Studenti 2026/2027']))
            ->assertOk()
            ->assertHeader('Content-Disposition', 'attachment; filename=export-studenti-2026-2027.xlsx');

        $this->assertEqualsCanonicalizing([$inA->id, $inB->id], $this->exportedRespondentIds($response));
    }

    public function testRejectsUnknownEventName(): void
    {
        $this->get(route(self::ROUTE_NAME, ['event' => 'neexistuje']))
            ->assertInvalid('event');
    }

    public function testRedirectsGuestToLogin(): void
    {
        auth()->logout();

        $this->get(route(self::ROUTE_NAME))
            ->assertRedirect(route('admin.login'));
    }

    /**
     * Přečte z vygenerovaného XLSX hodnoty sloupce A (ID respondenta) od 3. řádku.
     *
     * @return int[]
     */
    private function exportedRespondentIds(TestResponse $response): array
    {
        $path = tempnam(sys_get_temp_dir(), 'export');
        assert($path !== false);
        file_put_contents($path, $response->streamedContent());

        $sheet = IOFactory::load($path)->getActiveSheet();
        unlink($path);

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
