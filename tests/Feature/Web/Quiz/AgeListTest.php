<?php
namespace Tests\Feature\Web\Quiz;

use App\Models\Age;
use Tests\TestCase;

class AgeListTest extends TestCase
{
    private const ROUTE_NAME = 'web.quiz.age-list';

    public function testAgeListEmpty(): void
    {
        $this->get(route(self::ROUTE_NAME))
            ->assertOk()
            ->assertJson([]);
    }

    public function testAgeList(): void
    {
        Age::factory()->count(3)->create();

        $this->get(route(self::ROUTE_NAME))
            ->assertOk()
            ->assertExactJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'name',
                    ],
                ],
            ]);
    }
}
