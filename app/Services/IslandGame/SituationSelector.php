<?php
namespace App\Services\IslandGame;

use App\Enums\Version;
use App\Models\Island;
use App\Models\Question;
use App\Models\Respondent;
use App\Models\RespondentAnswer;
use App\Models\Situation;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

/**
 * Vybírá situaci (baterii otázek) pro konkrétní tlačítko ostrova.
 *
 * Adaptivní obtížnost: drží se jedno skóre znalostí počítané ze všech odpovědí
 * respondenta napříč všemi ostrovy. Správná odpověď ho zvýší, špatná sníží,
 * takže čím lépe si respondent celkově vede, tím těžší otázky se mu nabízejí.
 *
 * Odměna za sérii: když respondent odpovídá pořád správně, jednou za čas (max
 * 2× za celou hru) se mu místo klasické otázky nabídne bonusová otázka.
 */
class SituationSelector
{
    /** Skóre znalostí potřebné pro obtížnost 2, resp. 3 (čte i GameStatistics). */
    public const SCORE_FOR_MEDIUM = 2;
    public const SCORE_FOR_HARD = 4;

    /** Série správných odpovědí v řadě, která odemkne 1., resp. 2. bonus. */
    private const STREAK_FOR_FIRST_BONUS = 3;
    private const STREAK_FOR_SECOND_BONUS = 6;

    /** Maximální počet bonusových otázek za celou hru. */
    private const MAX_BONUSES = 2;

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
            // bonusové otázky se na kameny ostrova nenabízejí
            ->whereHas('question', fn(Builder $query) => $query->where('bonus', false))
            ->when(
                $answeredQuestionIds !== [],
                fn(Builder $query) => $query->whereNotIn('question_id', $answeredQuestionIds),
            )
            ->with(['question.difficulty', 'question.questionGroup', 'question.options'])
            ->get();

        if ($candidates->isEmpty()) {
            return null;
        }

        // odměna za sérii správných odpovědí – místo klasické otázky občas bonus
        $bonus = $this->maybeBonusSituation($respondent, $island, $button, $answeredQuestionIds);
        if ($bonus !== null) {
            return $bonus;
        }

        return $this->pickByDifficulty($candidates, $this->targetDifficulty($respondent));
    }

    /**
     * Pokud respondent jede sérii správných odpovědí a ještě nevyčerpal limit
     * bonusů, vrátí (neuloženou) situaci obalující bonusovou otázku, kterou ještě
     * nedostal. Jinak null. Bonusy se rozprostřou: 1. při sérii 3, 2. při sérii 6.
     *
     * @param int[] $answeredQuestionIds
     */
    private function maybeBonusSituation(
        Respondent $respondent,
        Island $island,
        int $button,
        array $answeredQuestionIds,
    ): ?Situation {
        $allowed = min(self::MAX_BONUSES, $this->allowedBonuses($this->correctStreak($respondent)));

        if ($this->answeredBonusCount($answeredQuestionIds) >= $allowed) {
            return null;
        }

        $bonusQuestion = Question::query()
            ->where('version', Version::Three->value)
            ->where('bonus', true)
            ->when(
                $answeredQuestionIds !== [],
                fn(Builder $query) => $query->whereNotIn('id', $answeredQuestionIds),
            )
            ->with(['difficulty', 'questionGroup', 'options', 'images'])
            ->inRandomOrder()
            ->first();

        if (!$bonusQuestion instanceof Question) {
            return null;
        }

        // bonusové otázky nemají vlastní situaci – obalíme ji přechodnou (neuloženou)
        $situation = new Situation([
            'island_id' => $island->id,
            'question_id' => $bonusQuestion->id,
            'position' => $button,
            'title' => null,
            'safety_card' => null,
        ]);
        $situation->setRelation('question', $bonusQuestion);

        return $situation;
    }

    /**
     * Kolik bonusů smí respondent dostat při dané sérii správných odpovědí.
     */
    private function allowedBonuses(int $streak): int
    {
        return match (true) {
            $streak >= self::STREAK_FOR_SECOND_BONUS => 2,
            $streak >= self::STREAK_FOR_FIRST_BONUS => 1,
            default => 0,
        };
    }

    /**
     * Počet po sobě jdoucích správných odpovědí na první pokus, počítáno od
     * poslední odpovědi. Špatná odpověď sérii nuluje.
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
     * Počet bonusových otázek, na které už respondent odpověděl.
     *
     * @param int[] $answeredQuestionIds
     */
    private function answeredBonusCount(array $answeredQuestionIds): int
    {
        if ($answeredQuestionIds === []) {
            return 0;
        }

        return Question::query()
            ->where('bonus', true)
            ->whereIn('id', $answeredQuestionIds)
            ->count();
    }

    /**
     * Cílová obtížnost (1–3) odvozená ze skóre znalostí respondenta.
     */
    private function targetDifficulty(Respondent $respondent): int
    {
        $score = $this->knowledgeScore($respondent);

        return match (true) {
            $score >= self::SCORE_FOR_HARD => 3,
            $score >= self::SCORE_FOR_MEDIUM => 2,
            default => 1,
        };
    }

    /**
     * Adaptivní skóre znalostí počítané ze všech odpovědí respondenta napříč
     * všemi ostrovy. Správná odpověď na první pokus skóre zvýší o 1, špatná
     * sníží o 1; skóre nikdy neklesne pod 0. Díky tomu se obtížnost přizpůsobuje
     * oběma směry a jedna chyba neshodí respondenta rovnou na nejlehčí úroveň.
     */
    private function knowledgeScore(Respondent $respondent): int
    {
        /** @var Collection<int, RespondentAnswer> $answers */
        $answers = $respondent->answers()
            ->where('attempt', 1)
            ->with('option')
            ->get();

        $score = 0;
        foreach ($answers as $answer) {
            $score += $answer->isRightAnswer() ? 1 : -1;
            $score = max(0, $score);
        }

        return $score;
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
