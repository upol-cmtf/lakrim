<?php

use App\Models\Island;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration {
    public function up(): void
    {
        Island::query()->each(function (Island $island): void {
            $island->update([
                'image' => pathinfo($island->image, PATHINFO_FILENAME) . '.webp',
            ]);
        });
    }

    public function down(): void
    {
        Island::query()->each(function (Island $island): void {
            $island->update([
                'image' => pathinfo($island->image, PATHINFO_FILENAME) . '.png',
            ]);
        });
    }
};
