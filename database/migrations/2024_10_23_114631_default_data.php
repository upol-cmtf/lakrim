<?php

use App\Enums\Difficulty as DifficultyEnum;
use App\Models\Difficulty;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Difficulty::create([
            'id' => DifficultyEnum::Easy->value,
            'name' => 'Lehké',
        ]);

        Difficulty::create([
            'id' => DifficultyEnum::Medium->value,
            'name' => 'Střední',
        ]);

        Difficulty::create([
            'id' => DifficultyEnum::Hard->value,
            'name' => 'Těžké',
        ]);
    }

    public function down(): void
    {
        Difficulty::all()->each->delete();
    }
};
