<?php

use App\Models\Island;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration {
    public function up(): void
    {
        Island::query()
            ->where('image', 'fake_wealth.webp')
            ->update(['intro' => $this->intro()]);
    }

    public function down(): void
    {
        Island::query()
            ->where('image', 'fake_wealth.webp')
            ->update([
                'intro' =>
                    '<p>Vítejte na Ostrově falešného bohatství, kapitáne. Tento ostrov vás bude lákat na snadné zisky, zázračné investice a výhry, které jsou ve skutečnosti past.</p>'
                    . '<p>Vaším úkolem je rozpoznat podezřelé nabídky a neuvěřitelně výhodné obchody dřív, než přijdete o peníze.</p>'
                    . '<p>Pojďme společně rozsvítit tento ostrov a ukázat, že když něco zní příliš dobře, je to obvykle podvod.</p>',
            ]);
    }

    private function intro(): string
    {
        return
            '<p>Vítejte na Ostrově falešného bohatství, kapitáne. Tohle místo se tváří jako země výhodných nabídek, snadného výdělku a rychlého zisku. Právě v tom je ale jeho nebezpečí. Podvodníci zde lákají na výhodné nabídky, rychlý zisk nebo možnost pomoci dobré věci. Všechno může vypadat lákavě a jednoduše, právě proto je potřeba zbystřit.</p>'
            . '<p>Aby vás podvodníci dostali tam, kam chtějí, používají tyto nekalé postupy:</p>'
            . '<ul>'
            . '<li><strong>Vidina rychlého zisku:</strong> Budou vám slibovat snadné investice, rychlé zhodnocení peněz a výdělky bez práce. Mohou se tvářit jako odborníci, úspěšní investoři nebo známé osobnosti ve videu.</li>'
            . '<li><strong>Lákavé nákupy a falešní kupující:</strong> Budou nabízet zboží za podezřele nízké ceny nebo se naopak vydávat za zájemce o vaše zboží. Chtějí vás přimět kliknout na odkaz, vyplnit údaje z karty nebo poslat peníze předem.</li>'
            . '<li><strong>Snadný přivýdělek bez námahy:</strong> Mohou vám nabídnout, že přes váš účet jen „na chvíli" projdou peníze a vy za to dostanete odměnu. Ve skutečnosti se vás snaží zatáhnout do cizího podvodu.</li>'
            . '<li><strong>Falešná dobročinnost:</strong> Někdy budou hrát na soucit a prosit o peníze pro nemocné dítě, opuštěné zvíře nebo lidi v nouzi. I falešná sbírka může vypadat velmi dojemně a důvěryhodně.</li>'
            . '</ul>'
            . '<p>Pamatujte si jedno zlaté pravidlo: Když něco vypadá až příliš výhodně, slibuje to rychlý zisk nebo na vás někdo tlačí, abyste jednali hned, je potřeba zbystřit. Skutečný obchod, bezpečná investice ani poctivá sbírka se nebojí času, ověření ani vašich otázek. Jakmile vás někdo tlačí k rychlé platbě, vyplnění údajů nebo převodu peněz, je to velmi silný varovný signál.</p>'
            . '<p>Na cestě k majáku Ostrova falešného bohatství vás čeká pět zkoušek. Vaším úkolem bude nenechat se oslnit vidinou zisku ani výhodné koupě. Pokud vás něco láká až příliš, zastavte se. Ověřte si nabídku, obchod nebo člověka, se kterým jednáte.</p>'
            . '<p>Jste připraveni rozpoznat falešné zlato od skutečné hodnoty a rozsvítit tento ostrov naplno? Pojďme na to.</p>';
    }
};
