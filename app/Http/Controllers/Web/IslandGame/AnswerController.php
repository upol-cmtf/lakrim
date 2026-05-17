<?php
namespace App\Http\Controllers\Web\IslandGame;

use App\Http\Controllers\Web\ApiController;
use App\Http\Resources\Web\ArrayResource;
use App\Models\Question;
use App\Models\QuestionOption;
use App\Models\Respondent;
use App\Models\RespondentSituation;
use App\Models\Situation;

final class AnswerController extends ApiController
{
    public function store(): ArrayResource
    {
        $questionId = $this->request->get('question_id', -1);
        assert(is_numeric($questionId));

        $this->validate($this->request, [
            'respondent_token' => 'required|string|exists:respondents,token',
            'question_id' => 'required|integer|exists:questions,id',
            'option_ids' => 'required|array',
            'option_ids.*' => 'integer|exists:questions_options,id,question_id,' . $questionId,
            'seconds' => 'required|integer',
            'attempt' => 'required|integer',
            'island_id' => 'required|integer|exists:islands,id',
            'button' => 'required|integer|min:1|max:5',
        ]);

        $attempt = $this->request->integer('attempt');
        $islandId = $this->request->integer('island_id');
        $button = $this->request->integer('button');

        $question = Question::where('id', $this->request->input('question_id'))->first();
        assert($question instanceof Question);

        $respondent = Respondent::where('token', $this->request->input('respondent_token'))->first();
        assert($respondent instanceof Respondent);

        /** @var int[] $optionIds */
        $optionIds = $this->request->input('option_ids');

        foreach ($optionIds as $optionId) {
            $option = $question->options()->where('id', $optionId)->first();
            assert($option instanceof QuestionOption);

            $respondent->answers()->create([
                'respondent_id' => $respondent->id,
                'question_option_id' => $option->id,
                'seconds' => $this->request->input('seconds'),
                'weight' => $option->weight,
                'attempt' => $attempt,
                'island_id' => $islandId,
                'button' => $button,
            ]);
        }

        $correct = $this->isCorrect($question, $optionIds);
        $situation = Situation::query()
            ->where('island_id', $islandId)
            ->where('position', $button)
            ->where('question_id', $question->id)
            ->first();

        $safetyCard = null;

        // správná odpověď → situace je splněná, hráč získává kartu bezpečí
        if ($correct && $situation instanceof Situation) {
            RespondentSituation::updateOrCreate(
                ['respondent_id' => $respondent->id, 'situation_id' => $situation->id],
                ['completed_at' => now()],
            );
            $safetyCard = $situation->safety_card;
        }

        return new ArrayResource([
            'correct' => $correct,
            'safetyCard' => $safetyCard,
            'evaluations' => $this->evaluations($question, $optionIds),
            'correctAnswerEvaluation' => match ($attempt) {
                1 => $question->first_wrong_answer_evaluation,
                2 => $question->second_wrong_answer_evaluation,
                default => null,
            },
        ]);
    }

    /**
     * Odpověď je správná, když hráč zvolil právě všechny správné možnosti otázky.
     *
     * @param int[] $optionIds
     */
    private function isCorrect(Question $question, array $optionIds): bool
    {
        /** @var int[] $rightOptionIds */
        $rightOptionIds = $question->options()->where('right', true)->pluck('id')->all();

        return $optionIds !== []
            && array_diff($optionIds, $rightOptionIds) === []
            && array_diff($rightOptionIds, $optionIds) === [];
    }

    /**
     * Hodnocení jednotlivých možností – vlastní volby, případně i ostatní,
     * pokud to obtížnost otázky dovoluje.
     *
     * @param int[] $optionIds
     * @return list<array{optionId: int, evaluation: string, evaluationTitle: string, rightAnswer: bool}>
     */
    private function evaluations(Question $question, array $optionIds): array
    {
        $evaluations = [];

        /** @var QuestionOption $option */
        foreach ($question->options as $option) {
            $chosen = in_array($option->id, $optionIds, true);

            if (!$chosen && !$option->question->difficulty->show_evaluations_for_other_options) {
                continue;
            }

            $evaluations[] = [
                'optionId' => $option->id,
                'evaluation' => $option->evaluation,
                'evaluationTitle' => $option->evaluation_title,
                'rightAnswer' => $option->right,
            ];
        }

        return $evaluations;
    }
}
