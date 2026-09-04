<?php
namespace Tests\Feature\Console;

use App\Enums\Version;
use App\Models\EasterEgg;
use App\Models\Question;
use App\Models\QuestionOption;
use App\Models\QuizEvent;
use App\Models\Respondent;
use App\Models\RespondentAnswer;
use App\Models\RespondentEasterEgg;
use App\Services\IslandGame\GameStatistics;
use Illuminate\Testing\PendingCommand;
use Tests\TestCase;

class IslandGameStatsCommandTest extends TestCase
{
    private const ISLANDS = 4;
    private const BUTTONS = 5;

    private GameStatistics $statistics;

    protected function setUp(): void
    {
        parent::setUp();

        // Seedovaní respondenti by zkreslovali počty – každý test si staví vlastní.
        RespondentEasterEgg::query()->delete();
        RespondentAnswer::query()->delete();
        Respondent::query()->delete();

        $this->statistics = $this->app->make(GameStatistics::class);
    }

    public function testCountsStartsCompletionsAndSuccessRate(): void
    {
        // dokončil: všech 20 kamenů správně
        $finished = $this->respondent();
        $this->resolveAllStones($finished);

        // dokončil: 19 kamenů správně, poslední 2× špatně (kámen je přesto vyřešený)
        $finishedWithFailure = $this->respondent();
        $this->resolveAllStones($finishedWithFailure, lastStoneFailedTwice: true);

        // rozehrál: jen 3 kameny
        $inProgress = $this->respondent();
        foreach (range(1, 3) as $button) {
            $this->answer($inProgress, island: 1, button: $button, right: true);
        }

        // jen otevřel stránku
        $this->respondent();

        // 1× špatně na kameni nestačí k vyřešení
        $oneWrong = $this->respondent();
        $this->answer($oneWrong, island: 1, button: 1, right: false, attempt: 1);

        // respondent jiné verze se nepočítá
        Respondent::factory()->createOneQuietly(['version' => Version::One->value]);

        $summary = $this->statistics->summary();

        $this->assertSame(5, $summary['spusteni']);
        $this->assertSame(4, $summary['spusteni_s_odpovedi']);
        $this->assertSame(2, $summary['dokonceni']);
        $this->assertSame(40.0, $summary['uspesnost_dokonceni_pct']);
        $this->assertSame(50.0, $summary['uspesnost_dokonceni_z_aktivnich_pct']);
        $this->assertSame(self::ISLANDS * self::BUTTONS, $summary['kamenu_celkem']);
        // (20 + 20 + 3 + 0 + 0) / 5
        $this->assertSame(8.6, $summary['prumer_vyresenych_kamenu']);

        $this->assertSame([0 => 2, 3 => 1, 20 => 2], $this->statistics->dropOff());
    }

    public function testAnswerQualityAndBreakdowns(): void
    {
        $respondent = $this->respondent();
        $this->answer($respondent, island: 1, button: 1, right: true, seconds: 10);
        $this->answer($respondent, island: 1, button: 2, right: false, seconds: 20, attempt: 1);
        $this->answer($respondent, island: 1, button: 2, right: true, seconds: 30, attempt: 2);
        $this->answer($respondent, island: 2, button: 1, right: true, seconds: 40);

        $answers = $this->statistics->answers();

        $this->assertSame(4, $answers['odpovedi_celkem']);
        // 3 první pokusy, 2 z nich správně
        $this->assertSame(66.7, $answers['spravne_na_prvni_pokus_pct']);
        $this->assertSame(75.0, $answers['spravne_celkem_pct']);
        $this->assertSame(25.0, $answers['prumerny_cas_s']);
        $this->assertSame(0, $answers['bonusovych_odpovedi']);

        $byIsland = $this->statistics->byIsland();
        $this->assertCount(2, $byIsland);
        $this->assertSame(3, $byIsland[0]['odpovedi']);
        $this->assertSame(50.0, $byIsland[0]['spravne_na_prvni_pokus_pct']);
        $this->assertSame(1, $byIsland[1]['odpovedi']);
        $this->assertSame(100.0, $byIsland[1]['spravne_na_prvni_pokus_pct']);

        $byDifficulty = $this->statistics->byDifficulty();
        $this->assertCount(1, $byDifficulty);
        $this->assertSame('Lehké', $byDifficulty[0]['obtiznost']);
        $this->assertSame(4, $byDifficulty[0]['odpovedi']);
    }

    public function testHardestQuestionsRespectMinimumPlayers(): void
    {
        $hard = Question::factory()->withoutGroup()->create(['version' => Version::Three]);
        $easy = Question::factory()->withoutGroup()->create(['version' => Version::Three]);

        for ($i = 0; $i < 3; $i++) {
            $respondent = $this->respondent();
            $this->answer($respondent, island: 1, button: 1, right: false, question: $hard);
            $this->answer($respondent, island: 1, button: 2, right: true, question: $easy);
        }

        $this->assertSame([], $this->statistics->hardestQuestions(minPlayers: 5));

        $ranking = $this->statistics->hardestQuestions(minPlayers: 3);
        $this->assertCount(2, $ranking);
        $this->assertSame($hard->id, $ranking[0]['id']);
        $this->assertSame(0.0, $ranking[0]['spravne_na_prvni_pokus_pct']);
        $this->assertSame(3, $ranking[0]['hracu']);
        $this->assertSame($easy->id, $ranking[1]['id']);
        $this->assertSame(100.0, $ranking[1]['spravne_na_prvni_pokus_pct']);
    }

