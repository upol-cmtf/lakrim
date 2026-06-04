<?php
namespace App\Http\Controllers\Web\Quiz;

use App\Http\Controllers\Web\ApiController;
use App\Http\Resources\Web\ArrayResource;
use App\Models\Respondent;

class RespondentEasterEggController extends ApiController
{
    /**
     * Marks an easter egg as completed by the respondent within their session.
     */
    public function store(): ArrayResource
    {
        $this->validate($this->request, [
            'respondent_token' => 'required|string|exists:respondents,token',
            'easter_egg_id' => 'required|integer|exists:easter_eggs,id',
            'seconds' => 'required|integer',
        ]);

        $respondent = Respondent::where('token', $this->request->input('respondent_token'))->first();
        assert($respondent instanceof Respondent);

        $respondent->easterEggs()->updateOrCreate(
            ['easter_egg_id' => $this->request->input('easter_egg_id')],
            [
                'seconds' => $this->request->input('seconds'),
                'completed_at' => now(),
            ],
        );

        return new ArrayResource(['completed' => true]);
    }
}
