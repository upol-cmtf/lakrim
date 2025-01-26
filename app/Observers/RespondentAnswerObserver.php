<?php
namespace App\Observers;

use App\Models\RespondentAnswer;

class RespondentAnswerObserver
{
    public function created(RespondentAnswer $respondentAnswer): void
    {
        $respondent = $respondentAnswer->respondent;

        if (!$respondent->isAllQuizQuestionsAnswered()) {
            return;
        }

        $respondent->update([
            'finished' => true,
        ]);
    }
}
