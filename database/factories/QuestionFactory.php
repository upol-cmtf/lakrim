<?php
namespace Database\Factories;

use App\Enums\Difficulty;
use App\Models\Question;
use App\Models\QuestionGroup;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Question>
 */
class QuestionFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'first_wrong_answer_evaluation' => $this->faker->text,
            'second_wrong_answer_evaluation' => $this->faker->text,
            'question_group_id' => QuestionGroup::factory()->createOneQuietly()->id,
            'perex' => $this->faker->sentence,
            'description' => $this->faker->text,
            'difficulty_id' => Difficulty::Easy->value,
        ];
    }
}
