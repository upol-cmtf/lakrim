<?php
namespace Database\Factories;

use App\Models\QuizEvent;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<QuizEvent>
 */
class QuizEventFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->word,
            'hash' => $this->faker->unique()->word,
        ];
    }
}
