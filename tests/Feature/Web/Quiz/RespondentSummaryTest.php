<?php
namespace Tests\Feature\Web\Quiz;

use App\Models\Difficulty;
use App\Models\Question;
use App\Models\QuestionOption;
use App\Models\Respondent;
use App\Models\RespondentAnswer;
use Tests\TestCase;

class RespondentSummaryTest extends TestCase
{
    private const ROUTE_NAME = 'web.quiz.respondent.summary';

    public function testRequiredParametersAreNotSet(): void
    {
        $this->postJson(route(self::ROUTE_NAME))
            ->assertUnprocessable()
            ->assertJsonFragment([
                'respondent_token' => ['respondent token musí být vyplněno.'],
            ]);
    }

    public function testRespondentNotFound(): void
    {
        $this->post(route(self::ROUTE_NAME), data: [
            'respondent_token' => 'abcdef',
        ])
            ->assertUnprocessable()
            ->assertJsonFragment([
                'respondent_token' => ['Zvolená hodnota pro respondent token není platná.'],
            ]);
    }

    public function testRespondentSummary(): void
    {
        $difficulty = Difficulty::easy()->first();
        assert($difficulty instanceof Difficulty);

        $question1 = Question::factory()
            ->has(QuestionOption::factory()->state(fn() => ['right' => false]), 'options')
            ->create([
                'difficulty_id' => $difficulty->id,
            ]);

        $question2 = Question::factory()
            ->has(QuestionOption::factory()->state(fn() => ['right' => true]), 'options')
            ->create([
                'difficulty_id' => $difficulty->id,
            ]);

        $option1 = $question1->options->first();
        $option2 = $question2->options->first();
        assert($option1 instanceof QuestionOption && $option2 instanceof QuestionOption);

        $respondent = Respondent::factory()
            ->has(
                RespondentAnswer::factory()->state(fn() => ['question_option_id' => $option1->id]),
                'answers',
            )
            ->has(
                RespondentAnswer::factory()->state(fn() => ['question_option_id' => $option2->id]),
                'answers',
            )
            ->createOneQuietly([
                'difficulty_id' => $difficulty->id,
            ]);

        $this->post(route(self::ROUTE_NAME), data: [
            'respondent_token' => $respondent->token,
        ])
            ->assertOk()
            ->assertExactJson([
                'data' => [
                    'statistics' => [
                        'totalQuestions' => 10,
                        'totalAnsweredQuestions' => 2,
                        'totalUnansweredQuestions' => 8,
                        'correctAnswers' => 1,
                        'incorrectAnswers' => 1,
                        'percentageCorrectAnswers' => 50,
                        'percentageIncorrectAnswers' => 50,
                    ],
                    'evaluation' => [
                        'right' => [$option2->summary],
                        'wrong' => [$option1->summary],
                    ],
                ],
            ]);
    }
}
