<?php
namespace App\Http\Controllers\Web\Quiz;

use App\Enums\Version;
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
        $respondent = Respondent::create([
            'session_id' => session()->get('_token'),
            'quiz_event_id' => $quizEvent?->id,
            'token' => Uuid::uuid4()->toString(),
            'ip' => request()->ip(),
            'version' => Version::One,
        ]);

        $difficulty = Difficulty::find($respondent->version->value);
        assert($difficulty instanceof Difficulty);

        return view('web.quiz.index', [
            'respondentToken' => $respondent->token,
            'settings' => array_merge(($difficulty->settings ?? []), [
                'maxQuestions' => $difficulty->max_questions,
                'showEvaluationsForOtherOptions' => $difficulty->show_evaluations_for_other_options,
            ]),
        ]);
    }
}
