<?php

use App\Models\Island;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('situations', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Island::class)->constrained('islands')->cascadeOnDelete();
            $table->smallInteger('position');
            $table->string('title')->nullable();
            $table->timestamps();

            $table->unique(['island_id', 'position']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('situations');
    }
};
