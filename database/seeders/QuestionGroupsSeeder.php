<?php

namespace Database\Seeders;

use App\Enums\Difficulty as DifficultyEnum;
use App\Models\Difficulty;
use App\Models\QuestionGroup;
use Illuminate\Database\Seeder;

class QuestionGroupsSeeder extends Seeder
{
    public function run(): void
    {
        QuestionGroup::factory()->createMany([
            [
                'id' => DifficultyEnum::Easy->value,
                'name' => 'Lehké',
            ],
            [
                'id' => DifficultyEnum::Medium->value,
                'name' => 'Střední',
            ],
            [
                'id' => DifficultyEnum::Hard->value,
                'name' => 'Těžké',
            ],
        ]);
    }
}
