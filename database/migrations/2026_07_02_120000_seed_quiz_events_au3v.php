<?php

use App\Models\QuizEvent;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Naplní 20 událostí kvízu pro školu AU3V.
 *
 * name  = vždy "AU3V"
 * hash  = maska "au3v-XXXX" (pomlčka + 2místné pořadí pro unikátnost + 2 náhodné znaky),
 *         hash je unikátní a používá se ve veřejných URL ({quizEvent:hash}).
 */
return new class extends Migration {
    private const NAME = 'AU3V';

    public function up(): void
    {
        DB::transaction(function (): void {
            foreach (range(1, 20) as $i) {
                QuizEvent::forceCreate([
                    'name' => self::NAME,
                    'hash' => sprintf('au3v-%02d%s', $i, Str::lower(Str::random(2))),
                ]);
            }
        });
    }

    public function down(): void
    {
        QuizEvent::query()
            ->where('name', self::NAME)
            ->where('hash', 'like', 'au3v-%')
            ->delete();
    }
};
