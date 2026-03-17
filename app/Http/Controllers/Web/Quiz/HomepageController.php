<?php
namespace App\Http\Controllers\Web\Quiz;

use App\Enums\Difficulty as DifficultyEnum;
use App\Http\Controllers\Controller;
use App\Models\Difficulty;
use App\Models\QuizEvent;
use App\Models\Respondent;
use Illuminate\Contracts\View\View;
use Ramsey\Uuid\Uuid;

final class HomepageController extends Controller
{
    public function index(?QuizEvent $quizEvent = null): View
    {
        $difficulty = Difficulty::find(DifficultyEnum::Easy->value);
        assert($difficulty instanceof Difficulty);

        $respondent = Respondent::create([
            'session_id' => session()->get('_token'),
            'quiz_event_id' => $quizEvent?->id,
            'token' => Uuid::uuid4()->toString(),
            'ip' => request()->ip(),
            'difficulty_id' => $difficulty->id,
        ]);

        return view('web.quiz.index', [
            'respondentToken' => $respondent->token,
            'settings' => array_merge(($respondent->difficulty->settings ?? []), [
                'maxQuestions' => $respondent->difficulty->max_questions,
                'showEvaluationsForOtherOptions' => $respondent->difficulty->show_evaluations_for_other_options,
            ]),
        ]);
    }
}
