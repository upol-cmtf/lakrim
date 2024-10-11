<?php
namespace App\Http\Controllers\Web\Quiz;

use App\Enums\Difficulty;
use App\Http\Controllers\Controller;
use App\Models\Respondent;
use Illuminate\Contracts\View\View;
use Ramsey\Uuid\Uuid;

class QuizRunController extends Controller
{
    public function run(): View
    {
        $respondent = Respondent::create([
            'session_id' => session()->get('_token'),
            'token' => Uuid::uuid4()->toString(),
            'ip' => request()->ip(),
            'difficulty_id' => Difficulty::Easy->value,
        ]);

        return view('web.quiz.run', [
            'uuid' => $respondent->token,
        ]);
    }
}
