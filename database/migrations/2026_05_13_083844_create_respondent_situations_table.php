<?php

use App\Models\Respondent;
use App\Models\Situation;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('respondent_situations', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Respondent::class)->constrained('respondents')->cascadeOnDelete();
            $table->foreignIdFor(Situation::class)->constrained('situations')->cascadeOnDelete();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->unique(['respondent_id', 'situation_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('respondent_situations');
    }
};
