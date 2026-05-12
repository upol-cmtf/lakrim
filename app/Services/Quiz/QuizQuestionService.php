<?php
namespace App\Services\Quiz;

use App\Enums\Version;
use App\Exceptions\MaximumQuestionsExceededException;
use App\Exceptions\QuestionNotFoundException;
use App\Models\Difficulty;
use App\Models\Question;
use App\Models\Respondent;

class QuizQuestionService
{
    /**
     * @throws MaximumQuestionsExceededException
     * @throws QuestionNotFoundException
     */
    public function getQuestionForRespondent(Respondent $respondent, Version $version): Question
    {
        $difficulty = Difficulty::find($respondent->version->value);
        assert($difficulty instanceof Difficulty);
        $answeredQuestionIds = $respondent->getAnsweredQuestionIds();

        if (count($answeredQuestionIds) >= $difficulty->max_questions) {
            throw new MaximumQuestionsExceededException();
        }

        $builder = Question::version($version)->where('difficulty_id', $difficulty->id);

        if ($answeredQuestionIds) {
            $builder->whereNotIn('id', $answeredQuestionIds);
        }

        $questions = $builder->get();

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
