<?php

use App\Models\EasterEgg;
use App\Models\Respondent;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('respondent_easter_eggs', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Respondent::class)->constrained('respondents')->cascadeOnDelete();
            $table->foreignIdFor(EasterEgg::class)->constrained('easter_eggs')->cascadeOnDelete();
            $table->unsignedInteger('seconds')->default(0);
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->unique(['respondent_id', 'easter_egg_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('respondent_easter_eggs');
    }
};
