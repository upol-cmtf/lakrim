<?php
namespace Database\Factories;

use App\Models\EasterEgg;
use App\Models\EasterEggImage;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<EasterEggImage>
 */
class EasterEggImageFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'easter_egg_id' => EasterEgg::factory(),
            'key' => $this->faker->unique()->slug(2),
            'path' => 'images/easter-eggs/' . $this->faker->word . '.png',
            'alt' => $this->faker->sentence,
            'position' => 0,
        ];
    }
}
