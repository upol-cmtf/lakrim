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

        Schema::create('difficulty', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->integer('min_questions')->default(1);
            $table->integer('max_questions')->default(10);
            $table->integer('shuffle_questions')->default(true);
        });

        Schema::create('questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('question_group_id')->constrained('questions_groups');
            $table->foreignId('difficulty_id')->constrained('difficulty');
            $table->text('perex');
            $table->text('description');
            $table->timestamps();
        });

        Schema::create('questions_options', function (Blueprint $table) {
            $table->id();
            $table->foreignId('question_id')->constrained('questions');
            $table->string('name');
            $table->text('description');
            $table->float('weight');
            $table->text('evaluation');
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
            $table->integer('seconds');
            $table->float('weight');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('questions_options');
        Schema::dropIfExists('questions');
        Schema::dropIfExists('questions_groups');
        Schema::dropIfExists('respondents_answers');
        Schema::dropIfExists('respondents');
        Schema::dropIfExists('difficulty');
    }
};
