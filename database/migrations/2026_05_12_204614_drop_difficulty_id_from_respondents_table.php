<?php

use App\Models\Difficulty;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('respondents', function (Blueprint $table) {
            $table->dropForeign(['difficulty_id']);
            $table->dropColumn('difficulty_id');
        });
    }

    public function down(): void
    {
        Schema::table('respondents', function (Blueprint $table) {
            $table->foreignIdFor(Difficulty::class)->after('id')->constrained('difficulty');
        });
    }
};
