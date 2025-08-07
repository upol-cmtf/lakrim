<?php
namespace Tests\Feature\Web\Quiz;

use App\Models\Difficulty;
use App\Models\Question;
use App\Models\QuestionOption;
use App\Models\Respondent;
use App\Models\RespondentAnswer;
use Tests\TestCase;

class QuestionTest extends TestCase
{
    private const ROUTE_NAME = 'web.quiz.question';

    public function testItThrownMaximumQuestionsExceededException(): void
    {
        $difficulty = Difficulty::easy()->first();
        assert($difficulty instanceof Difficulty);

        $difficulty->update([
            'max_questions' => 1,
        ]);

        $question = Question::factory()
            ->has(QuestionOption::factory()->count(4), 'options')
            ->create([
                'difficulty_id' => $difficulty->id,
            ]);
        $option = $question->options->first();
        assert($option instanceof QuestionOption);

        $respondent = Respondent::factory()
            ->has(
                RespondentAnswer::factory()->state(fn() => ['question_option_id' => $option->id]),
                'answers',
            )
            ->createOneQuietly([
                'difficulty_id' => $difficulty->id,
            ]);

        $this->postJson(route(self::ROUTE_NAME), [
            'respondent_token' => $respondent->token,
        ])
            ->assertBadRequest()
            ->assertExactJson([
                'data' => [
                    'code' => 'maximum_questions_exceeded',
                    'error' => $this->translator->get('questions.maximum_questions_exceeded'),
                ],
            ]);
    }

    public function testItReturnsTheFirstQuestionIfNoneHasBeenAnsweredYet(): void
    {
        $difficulty = Difficulty::easy()->first();
        assert($difficulty instanceof Difficulty);

        [$question] = Question::factory()
            ->count(2)
            ->has(QuestionOption::factory()->count(4), 'options')
            ->create([
                'difficulty_id' => $difficulty->id,
            ]);
        assert($question instanceof Question);

        $respondent = Respondent::factory()->createOneQuietly([
            'difficulty_id' => $difficulty->id,
        ]);

        $this->postJson(route(self::ROUTE_NAME), [
            'respondent_token' => $respondent->token,
        ])
            ->assertOk()
            ->assertExactJson([
                'data' => [
                    'id' => $question->id,
                    'perex' => $question->perex,
                    'description' => $question->description,
                    // @phpstan-ignore argument.type
                    'options' => $question->getOptions()->map(fn(QuestionOption $option): array => [
                        'description' => $option->description,
                        'id' => $option->id,
                        'name' => $option->name,
                    ]),
                    'group' => [
                        'id' => $question->questionGroup->id,
                        'name' => $question->questionGroup->name,
                    ],
                    'settings' => null,
                    'type' => $question->type,
                ],
            ]);
    }

    public function testReturnsTheSecondQuestionIfTheFirstOneHasAlreadyBeenAnswered(): void
    {
        $difficulty = Difficulty::easy()->first();
        assert($difficulty instanceof Difficulty);

        [$question1, $question2] = Question::factory()
            ->count(2)
            ->has(QuestionOption::factory()->count(4), 'options')
            ->create([
                'difficulty_id' => $difficulty->id,
            ]);
        assert($question1 instanceof Question && $question2 instanceof Question);

        $option = $question1->options->first();
        assert($option instanceof QuestionOption);

        $respondent = Respondent::factory()
            ->has(
                RespondentAnswer::factory()->state(fn() => ['question_option_id' => $option->id]),
                'answers',
            )
            ->createOneQuietly([
                'difficulty_id' => $difficulty->id,
            ]);

        $this->postJson(route(self::ROUTE_NAME), [
            'respondent_token' => $respondent->token,
        ])
            ->assertOk()
            ->assertExactJson([
                'data' => [
                    'id' => $question2->id,
                    'perex' => $question2->perex,
                    'description' => $question2->description,
                    // @phpstan-ignore argument.type
                    'options' => $question2->getOptions()->map(fn(QuestionOption $option): array => [
                        'description' => $option->description,
                        'id' => $option->id,
                        'name' => $option->name,
                    ]),
                    'group' => [
                        'id' => $question2->questionGroup->id,
                        'name' => $question2->questionGroup->name,
                    ],
                    'settings' => null,
                    'type' => $question2->type,
                ],
            ]);
    }
}
