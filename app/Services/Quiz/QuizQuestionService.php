<?php
namespace App\Services\Quiz;

use App\Exceptions\MaximumQuestionsExceededException;
use App\Exceptions\QuestionNotFoundException;
use App\Models\Question;
use App\Models\Respondent;
use Illuminate\Database\Eloquent\Collection;

class QuizQuestionService
{
    /**
     * @throws MaximumQuestionsExceededException
     * @throws QuestionNotFoundException
     */
    public function getQuestionForRespondent(Respondent $respondent): Question
    {
        $difficulty = $respondent->difficulty;
        $answeredQuestionIds = $respondent->getAnsweredQuestionIds();

        if (count($answeredQuestionIds) >= $difficulty->max_questions) {
            throw new MaximumQuestionsExceededException();
        }

        $builder = Question::where('difficulty_id', $difficulty->id);

        $answeredQuestionIds = $respondent->getAnsweredQuestionIds();
        if ($answeredQuestionIds) {
            $builder->whereNotIn('id', $answeredQuestionIds);
        }

        $questions = $builder->get();
        assert($questions instanceof Collection);

        $question = $difficulty->shuffle_questions
            ? $questions->random()
            : $questions->first();

        if ($questions->isEmpty()) {
            throw new QuestionNotFoundException();
        }

        assert($question instanceof Question);

        return $question;
    }
}
