<?php

use App\Models\Age;
use App\Models\Difficulty;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('respondents', function (Blueprint $table) {
            $table->foreignIdFor(Difficulty::class)->after('id')->constrained('difficulty');
            $table->foreignIdFor(Age::class)->nullable()->after('difficulty_id')->constrained('ages');
            $table->string('token')->index()->after('ip');
            $table->string('sex', length: 1)->after('token')->nullable();

            $table->dropColumn('cookie');
        });
    }

    public function down(): void
    {
        Schema::table('respondents', function (Blueprint $table) {
            $table->text('cookie')->after('ip');

            $table->dropColumn('token');
            $table->dropColumn('sex');
            $table->dropForeignIdFor(Age::class);
            $table->dropColumn('age_id');
            $table->dropForeignIdFor(Difficulty::class);
            $table->dropColumn('difficulty_id');
        });
    }
};
