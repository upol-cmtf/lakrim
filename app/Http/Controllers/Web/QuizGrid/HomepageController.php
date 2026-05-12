<?php
namespace App\Http\Controllers\Web\QuizGrid;

use App\Enums\Version;
use App\Http\Controllers\Controller;
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
            'version' => Version::Two,
        ]);

        return view('web.quiz-grid.index', [
            'respondentToken' => $respondent->token,
            'maxTiles' => 16,
        ]);
    }
}
