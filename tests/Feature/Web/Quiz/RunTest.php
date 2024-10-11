<?php
namespace Tests\Feature\Web\Quiz;

use App\Models\Respondent;
use Tests\TestCase;

class RunTest extends TestCase
{
    private const ROUTE_NAME = 'web.quiz.run';

    public function testSuccessful(): void
    {
        $this->get(route(self::ROUTE_NAME))
            ->assertOk();

        $this->assertDatabaseHas(Respondent::class, [
            'session_id' => session()->get('_token'),
            'ip' => request()->ip(),
            'difficulty_id' => 1,
            'age_id' => null,
            'sex' => null,
        ]);
    }
}
