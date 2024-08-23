<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('questions_groups', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->timestamps();
        });

        Schema::create('questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('question_group_id')->constrained('questions_groups');
            $table->integer('difficulty');
            $table->timestamps();

            $table->index('difficulty');
        });

        Schema::create('questions_options', function (Blueprint $table) {
            $table->id();
            $table->foreignId('question_id')->constrained('questions');
            $table->string('name');
            $table->text('description');
            $table->float('weight');
            $table->string('evaluation');
            $table->timestamps();
        });

        Schema::create('respondents', function (Blueprint $table) {
            $table->id();
            $table->text('cookie');
            $table->string('ip')->nullable();
            $table->timestamps();
        });

        Schema::create('respondents_answers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('respondent_id')->constrained('respondents');
            $table->foreignId('question_option_id')->constrained('questions_options');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('questions');
        Schema::dropIfExists('questions_groups');
        Schema::dropIfExists('questions_answers');
    }
};
