<?php
namespace Database\Factories;

use App\Models\RespondentAnswer;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<RespondentAnswer>
 */
class RespondentAnswerFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'seconds' => $this->faker->numberBetween(0, 1000),
            'weight' => $this->faker->numberBetween(0, 1000),
        ];
    }
}
