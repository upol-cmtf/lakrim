<?php
namespace Database\Factories;

use App\Models\Island;
use App\Models\Situation;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Situation>
 */
class SituationFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'island_id' => Island::factory(),
            'position' => $this->faker->numberBetween(1, 5),
            'title' => $this->faker->sentence,
            'safety_card' => null,
        ];
    }

    /**
     * Situace, která hráči po správném zvládnutí udělí kartu bezpečí.
     *
     * @return Factory<Situation>
     */
    public function withSafetyCard(?string $safetyCard = null): Factory
    {
        return $this->state(fn(array $attributes): array => [
            'safety_card' => $safetyCard ?? $this->faker->paragraph,
        ]);
    }
}
