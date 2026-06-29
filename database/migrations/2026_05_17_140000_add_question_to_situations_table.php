<?php

use App\Models\Question;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('situations', function (Blueprint $table): void {
            // FK na island_id se dosud opíral o unique index – nahradíme ho samostatným
            $table->index('island_id');
            // víc situací (= baterie otázek) na jedno tlačítko
            $table->dropUnique(['island_id', 'position']);
            $table->foreignIdFor(Question::class)
                ->after('island_id')
                ->constrained('questions')
                ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('situations', function (Blueprint $table): void {
            $table->dropConstrainedForeignIdFor(Question::class);
            $table->unique(['island_id', 'position']);
            $table->dropIndex(['island_id']);
        });
    }
};
