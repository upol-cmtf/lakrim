<?php
namespace Database\Factories;

use App\Models\Age;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Age>
 */
class AgeFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->word,
        ];
    }
}
