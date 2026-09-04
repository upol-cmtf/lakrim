<?php
namespace App\Services\IslandGame;

use App\Enums\Version;
use App\Models\QuizEvent;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;

/**
 * Statistiky Dobrodružné výpravy (verze 3) počítané přímo z uložených odpovědí.
 *
 * Dokončení hry se neodvozuje ze sloupce `respondents.finished` (ten patří
 * lineárnímu kvízu), ale stejně jako ve frontendu: hra je dohraná, když je každý
 * kámen (ostrov × pozice) vyřešen – správnou odpovědí, nebo 2. neúspěšným pokusem.
 *
 * Známé omezení: vypršení časového limitu frontend na server neposílá, kámen
 * ukončený dvakrát vypršeným časem se tedy v datech jako vyřešený nejeví.
 */
final class GameStatistics
{
    /** Minimální počet hráčů, aby se otázka objevila v žebříčku nejtěžších. */
    public const DEFAULT_MIN_PLAYERS = 5;

    private ?QuizEvent $event = null;

    public function forEvent(?QuizEvent $event): self
    {
        $this->event = $event;

        return $this;
    }

    /**
     * @return array{
     *     spusteni: int,
     *     spusteni_s_odpovedi: int,
     *     dokonceni: int,
     *     uspesnost_dokonceni_pct: float,
     *     uspesnost_dokonceni_z_aktivnich_pct: float,
     *     kamenu_celkem: int,
     *     prumer_vyresenych_kamenu: float,
     * }
     */
    public function summary(): array
    {
        $rows = $this->perRespondentRows();
        $totalStones = $this->totalStones();

        $starts = count($rows);
        $active = 0;
        $completions = 0;
        $resolvedSum = 0;

        foreach ($rows as $row) {
            $active += $row['has_answered'] ? 1 : 0;
            $completions += $totalStones > 0 && $row['stones_resolved'] >= $totalStones ? 1 : 0;
            $resolvedSum += $row['stones_resolved'];
        }

        return [
            'spusteni' => $starts,
            'spusteni_s_odpovedi' => $active,
            'dokonceni' => $completions,
            'uspesnost_dokonceni_pct' => self::pct($completions, $starts),
            'uspesnost_dokonceni_z_aktivnich_pct' => self::pct($completions, $active),
            'kamenu_celkem' => $totalStones,
            'prumer_vyresenych_kamenu' => $starts > 0 ? round($resolvedSum / $starts, 1) : 0.0,
        ];
    }

    /**
     * Rozložení hráčů podle počtu vyřešených kamenů (kde hráči odpadají).
     *
     * @return array<int, int> počet vyřešených kamenů => počet hráčů
     */
    public function dropOff(): array
    {
        $distribution = [];

        foreach ($this->perRespondentRows() as $row) {
            $distribution[$row['stones_resolved']] = ($distribution[$row['stones_resolved']] ?? 0) + 1;
        }

        ksort($distribution);

        return $distribution;
    }

    /**
     * @return array{
     *     odpovedi_celkem: int,
     *     spravne_na_prvni_pokus_pct: float,
     *     spravne_celkem_pct: float,
     *     prumerny_cas_s: float,
     *     bonusovych_odpovedi: int,
     * }
     */
    public function answers(): array
    {
        $row = $this->answersQuery()
            ->selectRaw('COUNT(*) AS total')
            ->selectRaw('SUM(ra.attempt = 1) AS first_attempts')
            ->selectRaw('SUM(qo.`right` = 1 AND ra.attempt = 1) AS first_attempt_correct')
            ->selectRaw('SUM(qo.`right` = 1) AS correct')
            ->selectRaw('AVG(ra.seconds) AS avg_seconds')
            ->selectRaw('SUM(q.bonus = 1) AS bonus')
            ->first();

        return [
            'odpovedi_celkem' => self::int($row?->total),
            'spravne_na_prvni_pokus_pct' => self::pct(
                self::int($row?->first_attempt_correct),
                self::int($row?->first_attempts),
            ),
            'spravne_celkem_pct' => self::pct(self::int($row?->correct), self::int($row?->total)),
            'prumerny_cas_s' => round(self::float($row?->avg_seconds), 1),
            'bonusovych_odpovedi' => self::int($row?->bonus),
        ];
    }

