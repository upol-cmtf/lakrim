<?php
namespace App\Http\Controllers\Web\IslandGame;

use App\Enums\Version;
use App\Http\Controllers\Controller;
use App\Models\EasterEgg;
use App\Models\Island;
use App\Models\QuizEvent;
use App\Models\Respondent;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Ramsey\Uuid\Uuid;

final class HomepageController extends Controller
{
    /** Klíč v session, pod kterým držíme token rozehraného respondenta. */
    public const SESSION_TOKEN_KEY = 'island_game_respondent_token';

    /** Je v této session rozehraná výprava (existující respondent)? */
    public static function hasInProgressGame(): bool
    {
        $token = session()->get(self::SESSION_TOKEN_KEY);
        return $token !== null && Respondent::where('token', $token)->exists();
    }

    public function index(?QuizEvent $quizEvent = null): View
    {
        // Znovu použijeme respondenta rozehraného v této session (stejná akce),
        // ať se hráč po odchodu na úvodní stránku a návratu zpět vrátí ke svému
        // postupu místo startu od začátku. Nového založíme jen když žádný není
        // (nebo přišel na jinou akci).
        $token = session()->get(self::SESSION_TOKEN_KEY);
        $respondent = $token ? Respondent::where('token', $token)->first() : null;

        if ($respondent && $respondent->quiz_event_id !== $quizEvent?->id) {
            $respondent = null;
        }

        if (!$respondent) {
            $respondent = Respondent::create([
                'session_id' => session()->get('_token'),
                'quiz_event_id' => $quizEvent?->id,
                'token' => Uuid::uuid4()->toString(),
                'ip' => request()->ip(),
                'version' => Version::Three,
            ]);
            session()->put(self::SESSION_TOKEN_KEY, $respondent->token);
        }

        return view('web.island-game.index', [
            'respondentToken' => $respondent->token,
            'islands' => Island::query()->get(['id', 'name', 'image', 'guide', 'intro']),
            // počet easter eggů = počet rybek, které se mají ve scéně vygenerovat
            'easterEggsCount' => EasterEgg::query()->count(),
        ]);
    }

    /**
     * „Začít znovu" – zahodí rozehraného respondenta ze session, takže index()
     * při dalším vstupu založí nového a hráč začne s čistým štítem. Úklid stavu
     * v localStorage řeší frontend (po startu nového tokenu smaže cizí klíče).
     */
    public function restart(): RedirectResponse
    {
        session()->forget(self::SESSION_TOKEN_KEY);

        return redirect()->route('web.island-game.homepage');
    }
}
