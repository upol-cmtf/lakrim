<?php
namespace App\Console\Commands;

use App\Models\QuizEvent;
use App\Services\IslandGame\GameStatistics;
use Illuminate\Console\Command;

/**
 * Vypíše statistiky Dobrodružné výpravy (verze 3): spuštění, dokončení, úspěšnost,
 * kvalitu odpovědí po ostrovech a obtížnostech, nejtěžší otázky, odpadávání hráčů,
 * délku hry, rozpad podle akcí a dnů, bonusové úkoly a chování adaptivního
 * mechanismu (přechody mezi obtížnostmi, reakce na chybu).
 */
class IslandGameStatsCommand extends Command
{
    protected $signature = 'stats:island-game
        {--event= : Omezit na akci podle jejího hashe z odkazu}
        {--min-players=' . GameStatistics::DEFAULT_MIN_PLAYERS . ' : Min. počet hráčů pro žebříček nejtěžších otázek}
        {--json : Vypsat jako JSON místo tabulek}';

    protected $description = 'Statistiky Dobrodružné výpravy (verze 3) z uložených odpovědí';

    public function __construct(
        private readonly GameStatistics $statistics,
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        $event = null;
        $eventHash = $this->option('event');

        if (is_string($eventHash) && $eventHash !== '') {
            $event = QuizEvent::query()->where('hash', $eventHash)->first();

            if (!$event instanceof QuizEvent) {
                $this->error("Akce s hashem \"{$eventHash}\" neexistuje.");

                return self::FAILURE;
            }
        }

        $minPlayers = max(1, (int) $this->option('min-players'));
        $stats = $this->statistics->forEvent($event);

        $data = [
            'souhrn' => $stats->summary(),
            'odpovedi' => $stats->answers(),
            'po_ostrovech' => $stats->byIsland(),
            'po_obtiznostech' => $stats->byDifficulty(),
            'nejtezsi_otazky' => $stats->hardestQuestions(minPlayers: $minPlayers),
            'odpadavani' => $stats->dropOff(),
            'delka_hry' => $stats->duration(),
            'po_akcich' => $stats->byEvent(),
            'po_dnech' => $stats->byDay(),
            'bonusove_ukoly' => $stats->easterEggs(),
            'adaptivita' => $stats->adaptivity(),
            'prechody_obtiznosti' => $stats->difficultyTransitions(),
            'obtiznost_po_chybe' => $stats->difficultyAfterMistake(),
        ];

        if ($this->option('json')) {
            $this->line((string) json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

            return self::SUCCESS;
        }

        $this->info($event instanceof QuizEvent
            ? "Dobrodružná výprava – akce {$event->name} ({$event->hash})"
            : 'Dobrodružná výprava – všechny akce');

        $this->section('Souhrn', $this->keyValueRows($data['souhrn']));
        $this->section('Odpovědi', $this->keyValueRows($data['odpovedi']));
        $this->section('Po ostrovech', $data['po_ostrovech']);
        $this->section('Po obtížnostech', $data['po_obtiznostech']);
        $this->section("Nejtěžší otázky (min. {$minPlayers} hráčů)", $data['nejtezsi_otazky']);
        $this->section('Odpadávání (vyřešených kamenů → hráčů)', $this->dropOffRows($data['odpadavani']));
        $this->section('Délka hry', $this->keyValueRows($data['delka_hry']));
        $this->section('Po akcích', $data['po_akcich']);
        $this->section('Po dnech', $data['po_dnech']);
        $this->section('Bonusové úkoly', $this->keyValueRows($data['bonusove_ukoly']));
        $this->section(
            'Adaptivita (cílová obtížnost přepočtená z prvních pokusů)',
            $this->keyValueRows($data['adaptivita']),
        );
        $this->section('Přechody mezi obtížnostmi', $data['prechody_obtiznosti']);
        $this->section('Cílová obtížnost po chybě', $data['obtiznost_po_chybe']);

        return self::SUCCESS;
    }

    /**
     * @param list<array<string, mixed>> $rows
     */
    private function section(string $title, array $rows): void
    {
        $this->newLine();
        $this->comment($title);

        if ($rows === []) {
            $this->line('  (žádná data)');

            return;
        }

        $this->table(array_keys($rows[0]), $rows);
    }

    /**
     * @param array<string, mixed> $values
     * @return list<array<string, mixed>>
     */
    private function keyValueRows(array $values): array
    {
        $rows = [];
        foreach ($values as $key => $value) {
            $rows[] = ['ukazatel' => $key, 'hodnota' => $value];
        }

        return $rows;
    }

    /**
     * @param array<int, int> $distribution
     * @return list<array<string, mixed>>
     */
    private function dropOffRows(array $distribution): array
    {
        $rows = [];
        foreach ($distribution as $stones => $players) {
            $rows[] = ['vyresenych_kamenu' => $stones, 'hracu' => $players];
        }

        return $rows;
    }
}
