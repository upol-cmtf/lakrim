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

        $token = $this->request->input('respondent_token');
        assert(is_string($token));

        $this->getRespondent($token)->update([
            'sex' => $this->request->input('sex'),
            'age_id' => $this->request->input('age_id'),
        ]);

        return response()->noContent();
    }

    public function storeStudentId(): Response
    {
        $this->validate($this->request, [
            'student_id' => 'required|string',
            'respondent_token' => 'required|string|exists:respondents,token',
        ]);

        $token = $this->request->input('respondent_token');
        assert(is_string($token));

        $this->getRespondent($token)->update([
            'student_id' => $this->request->input('student_id'),
        ]);

        return response()->noContent();
    }

    private function getRespondent(string $token): Respondent
    {
        $respondent = Respondent::where('token', $token)->first();
        assert($respondent instanceof Respondent);

        return $respondent;
    }
}
