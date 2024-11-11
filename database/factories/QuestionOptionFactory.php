<?php
namespace Database\Factories;

use App\Models\QuestionOption;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<QuestionOption>
 */
class QuestionOptionFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'description' => $this->faker->text,
            'evaluation' => $this->faker->text,
            'summary' => $this->faker->text,
            'name' => $this->faker->word,
            'right' => $this->faker->boolean,
            'weight' => $this->faker->randomFloat(2, 0, 1),
        ];
    }
}
