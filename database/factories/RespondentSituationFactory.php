<?php
namespace Database\Factories;

use App\Models\Respondent;
use App\Models\RespondentSituation;
use App\Models\Situation;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<RespondentSituation>
 */
class RespondentSituationFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'respondent_id' => Respondent::factory(),
            'situation_id' => Situation::factory(),
            'completed_at' => null,
        ];
    }
}
