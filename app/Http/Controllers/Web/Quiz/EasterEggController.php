<?php
namespace App\Http\Controllers\Web\Quiz;

use App\Http\Controllers\Web\ApiController;
use App\Http\Resources\Web\EasterEggResource;
use App\Models\EasterEgg;
use App\Models\Respondent;
use Illuminate\Http\JsonResponse;

class EasterEggController extends ApiController
{
    /**
     * Returns the first easter egg the respondent has not completed yet within
     * their session, or null when there are none left.
     */
    public function index(): EasterEggResource|JsonResponse
    {
        $this->validate($this->request, [
            'respondent_token' => 'required|string|exists:respondents,token',
        ]);

        $respondent = Respondent::where('token', $this->request->input('respondent_token'))->first();
        assert($respondent instanceof Respondent);

        $completedEasterEggIds = $respondent->easterEggs()->pluck('easter_egg_id')->all();

        $easterEgg = EasterEgg::query()
            ->whereNotIn('id', $completedEasterEggIds)
            ->orderBy('id')
            ->first();

        if (!$easterEgg instanceof EasterEgg) {
            return new JsonResponse(['data' => null]);
        }

        return new EasterEggResource($easterEgg);
    }
}
