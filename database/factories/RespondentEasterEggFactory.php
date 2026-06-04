<?php
namespace Database\Factories;

use App\Models\EasterEgg;
use App\Models\Respondent;
use App\Models\RespondentEasterEgg;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<RespondentEasterEgg>
 */
class RespondentEasterEggFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'respondent_id' => Respondent::factory(),
            'easter_egg_id' => EasterEgg::factory(),
            'seconds' => 0,
            'completed_at' => null,
        ];
    }
}
