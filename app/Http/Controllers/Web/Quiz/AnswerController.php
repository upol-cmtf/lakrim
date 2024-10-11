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
            'option_id' => 'required|integer|exists:questions_options,id,question_id,' . $questionId,
            'respondent_token' => 'required|string|exists:respondents,token',
            'seconds' => 'required|integer',
        ]);

        $question = Question::where('id', $this->request->input('question_id'))->first();
        assert($question instanceof Question);

        $option = $question->options()->where('id', $this->request->input('option_id'))->first();
        assert($option instanceof QuestionOption);

        $respondent = Respondent::where('token', $this->request->input('respondent_token'))->first();
        assert($respondent instanceof Respondent);

        $respondent->answers()->create([
            'respondent_id' => $respondent->id,
            'question_option_id' => $option->id,
            'seconds' => $this->request->input('seconds'),
            'weight' => $option->weight,
        ]);

        return new ArrayResource([
            'end' => $respondent->isAllQuizQuestionsAnswered(),
            'evaluation' => $option->evaluation,
        ]);
    }
}
