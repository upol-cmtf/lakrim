<?php
namespace App\Services\Respondent;

use App\Models\Respondent;
use Exception;

class Statistics
{
    private ?Respondent $respondent = null;

    public function setRespondent(Respondent $respondent): self
    {
        $this->respondent = $respondent;
        return $this;
    }

    /**
     * @return array{
     *     totalQuestions: int,
     *     totalAnsweredQuestions: int,
     *     totalUnansweredQuestions: int,
     *     correctAnswers: int,
     *     incorrectAnswers: int,
     *     percentageCorrectAnswers: int,
     *     percentageIncorrectAnswers: int,
     * }
     * @throws Exception
     */
    public function getStatistics(): array
    {
        if (!$this->respondent) {
            throw new Exception('Respondent not set!');
        }

        $percentageCorrectAnswers = $percentageIncorrectAnswers = 0;
        $totalQuestions = $this->respondent->difficulty->max_questions;
        $totalAnsweredQuestions = $this->respondent->answers->count();
        $rightAnswers = $this->getTotalRightAnswers();

        if ($totalAnsweredQuestions) {
            $percentageCorrectAnswers = (int) round(($rightAnswers / $totalAnsweredQuestions) * 100);
            $percentageIncorrectAnswers = 100 - $percentageCorrectAnswers;
        }

        return [
            'totalQuestions' => $totalQuestions,
            'totalAnsweredQuestions' => $totalAnsweredQuestions,
            'totalUnansweredQuestions' => $totalQuestions - $totalAnsweredQuestions,
            'correctAnswers' => $rightAnswers,
            'incorrectAnswers' => $totalAnsweredQuestions - $rightAnswers,
            'percentageCorrectAnswers' => $percentageCorrectAnswers,
            'percentageIncorrectAnswers' => $percentageIncorrectAnswers,
        ];
    }

    private function getTotalRightAnswers(): int
    {
        assert($this->respondent instanceof Respondent);
        $total = 0;

        foreach ($this->respondent->answers as $answer) {
            $total += (int) $answer->isRightAnswer();
        }

        return $total;
    }
}