    /**
     * Úspěšnost po ostrovech (bez bonusových otázek).
     *
     * @return list<array{ostrov: string, odpovedi: int, spravne_na_prvni_pokus_pct: float, prumerny_cas_s: float}>
     */
    public function byIsland(): array
    {
        $rows = $this->answersQuery()
            ->join('islands AS i', 'i.id', '=', 'ra.island_id')
            ->where('q.bonus', false)
            ->select('i.name')
            ->selectRaw('COUNT(*) AS total')
            ->selectRaw('SUM(ra.attempt = 1) AS first_attempts')
            ->selectRaw('SUM(qo.`right` = 1 AND ra.attempt = 1) AS first_attempt_correct')
            ->selectRaw('AVG(ra.seconds) AS avg_seconds')
            ->groupBy('i.id', 'i.name')
            ->orderBy('i.id')
            ->get();

        $result = [];
        foreach ($rows as $row) {
            $result[] = [
                'ostrov' => self::string($row->name),
                'odpovedi' => self::int($row->total),
                'spravne_na_prvni_pokus_pct' => self::pct(
                    self::int($row->first_attempt_correct),
                    self::int($row->first_attempts),
                ),
                'prumerny_cas_s' => round(self::float($row->avg_seconds), 1),
            ];
        }

        return $result;
    }

    /**
     * Úspěšnost po obtížnostech (bez bonusových otázek).
     *
     * @return list<array{obtiznost: string, odpovedi: int, spravne_na_prvni_pokus_pct: float, prumerny_cas_s: float}>
     */
    public function byDifficulty(): array
    {
        $rows = $this->answersQuery()
            ->join('difficulty AS d', 'd.id', '=', 'q.difficulty_id')
            ->where('q.bonus', false)
            ->select('d.name')
            ->selectRaw('COUNT(*) AS total')
            ->selectRaw('SUM(ra.attempt = 1) AS first_attempts')
            ->selectRaw('SUM(qo.`right` = 1 AND ra.attempt = 1) AS first_attempt_correct')
            ->selectRaw('AVG(ra.seconds) AS avg_seconds')
            ->groupBy('d.id', 'd.name')
            ->orderBy('d.id')
            ->get();

        $result = [];
        foreach ($rows as $row) {
            $result[] = [
                'obtiznost' => self::string($row->name),
                'odpovedi' => self::int($row->total),
                'spravne_na_prvni_pokus_pct' => self::pct(
                    self::int($row->first_attempt_correct),
                    self::int($row->first_attempts),
                ),
                'prumerny_cas_s' => round(self::float($row->avg_seconds), 1),
            ];
        }

        return $result;
    }

    /**
     * Otázky s nejnižší úspěšností na první pokus (kandidáti na úpravu zadání).
     *
     * @return list<array{id: int, otazka: string, obtiznost: string, hracu: int, spravne_na_prvni_pokus_pct: float}>
     */
    public function hardestQuestions(int $limit = 10, int $minPlayers = self::DEFAULT_MIN_PLAYERS): array
    {
        $rows = $this->answersQuery()
            ->join('difficulty AS d', 'd.id', '=', 'q.difficulty_id')
            ->select('q.id', 'q.perex', 'd.name')
            ->selectRaw('COUNT(DISTINCT ra.respondent_id) AS players')
            ->selectRaw('SUM(ra.attempt = 1) AS first_attempts')
            ->selectRaw('SUM(qo.`right` = 1 AND ra.attempt = 1) AS first_attempt_correct')
            ->groupBy('q.id', 'q.perex', 'd.name')
            ->havingRaw('COUNT(DISTINCT ra.respondent_id) >= ?', [$minPlayers])
            ->orderByRaw('SUM(qo.`right` = 1 AND ra.attempt = 1) / NULLIF(SUM(ra.attempt = 1), 0) ASC')
            ->limit($limit)
            ->get();

        $result = [];
        foreach ($rows as $row) {
            $result[] = [
                'id' => self::int($row->id),
                'otazka' => mb_strimwidth(strip_tags(self::string($row->perex)), 0, 80, '…'),
                'obtiznost' => self::string($row->name),
                'hracu' => self::int($row->players),
                'spravne_na_prvni_pokus_pct' => self::pct(
                    self::int($row->first_attempt_correct),
                    self::int($row->first_attempts),
                ),
            ];
        }

        return $result;
    }

