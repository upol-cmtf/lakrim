<?php
namespace App\Http\Controllers\Web\Quiz;

use App\Http\Controllers\Web\ApiController;
use Illuminate\Http\JsonResponse;

class QuestionController extends ApiController
{
    public function index(): JsonResponse
    {
        $this->validate($this->request, [
            'respondent_token' => 'required|string|exists:respondents,token',
        ]);

        // TODO implement the logic to return the next question
        // - otazku, kterou jeste nemel
        // ...

        return response()->json();
    }
}
