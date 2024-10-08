<?php

namespace Database\Seeders;

use App\Enums\Difficulty as DifficultyEnum;
use App\Models\Difficulty;
use Illuminate\Database\Seeder;

class DifficultySeeder extends Seeder
{
    public function run(): void
    {
        Difficulty::factory()->createMany([
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
