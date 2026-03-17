<?php
namespace App\Http\Controllers\Web\QuizGrid;

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
        $difficulty = Difficulty::find(DifficultyEnum::Medium->value);
        assert($difficulty instanceof Difficulty);

        $respondent = Respondent::create([
            'session_id' => session()->get('_token'),
            'quiz_event_id' => $quizEvent?->id,
            'token' => Uuid::uuid4()->toString(),
            'ip' => request()->ip(),
            'difficulty_id' => $difficulty->id,
        ]);

        return view('web.quiz-grid.index', [
            'respondentToken' => $respondent->token,
            'maxTiles' => 16,
        ]);
    }
}
