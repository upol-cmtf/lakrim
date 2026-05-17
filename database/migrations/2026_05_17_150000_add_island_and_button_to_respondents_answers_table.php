<?php

use App\Models\Island;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('respondents_answers', function (Blueprint $table): void {
            // kontext odpovědi z 3. verze hry – ostrov a tlačítko (u v1/v2 zůstává null)
            $table->foreignIdFor(Island::class)
                ->nullable()
                ->after('question_option_id')
                ->constrained('islands')
                ->nullOnDelete();
            $table->unsignedTinyInteger('button')->nullable()->after('island_id');
        });
    }

    public function down(): void
    {
        Schema::table('respondents_answers', function (Blueprint $table): void {
            $table->dropConstrainedForeignIdFor(Island::class);
            $table->dropColumn('button');
        });
    }
};
