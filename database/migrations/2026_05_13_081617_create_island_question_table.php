<?php

use App\Models\Island;
use App\Models\Question;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('island_question', function (Blueprint $table) {
            $table->foreignIdFor(Island::class)->constrained('islands')->cascadeOnDelete();
            $table->foreignIdFor(Question::class)->constrained('questions')->cascadeOnDelete();
            $table->primary(['island_id', 'question_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('island_question');
    }
};
