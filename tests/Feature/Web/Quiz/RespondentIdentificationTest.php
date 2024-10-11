<?php
namespace Tests\Feature\Web\Quiz;

use App\Models\Age;
use App\Models\Respondent;
use Tests\TestCase;

class RespondentIdentificationTest extends TestCase
{
    private const ROUTE_NAME = 'web.quiz.respondent.identification';

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

    public function testInvalidInputData(): void
    {
        $respondent = Respondent::factory()->createOneQuietly();
        $this->post(route(self::ROUTE_NAME), data: [
            'respondent_token' => $respondent->token,
            'sex' => 'invalid',
            'age_id' => 1212,
        ])
            ->assertUnprocessable()
            ->assertJsonMissingValidationErrors(['respondent_token'])
            ->assertJsonFragment([
                'sex' => ['Zvolená hodnota pro sex není platná.'],
                'age_id' => ['Zvolená hodnota pro age id není platná.'],
            ]);
    }

    public function testStoreRespondentWithoutIdentification(): void
    {
        $respondent = Respondent::factory()->createOneQuietly();
        $this->post(route(self::ROUTE_NAME), data: [
            'respondent_token' => $respondent->token,
        ])
            ->assertNoContent();

        $this->assertDatabaseHas(Respondent::class, [
            'id' => $respondent->id,
            'sex' => null,
            'age_id' => null,
        ]);
    }

    public function testStoreRespondentIdentification(): void
    {
        $age = Age::factory()->createOneQuietly();
        $respondent = Respondent::factory()->createOneQuietly();

        $this->post(route(self::ROUTE_NAME), data: [
            'respondent_token' => $respondent->token,
            'sex' => 'M',
            'age_id' => $age->id,
        ])
            ->assertNoContent();

        $this->assertDatabaseHas(Respondent::class, [
            'id' => $respondent->id,
            'sex' => 'M',
            'age_id' => $age->id,
        ]);
    }
}
