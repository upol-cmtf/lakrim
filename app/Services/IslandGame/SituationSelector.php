<?php
namespace App\Services\IslandGame;

use App\Models\Island;
use App\Models\Respondent;
use App\Models\RespondentAnswer;
use App\Models\Situation;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

/**
 * Vybírá situaci (baterii otázek) pro konkrétní tlačítko ostrova.
 *
 * Adaptivní obtížnost: čím delší má respondent sérii správných odpovědí,
 * tím těžší otázky se mu nabízejí.
 */
class SituationSelector
{
    /** Délka série správných odpovědí potřebná pro obtížnost 2, resp. 3. */
    private const STREAK_FOR_MEDIUM = 2;
    private const STREAK_FOR_HARD = 4;

    /**
     * Vrátí situaci pro dané tlačítko, na kterou respondent ještě neodpověděl,
     * s obtížností co nejblíž jeho aktuální úrovni. Když žádná nezbývá, vrátí null.
     */
    public function select(Respondent $respondent, Island $island, int $button): ?Situation
    {
        $answeredQuestionIds = $respondent->getAnsweredQuestionIds();

        /** @var Collection<int, Situation> $candidates */
        $candidates = Situation::query()
            ->where('island_id', $island->id)
            ->where('position', $button)
            ->when(
                $answeredQuestionIds !== [],
                fn(Builder $query) => $query->whereNotIn('question_id', $answeredQuestionIds),
            )
            ->with(['question.difficulty', 'question.questionGroup', 'question.options'])
            ->get();

        if ($candidates->isEmpty()) {
            return null;
        }

        return $this->pickByDifficulty($candidates, $this->targetDifficulty($respondent));
    }

    /**
     * Cílová obtížnost (1–3) odvozená ze série správných odpovědí respondenta.
     */
    private function targetDifficulty(Respondent $respondent): int
    {
        $streak = $this->correctStreak($respondent);

        return match (true) {
            $streak >= self::STREAK_FOR_HARD => 3,
            $streak >= self::STREAK_FOR_MEDIUM => 2,
            default => 1,
        };
    }

    /**
     * Počet po sobě jdoucích správných odpovědí na první pokus, počítáno
     * od poslední odpovědi. Špatná odpověď sérii nuluje.
     */
    private function correctStreak(Respondent $respondent): int
    {
        /** @var Collection<int, RespondentAnswer> $answers */
        $answers = $respondent->answers()
            ->where('attempt', 1)
            ->with('option')
            ->orderByDesc('id')
            ->get();

        $streak = 0;
        foreach ($answers as $answer) {
            if (!$answer->isRightAnswer()) {
                break;
            }

            $streak++;
        }

        return $streak;
    }

    /**
     * Vybere situaci s cílovou obtížností; když na ní žádná není, sáhne po
     * nejbližší dostupné (nejdřív nižší, pak vyšší). Při více kandidátech náhodně.
     *
     * @param Collection<int, Situation> $candidates
     */
    private function pickByDifficulty(Collection $candidates, int $target): Situation
    {
        $preference = collect([$target])
            ->merge(range($target - 1, 1))
            ->merge(range($target + 1, 3))
            ->unique()
            ->filter(fn(int $difficulty): bool => $difficulty >= 1 && $difficulty <= 3);

        foreach ($preference as $difficulty) {
            $group = $candidates->filter(
                fn(Situation $situation): bool => $situation->question->difficulty->id === $difficulty,
            );

            if ($group->isNotEmpty()) {
                return $group->random();
            }
        }

        return $candidates->random();
    }
}
