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
 * Adaptivní obtížnost: drží se jedno skóre znalostí počítané ze všech odpovědí
 * respondenta napříč všemi ostrovy. Správná odpověď ho zvýší, špatná sníží,
 * takže čím lépe si respondent celkově vede, tím těžší otázky se mu nabízejí.
 */
class SituationSelector
{
    /** Skóre znalostí potřebné pro obtížnost 2, resp. 3. */
    private const SCORE_FOR_MEDIUM = 2;
    private const SCORE_FOR_HARD = 4;

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
