<?php
namespace Database\Factories;

use App\Models\Island;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Island>
 */
class IslandFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $slug = $this->faker->unique()->word;

        return [
            'name' => $this->faker->word,
            'image' => $slug . '.webp',
            'guide' => $slug . '_guide.webp',
            'settings' => null,
        ];
    }
}