    /**
     * Délka hry od první do poslední odpovědi (jen hráči s alespoň dvěma odpověďmi).
     *
     * @return array{hracu: int, prumer_min: float, median_min: float, max_min: float}
     */
    public function duration(): array
    {
        $rows = DB::table('respondents_answers AS ra')
            ->join('respondents AS r', 'r.id', '=', 'ra.respondent_id')
            ->tap(fn(Builder $query) => $this->scopeRespondents($query, 'r'))
            ->selectRaw('TIMESTAMPDIFF(SECOND, MIN(ra.created_at), MAX(ra.created_at)) AS seconds')
            ->groupBy('ra.respondent_id')
            ->havingRaw('COUNT(*) >= 2')
            ->pluck('seconds');

        $minutes = $rows->map(fn(mixed $seconds): float => self::float($seconds) / 60)->sort()->values();
        $count = $minutes->count();

        if ($count === 0) {
            return ['hracu' => 0, 'prumer_min' => 0.0, 'median_min' => 0.0, 'max_min' => 0.0];
        }

        $middle = intdiv($count, 2);
        $median = $count % 2 === 1
            ? self::float($minutes->get($middle))
            : (self::float($minutes->get($middle - 1)) + self::float($minutes->get($middle))) / 2;

        return [
            'hracu' => $count,
            'prumer_min' => round(self::float($minutes->avg()), 1),
            'median_min' => round($median, 1),
            'max_min' => round(self::float($minutes->max()), 1),
        ];
    }

    /**
     * Spuštění a dokončení podle akce (hash v odkazu).
     *
     * @return list<array{akce: string, hash: string|null, spusteni: int, dokonceni: int}>
     */
    public function byEvent(): array
    {
        $totalStones = $this->totalStones();
        $events = [];

        foreach ($this->perRespondentRows() as $row) {
            $key = $row['event_id'] ?? 0;
            $events[$key] ??= [
                'akce' => $row['event_name'] ?? '(bez akce)',
                'hash' => $row['event_hash'],
                'spusteni' => 0,
                'dokonceni' => 0,
            ];
            $events[$key]['spusteni']++;
            $events[$key]['dokonceni'] += $totalStones > 0 && $row['stones_resolved'] >= $totalStones ? 1 : 0;
        }

        usort($events, fn(array $a, array $b): int => $b['spusteni'] <=> $a['spusteni']);

        return $events;
    }

    /**
     * Spuštění po dnech.
     *
     * @return list<array{den: string, spusteni: int}>
     */
    public function byDay(): array
    {
        $rows = DB::table('respondents AS r')
            ->tap(fn(Builder $query) => $this->scopeRespondents($query, 'r'))
            ->selectRaw('DATE(r.created_at) AS day')
            ->selectRaw('COUNT(*) AS total')
            ->groupByRaw('DATE(r.created_at)')
            ->orderByRaw('DATE(r.created_at)')
            ->get();

        $result = [];
        foreach ($rows as $row) {
            $result[] = ['den' => self::string($row->day), 'spusteni' => self::int($row->total)];
        }

        return $result;
    }

    /**
     * Bonusové úkoly (rybky).
     *
     * @return array{hracu_s_rybkou: int, ulovenych_rybek: int, prumerny_cas_v_ukolu_s: float}
     */
    public function easterEggs(): array
    {
        $row = DB::table('respondent_easter_eggs AS ree')
            ->join('respondents AS r', 'r.id', '=', 'ree.respondent_id')
            ->tap(fn(Builder $query) => $this->scopeRespondents($query, 'r'))
            ->selectRaw('COUNT(DISTINCT ree.respondent_id) AS players')
            ->selectRaw('COUNT(*) AS total')
            ->selectRaw('AVG(ree.seconds) AS avg_seconds')
            ->first();

        return [
            'hracu_s_rybkou' => self::int($row?->players),
            'ulovenych_rybek' => self::int($row?->total),
            'prumerny_cas_v_ukolu_s' => round(self::float($row?->avg_seconds), 0),
        ];
    }

