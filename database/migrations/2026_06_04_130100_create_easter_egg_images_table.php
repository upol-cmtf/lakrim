<?php

use App\Models\EasterEgg;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('easter_egg_images', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(EasterEgg::class)->constrained('easter_eggs')->cascadeOnDelete();
            $table->string('key');
            $table->string('path');
            $table->string('alt')->nullable();
            $table->unsignedInteger('position')->default(0);
            $table->timestamps();

            $table->unique(['easter_egg_id', 'key']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('easter_egg_images');
    }
};
