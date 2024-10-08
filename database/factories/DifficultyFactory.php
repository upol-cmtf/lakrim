<?php
namespace Database\Factories;

use App\Models\Difficulty;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Difficulty>
 */
class DifficultyFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'min_questions' => 1,
            'max_questions' => 10,
            'shuffle_questions' => true,
        ];
    }
}
