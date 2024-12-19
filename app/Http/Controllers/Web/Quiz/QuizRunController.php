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
        $difficultyType = DifficultyEnum::Easy;
        $difficulty = Difficulty::find($difficultyType->value);
        assert($difficulty instanceof Difficulty);

        $respondent = Respondent::create([
            'session_id' => session()->get('_token'),
            'quiz_event_id' => $quizEvent?->id,
            'token' => Uuid::uuid4()->toString(),
            'ip' => request()->ip(),
            'difficulty_id' => $difficulty->id,
        ]);

        return view('web.quiz.run', [
            'respondentToken' => $respondent->token,
            'settings' => [
                'maxQuestions' => $difficulty->max_questions,
            ],
        ]);
    }
}
