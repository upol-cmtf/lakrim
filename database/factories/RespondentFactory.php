<?php
namespace Database\Factories;

use App\Enums\Version;
use App\Models\Respondent;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Respondent>
 */
class RespondentFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'session_id' => $this->faker->uuid,
            'token' => $this->faker->uuid,
            'ip' => $this->faker->ipv4,
            'version' => Version::One->value,
        ];
    }
}