    /**
     * Počet kamenů ve hře (ostrov × pozice).
     */
    public function totalStones(): int
    {
        return self::int(
            DB::table('situations')->selectRaw('COUNT(DISTINCT island_id, position) AS total')->value('total'),
        );
    }

    /**
     * Jeden řádek na respondenta: počet vyřešených kamenů, zda vůbec odpovídal, akce.
     *
     * @return list<array{
     *     id: int,
     *     stones_resolved: int,
     *     has_answered: bool,
     *     event_id: int|null,
     *     event_name: string|null,
     *     event_hash: string|null,
     * }>
     */
    private function perRespondentRows(): array
    {
        $resolved = DB::table('respondents_answers AS ra')
            ->join('questions_options AS qo', 'qo.id', '=', 'ra.question_option_id')
            ->whereNotNull('ra.island_id')
            ->select('ra.respondent_id', 'ra.island_id', 'ra.button')
            ->groupBy('ra.respondent_id', 'ra.island_id', 'ra.button')
            ->havingRaw('MAX(qo.`right`) = 1 OR MAX(ra.attempt) >= 2');

        $rows = DB::table('respondents AS r')
            ->tap(fn(Builder $query) => $this->scopeRespondents($query, 'r'))
            ->leftJoinSub($resolved, 'res', 'res.respondent_id', '=', 'r.id')
            ->leftJoin('quiz_events AS e', 'e.id', '=', 'r.quiz_event_id')
            ->select('r.id', 'e.id AS event_id', 'e.name AS event_name', 'e.hash AS event_hash')
            ->selectRaw('COUNT(res.island_id) AS stones_resolved')
            ->selectRaw('EXISTS(SELECT 1 FROM respondents_answers ra2 WHERE ra2.respondent_id = r.id) AS has_answered')
            ->groupBy('r.id', 'e.id', 'e.name', 'e.hash')
            ->get();

        $result = [];
        foreach ($rows as $row) {
            $result[] = [
                'id' => self::int($row->id),
                'stones_resolved' => self::int($row->stones_resolved),
                'has_answered' => self::int($row->has_answered) === 1,
                'event_id' => $row->event_id === null ? null : self::int($row->event_id),
                'event_name' => $row->event_name === null ? null : self::string($row->event_name),
                'event_hash' => $row->event_hash === null ? null : self::string($row->event_hash),
            ];
        }

        return $result;
    }

    /**
     * Odpovědi ve výpravě (s ostrovem) respondentů verze 3, včetně možnosti a otázky.
     */
    private function answersQuery(): Builder
    {
        return DB::table('respondents_answers AS ra')
            ->join('respondents AS r', 'r.id', '=', 'ra.respondent_id')
            ->join('questions_options AS qo', 'qo.id', '=', 'ra.question_option_id')
            ->join('questions AS q', 'q.id', '=', 'qo.question_id')
            ->tap(fn(Builder $query) => $this->scopeRespondents($query, 'r'))
            ->whereNotNull('ra.island_id');
    }

    private function scopeRespondents(Builder $query, string $alias): void
    {
        $query->where($alias . '.version', Version::Three->value);

        if ($this->event !== null) {
            $query->where($alias . '.quiz_event_id', $this->event->id);
        }
    }

    private static function pct(int $part, int $whole): float
    {
        return $whole > 0 ? round(100 * $part / $whole, 1) : 0.0;
    }

    private static function int(mixed $value): int
    {
        return is_numeric($value) ? (int) $value : 0;
    }

    private static function float(mixed $value): float
    {
        return is_numeric($value) ? (float) $value : 0.0;
    }

    private static function string(mixed $value): string
    {
        return is_scalar($value) ? (string) $value : '';
    }
}