    public function testEventFilterAndByEvent(): void
    {
        $event = QuizEvent::factory()->create(['name' => 'Kurz A', 'hash' => 'kurz-a']);

        $inEvent = $this->respondent($event);
        $this->resolveAllStones($inEvent);
        $this->respondent($event);
        $this->respondent();

        $all = $this->statistics->summary();
        $this->assertSame(3, $all['spusteni']);
        $this->assertSame(1, $all['dokonceni']);

        $byEvent = $this->statistics->byEvent();
        $this->assertCount(2, $byEvent);
        $this->assertSame('Kurz A', $byEvent[0]['akce']);
        $this->assertSame('kurz-a', $byEvent[0]['hash']);
        $this->assertSame(2, $byEvent[0]['spusteni']);
        $this->assertSame(1, $byEvent[0]['dokonceni']);
        $this->assertSame('(bez akce)', $byEvent[1]['akce']);

        $scoped = $this->statistics->forEvent($event)->summary();
        $this->assertSame(2, $scoped['spusteni']);
        $this->assertSame(1, $scoped['dokonceni']);
        $this->assertSame(50.0, $scoped['uspesnost_dokonceni_pct']);
    }

    public function testDurationAndEasterEggs(): void
    {
        $respondent = $this->respondent();
        $this->answer($respondent, island: 1, button: 1, right: true, createdAt: '2026-06-01 10:00:00');
        $this->answer($respondent, island: 1, button: 2, right: true, createdAt: '2026-06-01 10:12:00');

        // jedna odpověď nestačí – délku nelze určit
        $single = $this->respondent();
        $this->answer($single, island: 1, button: 1, right: true);

        $duration = $this->statistics->duration();
        $this->assertSame(1, $duration['hracu']);
        $this->assertSame(12.0, $duration['prumer_min']);
        $this->assertSame(12.0, $duration['median_min']);
        $this->assertSame(12.0, $duration['max_min']);

        foreach ([30, 90] as $seconds) {
            RespondentEasterEgg::factory()->create([
                'respondent_id' => $respondent->id,
                'easter_egg_id' => EasterEgg::factory()->create()->id,
                'seconds' => $seconds,
            ]);
        }

        $this->assertSame(
            ['hracu_s_rybkou' => 1, 'ulovenych_rybek' => 2, 'prumerny_cas_v_ukolu_s' => 60.0],
            $this->statistics->easterEggs(),
        );
    }

    public function testCommandPrintsTablesAndJson(): void
    {
        $respondent = $this->respondent();
        $this->resolveAllStones($respondent);

        $this->runCommand('stats:island-game')
            ->expectsOutputToContain('Dobrodružná výprava – všechny akce')
            ->expectsOutputToContain('spusteni')
            ->assertSuccessful();

        $this->runCommand('stats:island-game --json')
            ->expectsOutputToContain('"dokonceni": 1')
            ->assertSuccessful();
    }

    public function testCommandFailsForUnknownEvent(): void
    {
        $this->runCommand('stats:island-game --event=neexistuje')
            ->expectsOutputToContain('neexistuje')
            ->assertFailed();
    }

    private function runCommand(string $command): PendingCommand
    {
        $pending = $this->artisan($command);
        assert($pending instanceof PendingCommand);

        return $pending;
    }

    private function respondent(?QuizEvent $event = null): Respondent
    {
        return Respondent::factory()->createOneQuietly([
            'version' => Version::Three->value,
            'quiz_event_id' => $event?->id,
        ]);
    }

    private function resolveAllStones(Respondent $respondent, bool $lastStoneFailedTwice = false): void
    {
        foreach (range(1, self::ISLANDS) as $island) {
            foreach (range(1, self::BUTTONS) as $button) {
                $isLast = $island === self::ISLANDS && $button === self::BUTTONS;

                if ($isLast && $lastStoneFailedTwice) {
                    $this->answer($respondent, $island, $button, right: false, attempt: 1);
                    $this->answer($respondent, $island, $button, right: false, attempt: 2);
                    continue;
                }

                $this->answer($respondent, $island, $button, right: true);
            }
        }
    }

    private function answer(
        Respondent $respondent,
        int $island,
        int $button,
        bool $right,
        int $attempt = 1,
        int $seconds = 5,
        ?Question $question = null,
        ?string $createdAt = null,
    ): void {
        $question ??= Question::factory()->withoutGroup()->create(['version' => Version::Three]);

        $option = QuestionOption::factory()->create([
            'question_id' => $question->id,
            'right' => $right,
        ]);

        RespondentAnswer::factory()->createQuietly([
            'respondent_id' => $respondent->id,
            'question_option_id' => $option->id,
            'attempt' => $attempt,
            'seconds' => $seconds,
            'island_id' => $island,
            'button' => $button,
            'created_at' => $createdAt ?? now(),
        ]);
    }
}
