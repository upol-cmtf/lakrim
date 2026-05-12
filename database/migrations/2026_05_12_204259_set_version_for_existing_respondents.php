<?php

use App\Enums\Difficulty as DifficultyEnum;
use App\Enums\Version;
use App\Models\Respondent;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration {
    public function up(): void
    {
        Respondent::query()
            ->where('difficulty_id', DifficultyEnum::Easy->value)
            ->update(['version' => Version::One->value]);

        Respondent::query()
            ->where('difficulty_id', DifficultyEnum::Medium->value)
            ->update(['version' => Version::Two->value]);
    }

    public function down(): void
    {
    }
};
