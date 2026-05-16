<?php
namespace Database\Factories;

use App\Models\Question;
use App\Models\QuestionImage;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<QuestionImage>
 */
class QuestionImageFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'question_id' => Question::factory(),
            'key' => $this->faker->unique()->slug(2),
            'path' => 'images/questions/' . $this->faker->word . '.png',
            'alt' => $this->faker->sentence,
            'position' => 0,
        ];
    }
}
