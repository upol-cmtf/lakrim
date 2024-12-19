<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('quiz_events', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('hash')->unique();
            $table->timestamps();
        });

        Schema::table('respondents', function (Blueprint $table) {
            $table->foreignId('quiz_event_id')->after('age_id')->nullable()->constrained('quiz_events')
                ->nullOnDelete()
                ->cascadeOnUpdate();
        });
    }

    public function down(): void
    {
        Schema::table('respondents', function (Blueprint $table) {
            $table->dropForeign(['quiz_event_id']);
            $table->dropColumn('quiz_event_id');
        });
        Schema::dropIfExists('quiz_events');
    }
};
