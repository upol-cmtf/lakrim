<?php
namespace App\Http\Controllers\Web\Quiz;

use App\Enums\Sex;
use App\Http\Controllers\Web\ApiController;
use App\Models\Respondent;
use Illuminate\Http\Response;
use Illuminate\Validation\Rule;

class RespondentIdentificationController extends ApiController
{
    public function store(): Response
    {
        $this->validate($this->request, [
            'sex' => [
                'nullable',
                Rule::in([Sex::Female->value, Sex::Male->value]),
            ],
            'age_id' => 'nullable|exists:ages,id',
            'respondent_token' => 'required|string|exists:respondents,token',
        ]);

        $respondent = Respondent::where('token', $this->request->input('respondent_token'))->first();
        assert($respondent instanceof Respondent);

        $respondent->update([
            'sex' => $this->request->input('sex'),
            'age_id' => $this->request->input('age_id'),
        ]);

        return response()->noContent();
    }
}
