<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        $questionIds = DB::table('questions')->where('version', 3)->pluck('id')->all();
        $respondentIds = DB::table('respondents')->where('version', 3)->pluck('id')->all();

        DB::transaction(function () use ($questionIds, $respondentIds) {
            $optionIds = $questionIds === []
                ? []
                : DB::table('questions_options')->whereIn('question_id', $questionIds)->pluck('id')->all();

            // 1) Respondenti verze 3 – nejdřív jejich odpovědi (FK respondent_id je
            //    RESTRICT), pak respondenti (respondent_situations i
            //    respondent_easter_eggs zmizí kaskádně).
            if ($respondentIds !== []) {
                DB::table('respondents_answers')->whereIn('respondent_id', $respondentIds)->delete();
                DB::table('respondents')->whereIn('id', $respondentIds)->delete();
            }

            // 2) Zbývající odpovědi navázané na otázky verze 3 (kdyby je dal i jiný
            //    než verze-3 respondent) – FK question_option_id je také RESTRICT.
            if ($optionIds !== []) {
                DB::table('respondents_answers')->whereIn('question_option_id', $optionIds)->delete();
            }

            // 3) Options otázek verze 3 (FK na questions je RESTRICT – musí pryč
            //    dřív než samotné otázky).
            if ($questionIds !== []) {
                DB::table('questions_options')->whereIn('question_id', $questionIds)->delete();

                // 4) Otázky verze 3 – kaskádně smažou situations (→ respondent_situations),
                //    řádky questions_images i vazby island_question.
                DB::table('questions')->whereIn('id', $questionIds)->delete();
            }
        });
    }

    public function down(): void
    {
    }
};
