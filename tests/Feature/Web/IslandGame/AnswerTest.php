<?php
namespace Tests\Feature\Web\IslandGame;

use App\Enums\Version;
use App\Models\Question;
use App\Models\QuestionOption;
use App\Models\Respondent;
use App\Models\RespondentSituation;
use App\Models\Situation;
use Tests\TestCase;

class AnswerTest extends TestCase
{
    private const ROUTE_NAME = 'web.island-game.answer';

    public function testRequiredParametersAreNotSet(): void
    {
        $this->postJson(route(self::ROUTE_NAME))
            ->assertUnprocessable()
            ->assertJsonValidationErrors([
                'respondent_token',
                'question_id',
                'option_ids',
                'seconds',
                'attempt',
                'island_id',
                'button',
            ]);
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

    public function testStoresAnswerWithIslandAndButton(): void
    {
        $respondent = $this->respondent();
        $scenario = $this->scenario(button: 3);

        $this->postJson(route(self::ROUTE_NAME), [
            'respondent_token' => $respondent->token,
            'question_id' => $scenario['question']->id,
            'option_ids' => [$scenario['right']->id],
            'seconds' => 12,
            'attempt' => 1,
            'island_id' => 1,
            'button' => 3,
        ])
            ->assertOk()
            ->assertJsonPath('data.correct', true);

        $this->assertDatabaseHas('respondents_answers', [
            'respondent_id' => $respondent->id,
            'question_option_id' => $scenario['right']->id,
            'island_id' => 1,
            'button' => 3,
        ]);
    }

    public function testCorrectAnswerCompletesSituationAndReturnsSafetyCard(): void
    {
        $respondent = $this->respondent();
        $scenario = $this->scenario(button: 1, safetyCard: 'Nikdy nesděluj PIN po telefonu.');

        $this->postJson(route(self::ROUTE_NAME), [
            'respondent_token' => $respondent->token,
            'question_id' => $scenario['question']->id,
            'option_ids' => [$scenario['right']->id],
            'seconds' => 8,
            'attempt' => 1,
            'island_id' => 1,
            'button' => 1,
        ])
            ->assertOk()
            ->assertJsonPath('data.correct', true)
            ->assertJsonPath('data.safetyCard', 'Nikdy nesděluj PIN po telefonu.');

        $respondentSituation = RespondentSituation::query()
            ->where('respondent_id', $respondent->id)
            ->where('situation_id', $scenario['situation']->id)
            ->first();

        assert($respondentSituation instanceof RespondentSituation);
        $this->assertNotNull($respondentSituation->completed_at);
    }

    public function testWrongAnswerDoesNotCompleteSituation(): void
    {
        $respondent = $this->respondent();
        $scenario = $this->scenario(button: 1, safetyCard: 'Karta bezpečí');

        $this->postJson(route(self::ROUTE_NAME), [
            'respondent_token' => $respondent->token,
            'question_id' => $scenario['question']->id,
            'option_ids' => [$scenario['wrong']->id],
            'seconds' => 8,
            'attempt' => 1,
            'island_id' => 1,
            'button' => 1,
        ])
            ->assertOk()
            ->assertJsonPath('data.correct', false)
            ->assertJsonPath('data.safetyCard', null);

        // odpověď se uloží, ale situace zůstává nesplněná
        $this->assertDatabaseHas('respondents_answers', [
            'respondent_id' => $respondent->id,
            'question_option_id' => $scenario['wrong']->id,
        ]);
        $this->assertDatabaseMissing('respondent_situations', [
            'respondent_id' => $respondent->id,
            'situation_id' => $scenario['situation']->id,
        ]);
    }

    public function testAnswerWithOneOfMultipleRightOptionsIsCorrect(): void
    {
        $respondent = $this->respondent();
        $scenario = $this->scenario(button: 1);

        // situace má víc přijatelných odpovědí – stačí vybrat některou z nich
        QuestionOption::factory()->create([
            'question_id' => $scenario['question']->id,
            'right' => true,
        ]);

        $this->postJson(route(self::ROUTE_NAME), [
            'respondent_token' => $respondent->token,
            'question_id' => $scenario['question']->id,
            'option_ids' => [$scenario['right']->id],
            'seconds' => 5,
            'attempt' => 1,
            'island_id' => 1,
            'button' => 1,
        ])
            ->assertOk()
            ->assertJsonPath('data.correct', true);
    }

    private function respondent(): Respondent
    {
        return Respondent::factory()->createOneQuietly([
            'version' => Version::Three->value,
        ]);
    }

    /**
     * @return array{question: Question, situation: Situation, right: QuestionOption, wrong: QuestionOption}
     */
    private function scenario(int $button, ?string $safetyCard = null): array
    {
        $question = Question::factory()->create(['difficulty_id' => 1]);
        $right = QuestionOption::factory()->create(['question_id' => $question->id, 'right' => true]);
        $wrong = QuestionOption::factory()->create(['question_id' => $question->id, 'right' => false]);

        $situation = Situation::factory()->create([
            'island_id' => 1,
            'question_id' => $question->id,
            'position' => $button,
            'safety_card' => $safetyCard,
        ]);

        return [
            'question' => $question,
            'situation' => $situation,
            'right' => $right,
            'wrong' => $wrong,
        ];
    }
}
