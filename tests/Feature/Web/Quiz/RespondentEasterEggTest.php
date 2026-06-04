<?php
namespace Tests\Feature\Web\Quiz;

use App\Models\EasterEgg;
use App\Models\Respondent;
use Tests\TestCase;

class RespondentEasterEggTest extends TestCase
{
    private const ROUTE_NAME = 'web.quiz.respondent.easter-egg';

    public function testRequiredParametersAreNotSet(): void
    {
        $this->postJson(route(self::ROUTE_NAME))
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['respondent_token', 'easter_egg_id', 'seconds']);
    }

    public function testRespondentNotFound(): void
    {
        $easterEgg = EasterEgg::factory()->create();

        $this->postJson(route(self::ROUTE_NAME), [
            'respondent_token' => 'abcdef',
            'easter_egg_id' => $easterEgg->id,
            'seconds' => 12,
        ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['respondent_token']);
    }

    public function testEasterEggNotFound(): void
    {
        $respondent = Respondent::factory()->createOneQuietly();

        $this->postJson(route(self::ROUTE_NAME), [
            'respondent_token' => $respondent->token,
            'easter_egg_id' => 999999,
            'seconds' => 12,
        ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['easter_egg_id']);
    }

    public function testStoresCompletedEasterEgg(): void
    {
        $easterEgg = EasterEgg::factory()->create();
        $respondent = Respondent::factory()->createOneQuietly();

        $this->postJson(route(self::ROUTE_NAME), [
            'respondent_token' => $respondent->token,
            'easter_egg_id' => $easterEgg->id,
            'seconds' => 42,
        ])
            ->assertOk()
            ->assertJsonFragment(['completed' => true]);

        $this->assertDatabaseHas('respondent_easter_eggs', [
            'respondent_id' => $respondent->id,
            'easter_egg_id' => $easterEgg->id,
            'seconds' => 42,
        ]);

        $record = $respondent->easterEggs()->where('easter_egg_id', $easterEgg->id)->first();
        assert($record !== null);
        $this->assertNotNull($record->completed_at);
    }

    public function testStoringAlreadyCompletedEasterEggUpdatesInsteadOfDuplicating(): void
    {
        $easterEgg = EasterEgg::factory()->create();
        $respondent = Respondent::factory()->createOneQuietly();
        $respondent->easterEggs()->create([
            'easter_egg_id' => $easterEgg->id,
            'seconds' => 10,
            'completed_at' => now(),
        ]);

        $this->postJson(route(self::ROUTE_NAME), [
            'respondent_token' => $respondent->token,
            'easter_egg_id' => $easterEgg->id,
            'seconds' => 25,
        ])->assertOk();

        $this->assertSame(1, $respondent->easterEggs()->count());
        $this->assertDatabaseHas('respondent_easter_eggs', [
            'respondent_id' => $respondent->id,
            'easter_egg_id' => $easterEgg->id,
            'seconds' => 25,
        ]);
    }
}
