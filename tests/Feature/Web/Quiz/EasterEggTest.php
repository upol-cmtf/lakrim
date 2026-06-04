<?php
namespace Tests\Feature\Web\Quiz;

use App\Models\EasterEgg;
use App\Models\Respondent;
use Tests\TestCase;

class EasterEggTest extends TestCase
{
    private const ROUTE_NAME = 'web.quiz.easter-egg';

    protected function setUp(): void
    {
        parent::setUp();

        // Vyčisti easter eggy nasazené seeder migrací, ať si test řídí vlastní data.
        EasterEgg::query()->delete();
    }

    public function testRequiredParametersAreNotSet(): void
    {
        $this->postJson(route(self::ROUTE_NAME))
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['respondent_token']);
    }

    public function testRespondentNotFound(): void
    {
        $this->postJson(route(self::ROUTE_NAME), [
            'respondent_token' => 'abcdef',
        ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['respondent_token']);
    }

    public function testReturnsFirstUncompletedEasterEgg(): void
    {
        $first = EasterEgg::factory()->create();
        $second = EasterEgg::factory()->create();

        $respondent = Respondent::factory()->createOneQuietly();

        $response = $this->postJson(route(self::ROUTE_NAME), [
            'respondent_token' => $respondent->token,
        ])
            ->assertOk();

        $this->assertSame($first->id, $response->json('data.id'));
        $this->assertNotSame($second->id, $response->json('data.id'));
    }

    public function testSkipsEasterEggsAlreadyCompletedByRespondent(): void
    {
        $first = EasterEgg::factory()->create();
        $second = EasterEgg::factory()->create();

        $respondent = Respondent::factory()->createOneQuietly();
        $respondent->easterEggs()->create([
            'easter_egg_id' => $first->id,
            'seconds' => 10,
            'completed_at' => now(),
        ]);

        $response = $this->postJson(route(self::ROUTE_NAME), [
            'respondent_token' => $respondent->token,
        ])
            ->assertOk();

        $this->assertSame($second->id, $response->json('data.id'));
    }

    public function testReturnsNullWhenAllEasterEggsCompleted(): void
    {
        $easterEgg = EasterEgg::factory()->create();

        $respondent = Respondent::factory()->createOneQuietly();
        $respondent->easterEggs()->create([
            'easter_egg_id' => $easterEgg->id,
            'seconds' => 5,
            'completed_at' => now(),
        ]);

        $this->postJson(route(self::ROUTE_NAME), [
            'respondent_token' => $respondent->token,
        ])
            ->assertOk()
            ->assertExactJson(['data' => null]);
    }

    public function testCompletionIsScopedPerRespondent(): void
    {
        $easterEgg = EasterEgg::factory()->create();

        $otherRespondent = Respondent::factory()->createOneQuietly();
        $otherRespondent->easterEggs()->create([
            'easter_egg_id' => $easterEgg->id,
            'seconds' => 3,
            'completed_at' => now(),
        ]);

        $respondent = Respondent::factory()->createOneQuietly();

        $response = $this->postJson(route(self::ROUTE_NAME), [
            'respondent_token' => $respondent->token,
        ])
            ->assertOk();

        $this->assertSame($easterEgg->id, $response->json('data.id'));
    }

    public function testRendersImagePlaceholdersInDescriptionAndEvaluation(): void
    {
        $easterEgg = EasterEgg::factory()->create([
            'description' => 'Najdi poklad [[image:mapa]]',
            'evaluation' => 'Výborně [[image:odmena]]',
        ]);
        $easterEgg->images()->create([
            'key' => 'mapa',
            'path' => 'images/easter-eggs/mapa.png',
            'alt' => 'Mapa',
            'position' => 0,
        ]);
        $easterEgg->images()->create([
            'key' => 'odmena',
            'path' => 'images/easter-eggs/odmena.png',
            'alt' => 'Odměna',
            'position' => 1,
        ]);

        $respondent = Respondent::factory()->createOneQuietly();

        $response = $this->postJson(route(self::ROUTE_NAME), [
            'respondent_token' => $respondent->token,
        ])
            ->assertOk();

        $this->assertStringContainsString('<img', (string) $response->json('data.description'));
        $this->assertStringContainsString('mapa.png', (string) $response->json('data.description'));
        $this->assertStringContainsString('odmena.png', (string) $response->json('data.evaluation'));
    }
}
