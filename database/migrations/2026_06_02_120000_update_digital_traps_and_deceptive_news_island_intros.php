<?php

use App\Models\Island;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration {
    public function up(): void
    {
        Island::query()
            ->where('image', 'digital_traps.webp')
            ->update(['intro' => $this->digitalTrapsIntro()]);

        Island::query()
            ->where('image', 'deceptive_news.webp')
            ->update(['intro' => $this->deceptiveNewsIntro()]);
    }

    public function down(): void
    {
        Island::query()
            ->where('image', 'digital_traps.webp')
            ->update([
                'intro' =>
                    '<p>Vítejte na Ostrově digitálních pastí, kapitáne. Na tomto ostrově na vás číhají nástrahy ukryté v e-mailech, odkazech a podezřelých přílohách.</p>'
                    . '<p>Vaším úkolem je naučit se rozpoznat, kdy se vás někdo snaží podvést pomocí falešné stránky, podvodného odkazu nebo škodlivého souboru.</p>'
                    . '<p>Pojďme společně rozsvítit tento ostrov a odhalit všechny jeho pasti.</p>',
            ]);

        Island::query()
            ->where('image', 'deceptive_news.webp')
            ->update([
                'intro' =>
                    '<p>Vítejte na Ostrově klamavých zpráv, kapitáne. Tady se setkáte s dezinformacemi, manipulativními titulky a obsahem, který má za cíl vás zmást.</p>'
                    . '<p>Naučíte se ověřovat zdroje, rozpoznat sdílený hoax a nenechat se nachytat na senzaci, která ve skutečnosti není pravdivá.</p>'
                    . '<p>Pojďme společně rozsvítit tento ostrov a oddělit pravdu od lži.</p>',
            ]);
    }

    private function digitalTrapsIntro(): string
    {
        return
            '<p><strong>Vítejte na Ostrově digitálních pastí, kapitáne.</strong> Toto místo je protkané neviditelnými sítěmi, do kterých se podvodníci snaží lapit vás i váš telefon či počítač. Na rozdíl od jiných ostrovů zde útočníci neútočí jen na vaše emoce, ale přímo na zabezpečení vašich osobních dat a peněz.</p>'
            . '<p>Aby vás podvodníci dostali tam, kam chtějí, používají třeba toto:</p>'
            . '<ul>'
            . '<li><strong>Falešná varování před viry:</strong> Budou vám e-maily nebo SMS zprávy s varováním, že je vaše zařízení zavirované, a budou vás nutit stáhnout si „bezpečnostní“ aplikaci. Ve skutečnosti je tato aplikace virem, který vám může ukrást hesla a přístupy do banky.</li>'
            . '<li><strong>Phishingové útoky (falešné úřady a banky):</strong> Budou vám posílat SMS nebo e-maily, které vypadají jako od České pošty, ČSSZ nebo banky, s naléhavou výzvou k zaplacení malého poplatku nebo „ověření totožnosti“. Cílem je vylákat z vás údaje z platební karty nebo přístupy k vašemu bankovnictví.</li>'
            . '<li><strong>Zneužití Bankovní identity:</strong> Pod záminkou „aktivace příspěvků“ nebo „ověření totožnosti“ vás budou nutit přihlásit se přes Bankovní identitu na falešné stránky. Tímto kliknutím jim prakticky odevzdáte přístupy do svého bankovního účtu.</li>'
            . '<li><strong>Škodlivé odkazy v SMS:</strong> Budou vám posílat zprávy s podezřelými odkazy, které vypadají jako oznámení o balíčku nebo nedoplatku.</li>'
            . '</ul>'
            . '<p><strong>Pamatujte si jedno zlaté pravidlo:</strong> Státní úřady ani banky vám nikdy neposílají odkazy na přihlášení či platby v běžných SMS zprávách a nikdy vás nenutí stahovat neznámé aplikace. Oficiální výzvy chodí výhradně do vaší Datové schránky, nebo se zobrazí přímo po vašem vlastním přihlášení do oficiálního portálu či aplikace banky.</p>'
            . '<p>Na cestě k majáku Ostrova digitálních pastí vás čeká pět zkoušek. Vaším úkolem je nenechat se zlákat zvědavostí a neklikat na nic, co nepůsobí na sto procent bezpečně.</p>'
            . '<p>Jste připraveni prohlédnout nástrahy digitálního světa a rozsvítit tento ostrov naplno? Pojďme na to.</p>';
    }

    private function deceptiveNewsIntro(): string
    {
        return
            '<p><strong>Vítejte na Ostrově klamavých zpráv, kapitáne.</strong> Tohle místo je zahaleno hustou mlhou polopravd a lží. Podvodníci zde většinou nechtějí okamžitě vaše hesla k účtu, ale útočí na vaši mysl. Snaží se vás vyděsit, rozzlobit nebo vám prodat falešnou naději, abyste přestali kriticky přemýšlet.</p>'
            . '<p>Aby vás podvodníci dostali tam, kam chtějí, používají třeba toto:</p>'
            . '<ul>'
            . '<li><strong>Zázračné léky a skryté reklamy:</strong> Budou vám podstrkovat články o „revolučních“ přípravcích na zdraví, které vypadají jako běžné zprávy. Jejich skutečným cílem je ale donutit vás zavolat a koupit si předražený a neúčinný nesmysl.</li>'
            . '<li><strong>Šíření strachu a paniky:</strong> Budou vám posílat řetězové e-maily plné šokujících zpráv (například o rušení důchodů nebo tajných poplatcích) s naléhavou výzvou „sdílejte to všem známým“. Chtějí z vás udělat nástroj k šíření paniky.</li>'
            . '<li><strong>Falešné zpravodajství:</strong> Budou vytvářet weby, které se logem a vzhledem tváří jako známá česká média, ale jejich skutečná internetová adresa bude zkomolená. Titulky budou vždy útočit na vaše emoce a tvrdit, že „televize o tom mlčí“.</li>'
            . '<li><strong>Smyšlené katastrofy a umělá inteligence:</strong> Budou vás děsit fotografiemi prázdných regálů v obchodech nebo hořících budov ve vašem okolí. Dnes je díky umělé inteligenci (AI) nesmírně snadné takový obrázek vyrobit za pár vteřin, aby vyvolal chaos a přitáhl pozornost.</li>'
            . '</ul>'
            . '<p>Pamatujte si jedno zlaté pravidlo: <strong>Zpráva, která vás nutí k okamžité reakci, vyvolává ve vás silné emoce (strach či vztek) a tvrdí, že „vláda a média všechno tají“, je téměř vždy lživá.</strong> Než takové šokující novince uvěříte, vždy se na chvíli zastavte a informaci si ověřte ve spolehlivých, oficiálních zdrojích.</p>'
            . '<p>Na cestě k majáku Ostrova klamavých zpráv vás čeká pět zkoušek.</p>'
            . '<p>Vaším úkolem bude zachovat si chladnou hlavu a nenechat se strhnout vlnou negativních emocí.</p>'
            . '<p>Jste připraveni rozehnat mlhu lží a rozsvítit tento ostrov naplno? Pojďme na to.</p>';
    }
};
