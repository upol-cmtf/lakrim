<?php
namespace App\Http\Controllers\Web\Quiz;

use App\Http\Controllers\Web\ApiController;
use App\Http\Resources\Web\ArrayResource;
use App\Models\Question;
use App\Models\QuestionOption;
use App\Models\Respondent;
use Illuminate\Http\JsonResponse;

class AnswerController extends ApiController
{
    public function store(): ArrayResource|JsonResponse
    {
        $questionId = $this->request->get('question_id');

        $this->validate($this->request, [
            'question_id' => 'required|integer|exists:questions,id',
            'option_ids' => 'required|array',
            'option_ids.*' => 'integer|exists:questions_options,id,question_id,' . $questionId,
            'respondent_token' => 'required|string|exists:respondents,token',
            'seconds' => 'required|integer',
        ]);

        $question = Question::where('id', $this->request->input('question_id'))->first();
        assert($question instanceof Question);

        $respondent = Respondent::where('token', $this->request->input('respondent_token'))->first();
        assert($respondent instanceof Respondent);

        $optionIds = $this->request->input('option_ids');
        assert(is_array($optionIds));

        foreach ($optionIds as $optionId) {
            $option = $question->options()->where('id', $optionId)->first();
            assert($option instanceof QuestionOption);

            $respondent->answers()->create([
                'respondent_id' => $respondent->id,
                'question_option_id' => $option->id,
                'seconds' => $this->request->input('seconds'),
                'weight' => $option->weight,
            ]);
        }

        return new ArrayResource([
            'end' => $respondent->isAllQuizQuestionsAnswered(),
            'evaluations' => $question->getOptions()
                // @phpstan-ignore-next-line
                ->filter(function (QuestionOption $questionOption) use ($optionIds) {
                    return $questionOption->question->difficulty->show_evaluations_for_other_options
                        || in_array($questionOption->id, $optionIds, true);
                })
                // @phpstan-ignore-next-line
                ->map(fn(QuestionOption $questionOption) => [
                    'optionId' => $questionOption->id,
                    'evaluation' => $questionOption->evaluation,
                    'evaluationTitle' => $questionOption->evaluation_title,
                    'rightAnswer' => $questionOption->right,
                ])
                ->toArray(),
        ]);
    }
}
