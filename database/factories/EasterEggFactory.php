<?php
namespace Database\Factories;

use App\Models\EasterEgg;
use App\Models\EasterEggImage;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<EasterEgg>
 */
class EasterEggFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'description' => $this->faker->sentence,
            'evaluation' => $this->faker->text,
        ];
    }

    /**
     * @return Factory<EasterEgg>
     */
    public function withImages(int $count = 1): Factory
    {
        return $this->has(EasterEggImage::factory()->count($count), 'images');
    }
}
