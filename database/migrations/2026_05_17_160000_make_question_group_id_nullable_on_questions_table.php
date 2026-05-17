<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // otázky 3. verze (ostrovní hra) nepatří do žádné skupiny otázek
        Schema::table('questions', function (Blueprint $table): void {
            $table->dropForeign(['question_group_id']);
            $table->unsignedBigInteger('question_group_id')->nullable()->change();
            $table->foreign('question_group_id')->references('id')->on('questions_groups');
        });
    }

    public function down(): void
    {
        Schema::table('questions', function (Blueprint $table): void {
            $table->dropForeign(['question_group_id']);
            $table->unsignedBigInteger('question_group_id')->nullable(false)->change();
            $table->foreign('question_group_id')->references('id')->on('questions_groups');
        });
    }
};
