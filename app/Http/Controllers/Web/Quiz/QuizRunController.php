<?php
namespace App\Http\Controllers\Web\Quiz;

use App\Enums\Version;
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
        $respondent = $this->createRespondent(Version::One, $quizEvent);

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

    public function runTiles(?QuizEvent $quizEvent = null): View
    {
        $respondent = $this->createRespondent(Version::Two, $quizEvent);

        return view('web.quiz-grid.index', [
            'respondentToken' => $respondent->token,
            'maxTiles' => 16,
        ]);
    }

    private function createRespondent(Version $version, ?QuizEvent $quizEvent = null): Respondent
    {
        return Respondent::create([
            'session_id' => session()->get('_token'),
            'quiz_event_id' => $quizEvent?->id,
            'token' => Uuid::uuid4()->toString(),
            'ip' => request()->ip(),
            'version' => $version->value,
        ]);
    }
}
