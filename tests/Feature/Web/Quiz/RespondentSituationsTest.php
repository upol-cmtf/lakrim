<?php
namespace Tests\Feature\Web\Quiz;

use App\Enums\Version;
use App\Models\Island;
use App\Models\Respondent;
use App\Models\RespondentSituation;
use App\Models\Situation;
use Tests\TestCase;

class RespondentSituationsTest extends TestCase
{
    private const ROUTE_NAME = 'web.quiz.respondent.situations';

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

    public function testReturnsSeededIslandsWithEmptySituationsByDefault(): void
    {
        $respondent = Respondent::factory()->createOneQuietly([
            'version' => Version::Three->value,
        ]);

        $response = $this->post(route(self::ROUTE_NAME), data: [
            'respondent_token' => $respondent->token,
        ])
            ->assertOk();

        $data = $response->json('data');
        assert(is_array($data));
        $this->assertCount(4, $data);

        foreach ($data as $island) {
            assert(is_array($island));
            $this->assertArrayHasKey('id', $island);
            $this->assertArrayHasKey('name', $island);
            $this->assertArrayHasKey('image', $island);
            $this->assertSame([], $island['situations']);
        }
    }

    public function testReturnsSituationsWithCompletionState(): void
    {
        $island1 = Island::query()->find(1);
        $island2 = Island::query()->find(2);
        assert($island1 instanceof Island && $island2 instanceof Island);

        $situation1 = Situation::factory()->create([
            'island_id' => $island1->id,
            'position' => 1,
            'title' => 'Situace 1A',
        ]);
        $situation2 = Situation::factory()->create([
            'island_id' => $island1->id,
            'position' => 2,
            'title' => 'Situace 1B',
        ]);
        $situation3 = Situation::factory()->create([
            'island_id' => $island2->id,
            'position' => 1,
            'title' => 'Situace 2A',
        ]);

        $respondent = Respondent::factory()->createOneQuietly([
            'version' => Version::Three->value,
        ]);

        RespondentSituation::factory()->create([
            'respondent_id' => $respondent->id,
            'situation_id' => $situation1->id,
            'completed_at' => now(),
        ]);

        RespondentSituation::factory()->create([
            'respondent_id' => $respondent->id,
            'situation_id' => $situation2->id,
            'completed_at' => null,
        ]);

        $response = $this->post(route(self::ROUTE_NAME), data: [
            'respondent_token' => $respondent->token,
        ])
            ->assertOk();

        $data = $response->json('data');
        assert(is_array($data));

        $island1Data = $this->findIsland($data, $island1->id);
        $this->assertSame([
            ['id' => $situation1->id, 'position' => 1, 'title' => 'Situace 1A', 'completed' => true],
            ['id' => $situation2->id, 'position' => 2, 'title' => 'Situace 1B', 'completed' => false],
        ], $island1Data['situations']);

        $island2Data = $this->findIsland($data, $island2->id);
        $this->assertSame([
            ['id' => $situation3->id, 'position' => 1, 'title' => 'Situace 2A', 'completed' => false],
        ], $island2Data['situations']);
    }

    public function testSituationsAreOrderedByPosition(): void
    {
        $island = Island::query()->find(1);
        assert($island instanceof Island);

        $situation3 = Situation::factory()->create(['island_id' => $island->id, 'position' => 3]);
        $situation1 = Situation::factory()->create(['island_id' => $island->id, 'position' => 1]);
        $situation2 = Situation::factory()->create(['island_id' => $island->id, 'position' => 2]);

        $respondent = Respondent::factory()->createOneQuietly([
            'version' => Version::Three->value,
        ]);

        $response = $this->post(route(self::ROUTE_NAME), data: [
            'respondent_token' => $respondent->token,
        ])
            ->assertOk();

        $data = $response->json('data');
        assert(is_array($data));

        $islandData = $this->findIsland($data, $island->id);
        $this->assertSame(
            [$situation1->id, $situation2->id, $situation3->id],
            array_column($islandData['situations'], 'id'),
        );
    }

    public function testCompletionIsScopedPerRespondent(): void
    {
        $island = Island::query()->find(1);
        assert($island instanceof Island);

        $situation = Situation::factory()->create([
            'island_id' => $island->id,
            'position' => 1,
        ]);

        $otherRespondent = Respondent::factory()->createOneQuietly([
            'version' => Version::Three->value,
        ]);
        RespondentSituation::factory()->create([
            'respondent_id' => $otherRespondent->id,
            'situation_id' => $situation->id,
            'completed_at' => now(),
        ]);

        $respondent = Respondent::factory()->createOneQuietly([
            'version' => Version::Three->value,
        ]);

        $response = $this->post(route(self::ROUTE_NAME), data: [
            'respondent_token' => $respondent->token,
        ])
            ->assertOk();

        $data = $response->json('data');
        assert(is_array($data));

        $islandData = $this->findIsland($data, $island->id);
        $this->assertFalse($islandData['situations'][0]['completed']);
    }

    /**
     * @param array<mixed, mixed> $data
     * @return array{
     *     id: int,
     *     name: string,
     *     image: string,
     *     situations: list<array{id: int, position: int, title: string|null, completed: bool}>,
     * }
     */
    private function findIsland(array $data, int $islandId): array
    {
        foreach ($data as $island) {
            assert(is_array($island));
            if ($island['id'] === $islandId) {
                /** @var array{
                 *     id: int,
                 *     name: string,
                 *     image: string,
                 *     situations: list<array{id: int, position: int, title: string|null, completed: bool}>,
                 * } $island
                 */
                return $island;
            }
        }

        $this->fail("Island {$islandId} not found in response");
    }
}
