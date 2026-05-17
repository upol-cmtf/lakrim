<?php

use App\Models\Island;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('islands', function (Blueprint $table) {
            $table->string('guide')->nullable()->after('image');
            $table->json('settings')->nullable()->after('guide');
        });

        Island::query()->each(function (Island $island): void {
            $island->update([
                'guide' => pathinfo($island->image, PATHINFO_FILENAME) . '_guide.webp',
            ]);
        });
    }

    public function down(): void
    {
        Schema::table('islands', function (Blueprint $table) {
            $table->dropColumn(['guide', 'settings']);
        });
    }
};
