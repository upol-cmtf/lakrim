<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('difficulty', function (Blueprint $table) {
            $table->boolean('show_evaluations_for_other_options')->default(1);
        });
    }

    public function down(): void
    {
        Schema::table('difficulty', function (Blueprint $table) {
            $table->dropColumn('show_evaluations_for_other_options');
        });
    }
};
