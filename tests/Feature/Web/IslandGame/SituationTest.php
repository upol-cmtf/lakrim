<?php
namespace Tests\Feature\Web\IslandGame;

use App\Enums\Version;
use App\Models\Question;
use App\Models\QuestionOption;
use App\Models\Respondent;
use App\Models\RespondentAnswer;
use App\Models\Situation;
use Tests\TestCase;

class SituationTest extends TestCase
{
    private const ROUTE_NAME = 'web.island-game.situation';

    protected function setUp(): void
    {
        parent::setUp();

        // Seedované situace by interferovaly s testovacími – každý test si vytváří vlastní.
        Situation::query()->delete();
    }

    public function testRequiredParametersAreNotSet(): void
    {
        $this->postJson(route(self::ROUTE_NAME))
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['respondent_token', 'island_id', 'button']);
    }

    public function testButtonCannotExceedFive(): void
    {
        $respondent = $this->respondent();

        $this->postJson(route(self::ROUTE_NAME), [
            'respondent_token' => $respondent->token,
            'island_id' => 1,
            'button' => 6,
        ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['button']);
    }

    public function testReturnsSituationForButton(): void
    {
        $respondent = $this->respondent();
        $situation = $this->situation(button: 1, difficulty: 1);

        $this->postJson(route(self::ROUTE_NAME), [
            'respondent_token' => $respondent->token,
            'island_id' => 1,
            'button' => 1,
        ])
            ->assertOk()
            ->assertJsonPath('data.id', $situation->id)
            ->assertJsonPath('data.position', 1)
            ->assertJsonPath('data.question.id', $situation->question_id);
    }

    public function testSkipsSituationsAlreadyAnswered(): void
    {
        $respondent = $this->respondent();
        $answered = $this->situation(button: 1, difficulty: 1);
        $available = $this->situation(button: 1, difficulty: 1);

        $this->answer($respondent, $answered->question, right: true);

        $this->postJson(route(self::ROUTE_NAME), [
            'respondent_token' => $respondent->token,
            'island_id' => 1,
            'button' => 1,
        ])
            ->assertOk()
            ->assertJsonPath('data.id', $available->id);
    }

    public function testReturnsNotFoundWhenNoSituationAvailable(): void
    {
        $respondent = $this->respondent();
        // situace existuje jen pro tlačítko 1, ptáme se na tlačítko 2
        $this->situation(button: 1, difficulty: 1);

        $this->postJson(route(self::ROUTE_NAME), [
            'respondent_token' => $respondent->token,
            'island_id' => 1,
            'button' => 2,
        ])
            ->assertNotFound()
            ->assertJsonPath('data.code', 'no_situation_available');
    }

    public function testServesHarderQuestionAfterCorrectStreak(): void
    {
        $respondent = $this->respondent();

        // čtyři správné odpovědi v řadě → cílová obtížnost 3
        for ($i = 0; $i < 4; $i++) {
            $this->answer($respondent, Question::factory()->create(), right: true);
        }

        $this->situation(button: 1, difficulty: 1);
        $hard = $this->situation(button: 1, difficulty: 3);

        $this->postJson(route(self::ROUTE_NAME), [
            'respondent_token' => $respondent->token,
            'island_id' => 1,
            'button' => 1,
        ])
            ->assertOk()
            ->assertJsonPath('data.id', $hard->id);
    }

    public function testWrongAnswerResetsDifficultyToEasy(): void
    {
        $respondent = $this->respondent();

        // tři správné, ale poslední odpověď špatná → série 0 → obtížnost 1
        for ($i = 0; $i < 3; $i++) {
            $this->answer($respondent, Question::factory()->create(), right: true);
        }
        $this->answer($respondent, Question::factory()->create(), right: false);

        $easy = $this->situation(button: 1, difficulty: 1);
        $this->situation(button: 1, difficulty: 3);

        $this->postJson(route(self::ROUTE_NAME), [
            'respondent_token' => $respondent->token,
            'island_id' => 1,
            'button' => 1,
        ])
            ->assertOk()
            ->assertJsonPath('data.id', $easy->id);
    }

    private function respondent(): Respondent
    {
        return Respondent::factory()->createOneQuietly([
            'version' => Version::Three->value,
        ]);
    }

    private function situation(int $button, int $difficulty): Situation
    {
        $question = Question::factory()->create(['difficulty_id' => $difficulty]);

        return Situation::factory()->create([
            'island_id' => 1,
            'question_id' => $question->id,
            'position' => $button,
        ]);
    }

    private function answer(Respondent $respondent, Question $question, bool $right): void
    {
        $option = QuestionOption::factory()->create([
            'question_id' => $question->id,
            'right' => $right,
        ]);

        RespondentAnswer::factory()->create([
            'respondent_id' => $respondent->id,
            'question_option_id' => $option->id,
            'attempt' => 1,
        ]);
    }
}
