<?php
namespace App\Http\Controllers\Web\Quiz;

use App\Enums\Difficulty as DifficultyEnum;
use App\Http\Controllers\Controller;
use App\Models\Difficulty;
use App\Models\QuizEvent;
use App\Models\Respondent;
use Illuminate\Contracts\View\View;
use Ramsey\Uuid\Uuid;

class QuizRunController extends Controller
{
    public function run(?QuizEvent $quizEvent = null): View
    {
        $respondent = $this->createRespondent(DifficultyEnum::Easy, $quizEvent);

        return view('web.quiz.run', [
            'respondentToken' => $respondent->token,
            'settings' => array_merge(($respondent->difficulty->settings ?? []), [
                'maxQuestions' => $respondent->difficulty->max_questions,
                'showEvaluationsForOtherOptions' => $respondent->difficulty->show_evaluations_for_other_options,
            ]),
        ]);
    }

    public function runTiles(?int $maxTiles = 16): View
    {
        $respondent = $this->createRespondent(DifficultyEnum::Medium);

        return view('web.quiz-grid.index', [
            'respondentToken' => $respondent->token,
            'maxTiles' => $maxTiles,
        ]);
    }

    private function createRespondent(DifficultyEnum $difficultyType, ?QuizEvent $quizEvent = null): Respondent
    {
        $difficulty = Difficulty::find($difficultyType->value);
        assert($difficulty instanceof Difficulty);

        return Respondent::create([
            'session_id' => session()->get('_token'),
            'quiz_event_id' => $quizEvent?->id,
            'token' => Uuid::uuid4()->toString(),
            'ip' => request()->ip(),
            'difficulty_id' => $difficulty->id,
        ]);
    }
}
