<?php

use App\Models\Island;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('islands', function (Blueprint $table) {
            $table->text('intro')->nullable()->after('guide');
        });

        $intros = [
            'exploited_emotions.webp' =>
                '<p>Vítejte na Ostrově zneužitých citů, kapitáne. Tohle místo vypadá na první pohled vlídně, ale nenechte se zmást. Podvodníci zde neútočí jen na vaše zařízení, ale především na vaše srdce, vaši lásku k rodině a vaši ochotu pomáhat.</p>'
                . '<p>Aby vás podvodníci dostali tam, kam chtějí, používají tyto nekalé postupy:</p>'
                . '<ul>'
                . '<li><strong>Zneužití strachu a emocí:</strong> Budou vám tvrdit, že váš vnuk měl nehodu nebo že je váš telefon v ohrožení virem. Chtějí vás vyděsit, abyste je v panice poslechli.</li>'
                . '<li><strong>Hra na city a osamělost:</strong> Budou se vydávat za sympatické lidi v nouzi nebo osamělé hrdiny, kteří potřebují právě vaši pomoc. Budují si u vás důvěru jen proto, aby ji později zpeněžili.</li>'
                . '<li><strong>Falešná autorita a nátlak:</strong> Někdy vystupují jako policisté nebo bankéři. Budou na vás spěchat a nutit vás k tajnostem před rodinou, abyste se nemohli s nikým poradit.</li>'
                . '</ul>'
                . '<p>Pamatujte si jedno zlaté pravidlo: Skutečná policie, banka nebo váš blízký po vás nikdy nebudou chtít, abyste své peníze narychlo někam posílali nebo si do telefonu instalovali neznámé programy. Jakmile na vás někdo v telefonu tlačí, zakazuje vám o tom mluvit s rodinou nebo vás straší virem, je to téměř jistě podvodník.</p>'
                . '<p>Na cestě k majáku Ostrova zneužitých citů vás čeká pět zkoušek. Vaším úkolem je nenechat se ovládnout emocemi. Pokud ucítíte tlak, zastavte se. Ověřte si vše u svých blízkých nebo přímo v bance.</p>'
                . '<p>Jste připraveni prokouknout jejich pasti a rozsvítit tento ostrov naplno? Pojďme na to.</p>',
            'digital_traps.webp' =>
                '<p>Vítejte na Ostrově digitálních pastí, kapitáne. Na tomto ostrově na vás číhají nástrahy ukryté v e-mailech, odkazech a podezřelých přílohách.</p>'
                . '<p>Vaším úkolem je naučit se rozpoznat, kdy se vás někdo snaží podvést pomocí falešné stránky, podvodného odkazu nebo škodlivého souboru.</p>'
                . '<p>Pojďme společně rozsvítit tento ostrov a odhalit všechny jeho pasti.</p>',
            'deceptive_news.webp' =>
                '<p>Vítejte na Ostrově klamavých zpráv, kapitáne. Tady se setkáte s dezinformacemi, manipulativními titulky a obsahem, který má za cíl vás zmást.</p>'
                . '<p>Naučíte se ověřovat zdroje, rozpoznat sdílený hoax a nenechat se nachytat na senzaci, která ve skutečnosti není pravdivá.</p>'
                . '<p>Pojďme společně rozsvítit tento ostrov a oddělit pravdu od lži.</p>',
            'fake_wealth.webp' =>
                '<p>Vítejte na Ostrově falešného bohatství, kapitáne. Tento ostrov vás bude lákat na snadné zisky, zázračné investice a výhry, které jsou ve skutečnosti past.</p>'
                . '<p>Vaším úkolem je rozpoznat podezřelé nabídky a neuvěřitelně výhodné obchody dřív, než přijdete o peníze.</p>'
                . '<p>Pojďme společně rozsvítit tento ostrov a ukázat, že když něco zní příliš dobře, je to obvykle podvod.</p>',
        ];

        Island::query()->each(function (Island $island) use ($intros): void {
            if (isset($intros[$island->image])) {
                $island->update(['intro' => $intros[$island->image]]);
            }
        });
    }

    public function down(): void
    {
        Schema::table('islands', function (Blueprint $table) {
            $table->dropColumn('intro');
        });
    }
};
