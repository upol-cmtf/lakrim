<?php

use App\Models\Question;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('questions_images', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Question::class)->constrained('questions')->cascadeOnDelete();
            $table->string('key');
            $table->string('path');
            $table->string('alt')->nullable();
            $table->unsignedInteger('position')->default(0);
            $table->timestamps();

            $table->unique(['question_id', 'key']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('questions_images');
    }
};
