<?php
namespace Tests\Feature\Web\Quiz;

use App\Models\Difficulty;
use App\Models\Question;
use App\Models\QuestionOption;
use App\Models\Respondent;
use App\Models\RespondentAnswer;
use Tests\TestCase;

class AnswerTest extends TestCase
{
    private const ROUTE_NAME = 'web.quiz.answer';

    public function testRequiredParametersAreNotSet(): void
    {
        $this->postJson(route(self::ROUTE_NAME))
            ->assertUnprocessable()
            ->assertJsonFragment([
                'seconds' => ['seconds musí být vyplněno.'],
                'respondent_token' => ['respondent token musí být vyplněno.'],
                'question_id' => ['question id musí být vyplněno.'],
                'option_id' => ['option id musí být vyplněno.'],
            ]);
    }

    public function testQuestionNotFound(): void
    {
        $respondent = Respondent::factory()->createOneQuietly();
        assert($respondent instanceof Respondent);

        $this->post(route(self::ROUTE_NAME), data: [
            'seconds' => 10,
            'respondent_token' => $respondent->token,
            'question_id' => 9987,
            'option_id' => 1212,
        ])
            ->assertUnprocessable()
            ->assertJsonMissingValidationErrors(['respondent_token', 'seconds'])
            ->assertJsonFragment([
                'question_id' => ['Zvolená hodnota pro question id není platná.'],
                'option_id' => ['Zvolená hodnota pro option id není platná.'],
            ]);
    }

    public function testQuestionOptionNotFound(): void
    {
        $respondent = Respondent::factory()->createOneQuietly();
        $question = Question::factory()->createOneQuietly();

        $this->post(route(self::ROUTE_NAME), data: [
            'seconds' => 10,
            'respondent_token' => $respondent->token,
            'question_id' => $question->id,
            'option_id' => 1212,
        ])
            ->assertUnprocessable()
            ->assertJsonMissingValidationErrors(['respondent_token', 'question_id', 'seconds'])
            ->assertJsonFragment([
                'option_id' => ['Zvolená hodnota pro option id není platná.'],
            ]);
    }

    public function testStoreAnswer(): void
    {
        $difficulty = Difficulty::easy()->first();
        assert($difficulty instanceof Difficulty);

        $respondent = Respondent::factory()->createOneQuietly([
            'difficulty_id' => $difficulty->id,
        ]);
        $question = Question::factory()
            ->has(QuestionOption::factory()->count(4), 'options')
            ->create([
                'difficulty_id' => $difficulty->id,
            ]);
        $option = $question->options->first();
        assert($option instanceof QuestionOption);

        $this->post(route(self::ROUTE_NAME), data: [
            'seconds' => 10,
            'respondent_token' => $respondent->token,
            'question_id' => $question->id,
            'option_id' => $option->id,
        ])
            ->assertOk()
            ->assertExactJson([
                'data' => [
                    'end' => false,
                    'evaluation' => $option->evaluation,
                    'evaluationTitle' => $option->evaluation_title,
                    'rightAnswer' => $option->right,
                ],
            ]);

        $this->assertDatabaseHas(RespondentAnswer::class, [
            'respondent_id' => $respondent->id,
            'question_option_id' => $option->id,
            'seconds' => 10,
            'weight' => $option->weight,
        ]);
    }

    public function testStoreLastAnswer(): void
    {
        $difficulty = Difficulty::easy()->first();
        assert($difficulty instanceof Difficulty);

        $difficulty->update([
            'max_questions' => 1,
        ]);

        $respondent = Respondent::factory()->createOneQuietly([
            'difficulty_id' => $difficulty->id,
        ]);
        $question = Question::factory()
            ->has(QuestionOption::factory()->count(4), 'options')
            ->create([
                'difficulty_id' => $difficulty->id,
            ]);
        $option = $question->options->first();
        assert($option instanceof QuestionOption);

        $this->post(route(self::ROUTE_NAME), data: [
            'seconds' => 10,
            'respondent_token' => $respondent->token,
            'question_id' => $question->id,
            'option_id' => $option->id,
        ])
            ->assertOk()
            ->assertExactJson([
                'data' => [
                    'end' => true,
                    'evaluation' => $option->evaluation,
                    'evaluationTitle' => $option->evaluation_title,
                    'rightAnswer' => $option->right,
                ],
            ]);

        $this->assertDatabaseHas(RespondentAnswer::class, [
            'respondent_id' => $respondent->id,
            'question_option_id' => $option->id,
            'seconds' => 10,
            'weight' => $option->weight,
        ]);
    }
}
