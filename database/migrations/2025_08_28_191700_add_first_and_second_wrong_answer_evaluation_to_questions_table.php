<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('questions', function (Blueprint $table) {
            $table->text('first_wrong_answer_evaluation')->after('description');
            $table->text('second_wrong_answer_evaluation')->after('first_wrong_answer_evaluation');
        });
    }

    public function down(): void
    {
        Schema::table('questions', function (Blueprint $table) {
            $table->dropColumn('first_wrong_answer_evaluation', 'second_wrong_answer_evaluation');
        });
    }
};
