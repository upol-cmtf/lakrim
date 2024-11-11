<?php

use App\Models\QuestionGroup;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration {
    public function up(): void
    {
        QuestionGroup::create([
            'name' => 'E-mail od banky',
        ]);

        QuestionGroup::create([
            'name' => 'Jsi to ty? ',
        ]);
        QuestionGroup::create([
            'name' => 'Vnuk v nesnázích',
        ]);

        QuestionGroup::create([
            'name' => 'Tajemství pro vaše zdraví',
        ]);

        QuestionGroup::create([
            'name' => 'Známá osobnost doporučuje',
        ]);

        QuestionGroup::create([
            'name' => 'Píše pošta',
        ]);

        QuestionGroup::create([
            'name' => 'Přispějte na dobrou věc',
        ]);

        QuestionGroup::create([
            'name' => 'Zmeškaný hovor',
        ]);

        QuestionGroup::create([
            'name' => 'Hoax',
        ]);
    }

    public function down(): void
    {
        QuestionGroup::all()->each->delete();
    }
};
