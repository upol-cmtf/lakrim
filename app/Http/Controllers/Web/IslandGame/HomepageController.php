<?php
namespace App\Http\Controllers\Web\IslandGame;

use App\Enums\Version;
use App\Http\Controllers\Controller;
use App\Models\Island;
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
            'version' => Version::Three,
        ]);

        return view('web.island-game.index', [
            'respondentToken' => $respondent->token,
            'islands' => Island::query()->get(['id', 'name', 'image', 'guide']),
        ]);
    }
}
