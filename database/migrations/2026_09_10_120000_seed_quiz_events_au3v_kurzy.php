<?php

use App\Models\QuizEvent;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Naplní události kvízu pro kurzy AU3V podle dodaných kódů kurzů.
 *
 * name  = "Studenti 2026/2027"
 * hash  = kód kurzu (C25277, …); hash je unikátní a používá se ve veřejných
 *         URL ({quizEvent:hash}), např. /ostrov/C25277.
 *
 * Migrace je idempotentní: událost s již existujícím hashem znovu nevkládá.
 */
return new class extends Migration {
    private const NAME = 'Studenti 2026/2027';

    private const HASHES = [
        'C25277', 'C25664', 'C25278', 'C25279', 'C25281',
        'C25282', 'C25283', 'C25284', 'C25285', 'C25286',
        'C25287', 'C25288', 'C25665', 'C25289', 'C25666',
        'C25293', 'C25294', 'C25295', 'C25296', 'C25290',
        'C25297', 'C25300', 'C25667', 'C25668', 'C25301',
    ];

    public function up(): void
    {
        DB::transaction(function (): void {
            foreach (self::HASHES as $hash) {
                QuizEvent::query()->firstOrCreate(
                    ['hash' => $hash],
                    ['name' => self::NAME],
                );
            }
        });
    }

    public function down(): void
    {
        QuizEvent::query()
            ->where('name', self::NAME)
            ->whereIn('hash', self::HASHES)
            ->delete();
    }
};
