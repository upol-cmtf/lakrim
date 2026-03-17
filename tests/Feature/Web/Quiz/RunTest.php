<?php
namespace Tests\Feature\Web\Quiz;

use App\Models\QuizEvent;
use App\Models\Respondent;
use Tests\TestCase;

class RunTest extends TestCase
{
    private const ROUTE_NAME = 'web.quiz-grid.homepage';

    public function testSuccessful(): void
    {
        $this->get(route(self::ROUTE_NAME))
            ->assertOk()
            ->assertSee('<questions-tiles', false);

        $this->assertDatabaseHas(Respondent::class, [
            'session_id' => session()->get('_token'),
            'quiz_event_id' => null,
            'ip' => request()->ip(),
            'difficulty_id' => 2,
            'age_id' => null,
            'sex' => null,
        ]);
    }

    public function testSuccessfulWithEvent(): void
    {
        $quizEvent = QuizEvent::factory()->create();

        $this->get(route(self::ROUTE_NAME, ['quizEvent' => $quizEvent->hash]))
            ->assertOk()
            ->assertSee('<questions-tiles', false);

        $this->assertDatabaseHas(Respondent::class, [
            'session_id' => session()->get('_token'),
            'quiz_event_id' => $quizEvent->id,
            'ip' => request()->ip(),
            'difficulty_id' => 2,
            'age_id' => null,
            'sex' => null,
        ]);
    }
}
