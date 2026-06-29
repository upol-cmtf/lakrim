<?php

use App\Enums\Version;
use App\Models\Island;
use App\Models\Question;
use App\Models\QuestionImage;
use App\Models\QuestionOption;
use App\Models\Situation;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Naplní Ostrov falešného bohatství – 5 herních kamenů (tlačítek),
 * každý se třemi situacemi o obtížnosti 1–3 (15 otázek celkem).
 * Otázky jsou verze 3, bez skupiny.
 */
return new class extends Migration {
    public function up(): void
    {
        $island = Island::query()->where('image', 'fake_wealth.webp')->first();

        if (!$island instanceof Island) {
            return;
        }

        DB::transaction(function () use ($island): void {
            foreach ($this->situations() as $data) {
                $question = Question::forceCreate([
                    'version' => Version::Three,
                    'question_group_id' => null,
                    'difficulty_id' => $data['difficulty'],
                    'perex' => $data['perex'],
                    'description' => $data['description'],
                    'first_wrong_answer_evaluation' => '',
                    'second_wrong_answer_evaluation' => '',
                ]);

                foreach ($data['options'] as $option) {
                    QuestionOption::forceCreate([
                        'question_id' => $question->id,
                        'name' => $option['name'],
                        'description' => '',
                        'weight' => $option['right'] ? 1.0 : 0.0,
                        'right' => $option['right'],
                        'evaluation' => $option['evaluation'],
                        'evaluation_title' => '',
                    ]);
                }

                if (isset($data['image'])) {
                    QuestionImage::forceCreate([
                        'question_id' => $question->id,
                        'key' => $data['image']['key'],
                        'path' => $data['image']['path'],
                        'alt' => $data['image']['alt'],
                        'position' => 0,
                    ]);
                }

                Situation::forceCreate([
                    'island_id' => $island->id,
                    'question_id' => $question->id,
                    'position' => $data['button'],
                    'title' => null,
                    'safety_card' => $data['safety_card'],
                ]);
            }
        });
    }

    public function down(): void
    {
        $island = Island::query()->where('image', 'fake_wealth.webp')->first();

        if (!$island instanceof Island) {
            return;
        }

        $questionIds = Situation::query()
            ->where('island_id', $island->id)
            ->pluck('question_id')
            ->all();

        QuestionOption::query()->whereIn('question_id', $questionIds)->delete();
        Question::query()->whereIn('id', $questionIds)->delete();
    }

    /**
     * @return list<array{
     *     button: int,
     *     difficulty: int,
     *     perex: string,
     *     description: string,
     *     safety_card: string|null,
     *     image?: array{key: string, path: string, alt: string},
     *     options: list<array{name: string, right: bool, evaluation: string}>,
     * }>
     */
    private function situations(): array
    {
        return [

            // ===== PRVNÍ herní kámen =====
            [
                'button' => 1,
                'difficulty' => 1,
                'perex' => 'Reklama na Facebooku – robotický vysavač',
                'description' => '[[image:situace]]<p>Reklama na Facebooku:</p><p>„LIKVIDACE SKLADU - POSLEDNÍ KUSY!<br>Robotický vysavač Philips SmartClean<br>Původní cena: 9 990 Kč<br>Dnes pouze: 1 290 Kč<br>Zbývají poslední 2 kusy.“</p><p>Pod reklamou je tlačítko „Koupit nyní“, které vás přesměruje na stránku obchodu.</p>',
                'safety_card' => null,
                'image' => [
                    'key' => 'situace',
                    'path' => 'images/situations/84fa8a9d-ec0d-48d8-bd25-ab6651e578b3.webp',
                    'alt' => 'Reklama na Facebooku – robotický vysavač',
                ],
                'options' => [
                    [
                        'name' => 'Tato sleva je super, produkt objednám hned.',
                        'right' => false,
                        'evaluation' => 'Zadržte, kapitáne!<br><br>Tímto rozhodnutím byste mohli poslat peníze na účet, o kterém nevíte, komu patří. Zboží by s velkou pravděpodobností vůbec nedorazilo.<br><br>Pojďme si tenhle „výhodný nákup“ rozebrat. Je pravda, že i běžné e-shopy používají slevy a časově omezené nabídky, ale tady se sešly tři věci, které dohromady nedávají smysl.<br><br>Neznámý zdroj:<br>Tato nabídka na vás vyskočila na Facebooku. Reklamu si tam může zaplatit kdokoliv. To, že ji vidíte, neznamená, že víte, kdo ji skutečně vytvořil.<br><br>Absurdní sleva:<br>Sleva z téměř 10 000 Kč na 1 290 Kč je velmi nepravděpodobná. Má ve vás vyvolat pocit, že jde o výjimečnou příležitost.<br><br>Umělý nedostatek:<br>Nápis „poslední kusy“ má vytvořit tlak, abyste přestali přemýšlet a rozhodli se rychle.<br><br>Teď si to zhodnoťme:<br>Když nevíte, kdo nabídku vytvořil, a zároveň na vás tlačí rychlé rozhodnutí, je to situace, kterou je potřeba zastavit.<br><br>Dobrá strategie je nabídku nejprve prověřit přes nezávislé recenze (např. Heureka). Pokud tam prodejce chybí nebo má špatné hodnocení, ruce pryč. Pro jistotu se můžete podívat i na seznam rizikových e-shopů na stránkách České obchodní inspekce nebo na portál dTest.<br><br>Zkuste to znovu.',
                    ],
                    [
                        'name' => 'Vyhledám si produkt i jinde na internetu.',
                        'right' => true,
                        'evaluation' => 'Skvělá práce!<br><br>Nenechali jste se strhnout lákavou reklamou a zachoval jste chladnou hlavu.<br><br><strong>Proč je to ten nejlepší krok?</strong><br><br><strong>Získáte nadhled:</strong> Když si produkt vyhledáte sami (třeba na Heurece nebo Zboží.cz), ihned uvidíte jeho reálnou cenu všude na trhu.<br><br><strong>Odhalíte past:</strong> Rychle zjistíte, že takto obří slevu nikdo jiný nenabízí. To je jasný důkaz, že reklama na Facebooku byl podvod.<br><br><strong>Nezávislost:</strong> Nenecháte se dotlačit tam, kam chce podvodník, ale jdete vlastní cestou.<br><br><strong>Hlavní pravidlo:</strong> Nejlepší obranou je ověřit si nabídku mimo samotnou reklamu.',
                    ],
                    [
                        'name' => 'Podívám se na stránku',
                        'right' => false,
                        'evaluation' => 'V pořádku, kapitáne – samotným pohledem se ještě nic nestalo a vaše peníze jsou zatím v bezpečí. Teď ale buďte ve střehu.<br><br><strong>Když už na stránce jste, okamžitě zkontrolujte 3 věci:</strong><br><br><strong>Adresa webu (nahoře v prohlížeči):</strong> Je tam správný název značky, nebo podivná zkomolenina?<br><br><strong>Kontakty:</strong> Má e-shop jasné sídlo firmy a IČO? Samotný formulář „Napište nám“ nestačí.<br><br><strong>Umělý spěch:</strong> Tiká na vás odpočet času, že sleva za pár minut končí?<br><br><strong>Hlavní pravidlo:</strong> Prohlížet si web klidně můžete. Ale <strong>nikdy</strong> tam nezadávejte své jméno, adresu ani číslo karty, dokud si nejste stoprocentně jistí, komu patří.<br><br>Zkusme to raději znovu a bezpečněji.',
                    ],
                ],
            ],
            [
                'button' => 1,
                'difficulty' => 2,
                'perex' => 'Reklama na Facebooku – fitness náramek',
                'description' => '[[image:situace]]<p>Chcete si koupit robotický vysavač.</p><p>Do vyhledávání zadáte: „Philips SmartClean sleva“.<br>Mezi prvními výsledky se objeví obchod <strong>ALZAA Elektro</strong>.</p><p>Název připomíná známý obchod Alza, stránka má logo, fotky výrobku a působí jako běžný e-shop.</p><p>U produktu svítí nabídka:<br>„Původní cena: 9 990 Kč, dnes pouze: 1 390 Kč“</p><p>Pod cenou:<br>„Mimořádná akce - doprodej skladu“, „Zbývají poslední kusy“</p><p>Na stránce ale není jasné, kdo obchod provozuje - chybí adresa i telefon.<br>Obchod nabízí jen platbu převodem předem.</p>',
                'safety_card' => null,
                'image' => [
                    'key' => 'situace',
                    'path' => 'images/situations/3d60a7e4-bae2-4a98-bf64-d616232adbaf.webp',
                    'alt' => 'Reklama na Facebooku – fitness náramek',
                ],
                'options' => [
                    [
                        'name' => 'Sleva je super, objednávku zaplatím převodem.',
                        'right' => false,
                        'evaluation' => 'Zadržte, kapitáne!<br><br>Tímto rozhodnutím byste poslali peníze na účet, o kterém nevíte, komu patří. Zboží by s velkou pravděpodobností vůbec nedorazilo a peníze by pravděpodobně nebylo možné získat zpět.<br><br>Pojďme si tuhle situaci rozebrat. Na první pohled působí obchod důvěryhodně - má název podobný známému e-shopu, logo i běžný vzhled stránky.<br><br><strong>Napodobení důvěry</strong>: Název „ALZAA Elektro“ je záměrně podobný známé značce. Má ve vás vyvolat pocit, že nakupujete u někoho, koho už znáte.<br><br><strong>Výhodná cena a spěch: </strong>Nízká cena a nápisy o doprodeji vás tlačí k rychlému rozhodnutí. Máte pocit, že by byla škoda nabídku nevyužít.<br><br><strong>Nejasná identita prodejce</strong>: Na stránce není jasné, kdo obchod skutečně provozuje. Nemáte možnost si ho snadno ověřit.<br><br><strong>Riziková platba</strong>: Obchod nabízí pouze převod předem. Peníze posíláte přímo na účet a pokud nevíte, komu patří, nemáte způsob, jak je získat zpět. Na rozdíl od jiných způsobů platby zde nemáte žádnou ochranu.<br><br><strong>Teď si to zhodnoťme</strong>: když se spojí podobný název, výrazná sleva, tlak na rychlé rozhodnutí a nejasné informace o prodejci, je to situace, kterou je potřeba zastavit.<br><br>Dobrá strategie je obchod ověřit mimo jeho stránku - například podle recenzí (Heureka) nebo varování (Česká obchodní inspekce, dTest).<br><br>Zkuste to znovu.',
                    ],
                    [
                        'name' => 'Produkt si jen prohlédnu, zatím neobjednávám.',
                        'right' => true,
                        'evaluation' => 'V pořádku, kapitáne – pouhým prohlížením stránky o žádné peníze nepřijdete. Je skvělé, že nespěcháte s objednávkou. Když už se ale díváte, tahle stránka přímo křičí, že je to past.<br><br><strong>Všimněte si tří zásadních varovných znamení:</strong><br><br><strong>Falešné jméno:</strong> Obchod se jmenuje <strong>ALZAA</strong> Elektro. Podvodníci schválně zkopírovali vzhled slavné Alzy a sází na to, že si jednoho písmenka navíc nevšimnete.<br><br><strong>Úplná anonymita:</strong> Chybí adresa, telefon i IČO. Nemáte vůbec tušení, komu posíláte peníze a kde případně zboží reklamovat.<br><br><strong>Pouze platba předem:</strong> Stránka vás nutí poslat peníze na účet dřív, než cokoli uvidíte. To je pro podvodníky nejjednodušší způsob, jak vás okrást.<br><br><strong>Hlavní pravidlo:</strong> Dívat se můžete, ale jakmile e-shop schovává své kontakty a vyžaduje pouze platbu předem, okamžitě pryč.',
                    ],
                    [
                        'name' => 'Obchod si nejprve prověřím.',
                        'right' => true,
                        'evaluation' => 'Výborně, kapitáne! Nenechal jste se zaslepit slevou a všiml jste si, že tady něco velmi nesedí.<br><br>Prověřit si e-shop na internetu, Heurece, dTestu nebo České obchodní inspekci je dobrý nápad, ale <strong>má to jeden velký háček</strong>.<br><br>Podvodné stránky vznikají a mizí každý den. Může se stát, že je tento web tak nový, že před ním zkrátka ještě nikdo nestihl varovat. To, že o něm nenajdete špatné zprávy, neznamená, že je bezpečný.<br><br>Vy jste ale správně odhalil spoustu varovných prvků přímo na stránce:<br><br><strong>Parazitování:</strong> „ALZAA“ má o písmenko víc, aby vás oklamala, že jde o známou Alzu.<br><br><strong>Úplná anonymita:</strong> Chybí IČO, adresa i telefon. Vlastně nevíte, u koho nakupujete.<br><br><strong>Jen peníze předem:</strong> Odmítají dobírku a chtějí peníze rovnou na účet.<br><br><strong>Hlavní pravidlo:</strong> Jakmile e-shop tají, kdo ho provozuje, a zároveň chce platbu předem, nehledejte na něj ani recenze a rovnou stránku zavřete. S největší pravděpodobností se jedná o podvod.',
                    ],
                ],
            ],
            [
                'button' => 1,
                'difficulty' => 3,
                'perex' => 'Reklama na Facebooku – chytré hodinky',
                'description' => '[[image:situace]]<p>Chcete si koupit robotický vysavač.</p><p>Do vyhledávání zadáte: „Philips SmartClean sleva“.<br>Mezi prvními výsledky se objeví obchod <strong>ALZAA Elektro</strong>.</p><p>Název připomíná známý obchod Alza, stránka má logo, fotky výrobku a působí jako běžný e-shop.</p><p>U produktu svítí nabídka:<br>„Původní cena: 9 990 Kč, dnes pouze: 1 390 Kč“</p><p>Pod cenou:<br>„Mimořádná akce - doprodej skladu“, „Zbývají poslední kusy“</p><p>Na stránce není jasné, kdo obchod provozuje - chybí adresa i telefon.<br>Obchod nabízí jen platbu převodem předem.</p><p>Navíc se objeví odpočet:<br>„Akce končí za 1:59“</p>',
                'safety_card' => null,
                'image' => [
                    'key' => 'situace',
                    'path' => 'images/situations/d0a5f8a7-955c-407b-8c1e-661902b06b80.webp',
                    'alt' => 'Reklama na Facebooku – chytré hodinky',
                ],
                'options' => [
                    [
                        'name' => 'Sleva je super, objednávku zaplatím převodem.',
                        'right' => false,
                        'evaluation' => 'Zadržte, kapitáne!<br><br>Tímto rozhodnutím byste poslali peníze na účet, o kterém nevíte, komu patří. Zboží by s velkou pravděpodobností vůbec nedorazilo a peníze by nebylo možné získat zpět.<br><br>Pojďme si tuhle situaci rozebrat. Na první pohled působí nabídka důvěryhodně - známě vypadající obchod, výrazná sleva a běžný vzhled stránky.<br><br>Napodobení důvěry: Název podobný známému obchodu ve vás vyvolává pocit, že nakupujete bezpečně.<br><br>Výhodná cena: Nízká cena ve vás vytváří pocit, že jde o výjimečnou příležitost, kterou by byla škoda propásnout.<br><br>Časový tlak: Odpočet „1:59“ je klíčový. Má vás donutit jednat okamžitě a nedat vám prostor si situaci promyslet.<br><br>Nejasná identita a riziková platba: Nevíte, kdo obchod provozuje, a přesto byste poslali peníze převodem předem na neznámý účet.<br><br>Teď si to zhodnoťme: když se spojí podobný název, výrazná sleva, tlak na rychlé rozhodnutí a nejasné informace o prodejci, je to situace, kterou je potřeba zastavit.<br><br>Seriózní e-shop vás nebude nutit zaplatit během dvou minut.<br><br>Dobrá strategie je obchod ověřit mimo jeho stránku - například podle recenzí (Heureka) nebo varování (Česká obchodní inspekce, dTest).<br><br>Zkuste to znovu.',
                    ],
                    [
                        'name' => 'Produkt si jen prohlédnu, zatím neobjednávám.',
                        'right' => true,
                        'evaluation' => 'V pořádku, kapitáne – pouhým prohlížením stránky o žádné peníze nepřijdete. Je skvělé, že nespěcháte s objednávkou. Když už se ale díváte, tahle stránka přímo křičí, že je to past.<br><br><strong>Všimněte si tří zásadních varovných znamení:</strong><br><br><strong>Falešné jméno:</strong> Obchod se jmenuje <strong>ALZAA</strong> Elektro. Podvodníci schválně zkopírovali vzhled slavné Alzy a sází na to, že si jednoho písmenka navíc nevšimnete.<br><br><strong>Úplná anonymita:</strong> Chybí adresa, telefon i IČO. Nemáte vůbec tušení, komu posíláte peníze a kde případně zboží reklamovat.<br><br><strong>Pouze platba předem:</strong> Stránka vás nutí poslat peníze na účet dřív, než cokoli uvidíte. To je pro podvodníky nejjednodušší způsob, jak vás okrást.<br><br><strong>Tlak na čas</strong>: odpočet zvyšuje šanci, že budeme jednat pod tlakem<br><br><strong>Hlavní pravidlo:</strong> Dívat se můžete, ale jakmile e-shop schovává své kontakty a vyžaduje pouze platbu předem,  a tlačí nás do okamžité reakce - rychle pryč.',
                    ],
                    [
                        'name' => 'Obchod se mi nezdá. Ještě ho prověřím.',
                        'right' => true,
                        'evaluation' => 'Výborně, kapitáne! Nenechal jste se zaslepit slevou a všiml jste si, že tady něco velmi nesedí.<br><br>Prověřit si e-shop na internetu, Heurece, dTestu nebo České obchodní inspekci je dobrý nápad, ale <strong>má to jeden velký háček</strong>.<br><br>Podvodné stránky vznikají a mizí každý den. Může se stát, že je tento web tak nový, že před ním zkrátka ještě nikdo nestihl varovat. To, že o něm nenajdete špatné zprávy, neznamená, že je bezpečný.<br><br>Vy jste ale správně odhalil spoustu varovných prvků přímo na stránce:<br><br><strong>Parazitování:</strong> „ALZAA“ má o písmenko víc, aby vás oklamala, že jde o známou Alzu.<br><br><strong>Úplná anonymita:</strong> Chybí IČO, adresa i telefon. Vlastně nevíte, u koho nakupujete.<br><br><strong>Jen peníze předem:</strong> Odmítají dobírku a chtějí peníze rovnou na účet.<br><br><strong>Časový nátla</strong>k: snaha o okamžitou reakci bez možnosti si nákup rozmyslet je varovný signál<br><br><strong>Hlavní pravidlo:</strong> Jakmile e-shop tají, kdo ho provozuje, a zároveň chce platbu předem, a spěchá na objednávku - nehledejte na něj ani recenze a rovnou stránku zavřete. S největší pravděpodobností se jedná o podvod.',
                    ],
                ],
            ],

            // ===== DRUHÝ herní kámen =====
            [
                'button' => 2,
                'difficulty' => 1,
                'perex' => 'Reklama – investice s vysokým výnosem',
                'description' => '[[image:situace]]<p>Při prohlížení Facebooku se vám zobrazí reklama:</p><p>„Matka samoživitelka z Ostravy našla způsob, jak si vydělávat z domova.<br>Dnes má pasivní příjem až 50 000 Kč týdně.“</p><p>V textu stojí:<br>„Stačilo začít s částkou 5 000 Kč.“</p><p>Pod reklamou jsou komentáře:<br>„Už jsem vydělal 60 tisíc.“<br>„Funguje to.“</p><p>Tlačítko: „Zjistit více“</p>',
                'safety_card' => null,
                'image' => [
                    'key' => 'situace',
                    'path' => 'images/situations/171be4ed-a01a-4c4e-817d-d587e56bc054.webp',
                    'alt' => 'Reklama – investice s vysokým výnosem',
                ],
                'options' => [
                    [
                        'name' => 'Kliknu na “Zjistit více”, nabídka je zajímavá',
                        'right' => false,
                        'evaluation' => 'Zadržte, kapitáne!<br><br>Kliknutím na reklamu byste vstoupili do procesu, který vás postupně dovede k zadání údajů a odeslání peněz.<br><br>Pojďme si tuhle situaci rozebrat. Na první pohled působí důvěryhodně – vidíte konkrétní příběh, částku i zkušenosti „ostatních lidí“.<br><br>Silný příběh: „Matka samoživitelka“ má ve vás vyvolat pocit, že jde o běžného člověka. Máte pocit, že když to zvládla ona, zvládnete to také.<br><br>Slib snadného zisku: Vysoký příjem bez námahy má vyvolat dojem, že jde o jednoduchou příležitost.<br><br>Sociální důkaz: Komentáře mají vytvořit pocit, že to funguje i ostatním. Často jsou ale falešné nebo vytvořené podvodníkem.<br><br>Teď si to zhodnoťme: sliby rychlého a vysokého výdělku bez rizika nejsou realistické.<br><br>Bezpečný postup je takovou reklamu neotevírat a informace si ověřit z nezávislých zdrojů.<br><br>Zkuste to znovu.',
                    ],
                    [
                        'name' => 'Ignoruji reklamu.',
                        'right' => true,
                        'evaluation' => 'Výborně, kapitáne!<br><br>Tím, že jste na reklamu vůbec nereagovali, jste zastavili celý proces hned na začátku.<br><br>Pojďme si říct, proč to bylo důležité.<br><br>Reklama je navržená tak, aby ve vás vyvolala zájem a přiměla vás kliknout. Využívá silný příběh, vysoký výdělek a komentáře, které vytvářejí dojem, že to funguje i ostatním.<br><br>Jakmile byste klikli, dostali byste se na stránku, kde by pokračovala další manipulace – například výzvy k registraci nebo první „investici“.<br><br>Teď si to zhodnoťme: pokud něco slibuje vysoký výdělek bez práce a bez rizika, je potřeba zbystřit. Takové nabídky nejsou realistické.',
                    ],
                    [
                        'name' => 'Nejsem si jistý, radši se poradím s rodinou.',
                        'right' => true,
                        'evaluation' => 'Výborně!<br><br>Rozhodli jste se nezůstat na to sami a to je v této situaci velmi důležité.<br><br>Podívejme se na tu reklamu. Příběh „matky samoživitelky“ a vysoký výdělek ve vás mají vyvolat pocit, že jde o běžnou a dosažitelnou věc. Komentáře jako „funguje to“ mají tento dojem ještě posílit.<br><br>Právě v takové chvíli je snadné uvěřit a kliknout.<br><br>Když jste se ale rozhodli poradit s někým dalším, získali jste odstup a možnost situaci zhodnotit klidněji.<br><br>Teď si to zhodnoťme: seriózní investice neslibují vysoké zisky bez rizika a nešíří se přes anonymní reklamy na sociálních sítích.<br><br>Nejbezpečnější postup je takovou reklamu vůbec neotevírat a dál ji neřešit.',
                    ],
                ],
            ],
            [
                'button' => 2,
                'difficulty' => 2,
                'perex' => 'Investiční e-mail – „úspěšní investují“',
                'description' => '[[image:situace]]<p>Do e-mailu vám přijde zpráva:</p><p>„Důležité: Nová investiční příležitost pro klienty českých bank“</p><p>Odesílatel: financni-info@novinki-news.com</p><p>Text:<br>„Nový investiční systém umožňuje vydělávat až 30 000 Kč týdně.“<br>„Banky se snaží tuto informaci skrýt.“<br>„Stačí začít s částkou 5 000 Kč.“</p><p>Tlačítko: „Otevřít investiční účet“</p>',
                'safety_card' => null,
                'image' => [
                    'key' => 'situace',
                    'path' => 'images/situations/4fea6123-0b4e-47c2-a4ec-b7ca97b11005.webp',
                    'alt' => 'Investiční e-mail – „úspěšní investují“',
                ],
                'options' => [
                    [
                        'name' => 'Kliknu na “Otevřít investiční účet“',
                        'right' => false,
                        'evaluation' => 'Zadržte, kapitáne!<br><br>Kliknutím na odkaz byste se dostali na stránku, která vás vyzve k zadání osobních údajů nebo první „investice“. Peníze by odešly podvodníkovi a nebylo by možné je získat zpět.<br><br>Pojďme si ten e-mail rozebrat. Na první pohled působí důvěryhodně – mluví o bankách a investování.<br><br>Falešná autorita: Zmínka o „klientech českých bank“ má vyvolat pocit, že jde o oficiální nabídku.<br><br>Slib vysokého výdělku: Částka „30 000 Kč týdně“ má vzbudit zájem a pocit výjimečné příležitosti.<br><br>Utajovaná informace: Věta „banky se to snaží skrýt“ má vyvolat dojem, že jste se dostali k něčemu výjimečnému.<br><br>Teď si to zhodnoťme: seriózní investice se neposílají neznámým e-mailem a neslibují vysoké zisky bez rizika.<br><br>Bezpečný postup je na takové odkazy neklikat a nabídku si ověřit z jiného zdroje.<br><br>Zkuste to znovu.',
                    ],
                    [
                        'name' => 'Chci více informací, na e-mail odepíšu',
                        'right' => false,
                        'evaluation' => 'Zadržte, kapitáne!<br><br>Odpovědí na e-mail byste podvodníkovi potvrdili, že je na druhé straně skutečný člověk.<br><br>Pojďme si říct, co by následovalo. Podvodník by začal komunikaci rozvíjet – vysvětloval by „výhody“, odpovídal na vaše otázky a snažil by se získat vaši důvěru.<br><br>Postupná manipulace: Přímá komunikace umožňuje podvodníkovi reagovat na vaše obavy a přesvědčit vás k dalším krokům.<br><br>Budování důvěry: Čím déle komunikace trvá, tím více může působit důvěryhodně.<br><br>Teď si to zhodnoťme: seriózní investiční nabídky nepřicházejí z neznámých e-mailů a nevyžadují osobní komunikaci s neznámým odesílatelem.<br><br>Bezpečný postup je na takové e-maily vůbec neodpovídat.<br><br>Zkuste to znovu.',
                    ],
                    [
                        'name' => 'Ověřím si nabídku jinde',
                        'right' => true,
                        'evaluation' => 'Výborně, kapitáne!<br><br>Podobné nabídky si můžete ověřit třeba přes <strong>Registr České národní banky (ČNB).</strong> Každá banka nebo firma, která v Česku legálně nabízí investice, <strong>musí</strong> mít licenci od ČNB. Na webu ČNB (cnb.cz) existuje seznam varování a seznam blokovaných podvodných firem. Pokud tam firma odesílatele chybí, je to nelegální past.<br><br><strong>Selský rozum a matematika:</strong> Slibují vám 30 000 Kč týdně z pouhých 5 000 Kč? To je zhodnocení o stovky procent za pár dní. Kdyby takový stroj na peníze existoval, nikdo v této zemi už nemusí chodit do práce a banky by dávno zkrachovaly. Garantovaný obří zisk bez rizika neexistuje.<br><br><strong>Infolinka vaší banky:</strong> V e-mailu se píše, že je to akce „pro klienty českých bank“. Pokud máte pochybnosti, stačí zavolat na oficiální linku své vlastní banky a zeptat se.<br><br>Pokud o takovou nabídku vůbec nestojíte, můžete podobný e-mail také rovnou smazat nebo ignorovat.',
                    ],
                ],
            ],
            [
                'button' => 2,
                'difficulty' => 3,
                'perex' => 'Investiční e-mail – falešná registrace',
                'description' => '<p>Do e-mailu vám přijde zpráva:</p><p>„Důležité video: Nová investiční příležitost pro občany České republiky“</p><p>Odesílatel: investice-info@financni-system24.com</p><p>V e-mailu je video, které vypadá jako reportáž z ČT24.<br>Vystupuje v něm známý český politik a mluví o investiční platformě, která údajně vydělává vysoké částky.</p><p>Pod videem stojí:<br>„Registrace je otevřena jen krátce.“<br>„Zbývá posledních 8 míst.“<br>„Dokončete registraci do 10 minut.“</p><p>Tlačítko: „Dokončit registraci“</p><p>(Hráč si video automaticky pustí.)</p>',
                'safety_card' => null,
                'options' => [
                    [
                        'name' => 'Rychle kliknu na „Dokončit registraci“',
                        'right' => false,
                        'evaluation' => 'Zadržte, kapitáne!<br><br>Tímto krokem byste se dostali na stránku, kde by po vás mohli chtít osobní údaje, kontaktní informace nebo první investici. Peníze či údaje by skončily u podvodníka.<br><br>Pojďme si tu situaci rozebrat. Tento e-mail kombinuje hned několik manipulačních prvků.<br><br>Falešná důvěryhodnost: Video připomíná známou zpravodajskou stanici a vystupuje v něm známý politik. To má vyvolat pocit, že jde o ověřenou informaci.<br><br>Známá osobnost: Lidé častěji důvěřují informaci, pokud ji spojují s někým známým nebo autoritativním.<br><br>Časový tlak: „Posledních 8 míst“ a „10 minut“ vás mají donutit jednat rychle, bez ověření.<br><br>Teď si to zhodnoťme: seriózní investice nestaví na nátlaku, časomíře ani videu v e-mailu.<br><br>Důležité je také vědět, že video se známou osobností nemusí být pravé. Podvodníci dnes dokážou upravit obraz i hlas tak, aby působily důvěryhodně.<br><br>Bezpečný postup je neklikat, nevyplňovat údaje a informaci si ověřit mimo e-mail.<br><br>Zkuste to znovu.',
                    ],
                    [
                        'name' => 'Chci více informací, na e-mail odepíšu',
                        'right' => false,
                        'evaluation' => 'Odpovědí byste podvodníkovi potvrdili, že e-mail používá skutečný člověk, který je ochotný reagovat.<br><br>Pojďme si říct, co by následovalo. Podvodník by mohl navázat osobní komunikaci, odpovídat na vaše otázky, posílat další „důkazy“ a postupně budovat důvěru.<br><br>Postupná manipulace: Čím déle komunikace trvá, tím snáz může působit důvěryhodně.<br><br>Falešný obsah: Video může být upravené nebo zcela nepravdivé.<br><br>Časový tlak: I zde vás může podvodník tlačit k rychlému rozhodnutí, než si vše ověříte.<br><br>Teď si to zhodnoťme: neznámý e-mail s investiční nabídkou není důvod k navazování komunikace.<br><br>Bezpečný postup je neodpovídat a informace si ověřit jinou cestou.<br><br>Zkuste to znovu.',
                    ],
                    [
                        'name' => 'Ověřím informace na věrohodných zdrojích a e-mail smažu.',
                        'right' => true,
                        'evaluation' => 'Výborně, kapitáne!<br><br>Nenechali jste se přesvědčit samotným videem ani tlakem na rychlou registraci. To je v této situaci zásadní krok.<br><br>Pojďme si rozebrat, co jste zvládli správně.<br><br>Nepodlehli jste falešné autoritě. To, že video vypadá jako známá reportáž nebo v něm vystupuje známá osoba, ještě neznamená, že je pravé.<br><br>Odolali jste i časovému tlaku. „Poslední místa“ a odpočet mají jediný cíl - zabránit vám v klidném ověření.<br><br>Místo toho jste informace ověřili mimo e-mail - například na oficiálních stránkách důvěryhodných médií, banky nebo České národní banky.<br><br>Teď si to zhodnoťme: pokud investiční nabídka stojí hlavně na emocích, autoritě a nátlaku, je potřeba zbystřit.<br><br>Smazáním e-mailu jste navíc přerušili další pokusy o manipulaci.',
                    ],
                ],
            ],

            // ===== TŘETÍ herní kámen =====
            [
                'button' => 3,
                'difficulty' => 1,
                'perex' => 'Nabídka brigády přes WhatsApp',
                'description' => '[[image:situace]]<p>Do mobilu vám přijde zpráva z neznámého čísla:</p><p>„Dobrý den, nabízíme jednoduchý přivýdělek z domova.</p><p>Hledáme lidi, kteří budou otevírat zaslaná videa, sledovat je podle pokynů a potvrzovat splnění jednoduchých úkolů.</p><p>Za každý splněný úkol dostanete zaplaceno.</p><p>Za první vyzkoušení získáte 200 Kč.</p><p>Denně si můžete vydělat až 4 000–10 000 Kč.“</p>',
                'safety_card' => null,
                'image' => [
                    'key' => 'situace',
                    'path' => 'images/situations/9d640ae4-7de9-4e74-902c-723603106c5c.webp',
                    'alt' => 'Nabídka brigády přes WhatsApp',
                ],
                'options' => [
                    [
                        'name' => 'Kliknu a rovnou splním úkol',
                        'right' => false,
                        'evaluation' => 'Zadržte, kapitáne! Tady jde o mnohem víc než jen o ztrátu času. Tato zdánlivě nevinná „brigáda“ je rafinovaná past, kde se nevědomky můžete dostat do vážného křížku se zákonem.<br><br><strong>Proč je tato nabídka tak obrovským rizikem?</strong><br><br><strong>Háček s návnadou:</strong> Podvodníci vám těch prvních 200 Kč klidně na účet pošlou. Udělají to schválně, aby získali vaši důvěru a vy jste uvěřil, že je všechno legální.<br><br><strong>Role „bílého koně“:</strong> V dalších krocích po vás budou chtít, abyste přijímal platby na svůj účet a posílal je dál, nebo si zakládal nové účty. Tím z vás udělají takzvaného <strong>bílého koně</strong> – nástroj, přes který podvodníci perou špinavé peníze ukradené jiným obětem.<br><br><strong>Neznalost neomlouvá:</strong> Pokud přes váš účet protečou ukradené peníze, policie zaklape na dveře <strong>vám</strong>, protože váš účet je jediná stopa, kterou mají. Před soudem vás neochrání ani obhajoba, že jste o ničem nevěděl. Za legalizaci výnosů z trestné činnosti hrozí reálné tresty.<br><br><strong>Hlavní pravidlo:</strong> Nikdo na světě vám nedá 10 000 Kč denně za pouhé klikání na videa. Jakmile po vás neznámý člověk z SMS chce jakékoli operace s penězi nebo plnění úkolů, okamžitě zprávu smažte.<br><br>Zkusme to znovu a bezpečněji.',
                    ],
                    [
                        'name' => 'Je to zajímavé, kliknu na odkaz, ale jen se podívám, úkol neplním',
                        'right' => false,
                        'evaluation' => 'Chválím vaši opatrnost, kapitáne .<br><br>Jenže u SMS zpráv od cizích čísel skrývá obrovské riziko už samotné kliknutí na odkaz.<br><br><strong>Proč je nebezpečné na takový odkaz vůbec kliknout?</strong><br><br><strong>Past na váš mobil (Malware):</strong> Odkaz vás může přesměrovat na stránku, která se pokusí do vašeho telefonu tajně stáhnout škodlivý program (virus). Ten pak může sledovat, co na mobilu děláte, nebo se pokusit dostat do vašeho mobilního bankovnictví.<br><br><strong>Chycení do databáze:</strong> Jakmile na odkaz kliknete, podvodníci ve svém systému uvidí: „Aha, toto telefonní číslo je aktivní a jeho majitel na zprávy kliká.“ Tím jim potvrdíte, že jste ideální terč, a začnou vás bombardovat dalšími podvodnými SMSkami a telefonáty.<br><br><strong>Psychologický tlak:</strong> Stránka za odkazem bývá graficky skvěle zpracovaná. Ukáže vám falešné fotky spokojených Čechů, kteří si takto „vydělali na nové auto“. Je těžké takovému nátlaku odolat, když už na té stránce jste.<br><br><strong>Hlavní pravidlo:</strong> U podezřelých SMS zpráv platí stopka hned na začátku. Zvědavost je přirozená, ale bezpečnější je na odkaz vůbec neklikat a zprávu rovnou smazat.<br><br>Zkusme to znovu a tentokrát bez risku.',
                    ],
                    [
                        'name' => 'Zprávu smažu, nezdá se mi to',
                        'right' => true,
                        'evaluation' => 'Výborně, kapitáne!<br><br>Odolal jste zvědavosti i slibům snadného výdělku a nepřítele jste zneškodnil tím nejúčinnějším způsobem – smazáním zprávy.<br><br><strong>Pojďme si říct, jakým dvěma velkým hrozbám jste se právě vyhnul:</strong><br><br><strong>Virům ve vašem telefonu:</strong> Tím, že jste neklikl na přiložený odkaz, jste zabránil tomu, aby se vám do mobilu případně stáhl škodlivý program.<br><br><strong>Pasti na „bílého koně“:</strong> U podobných nabídek práce podvodníci často pošlou malou částku (např. těch 200 Kč) pro získání důvěry. Následně by po vás ale chtěli přeposílat peníze na jiné účty. Tím by vás nevědomky zapojili do praní špinavých peněz – a jak víme, před zákonem byste nesl odpovědnost vy, protože neznalost neomlouvá.<br><br>Smazáním zprávy jste udělal ten nejbezpečnější krok.',
                    ],
                ],
            ],
            [
                'button' => 3,
                'difficulty' => 2,
                'perex' => 'Pokračování brigády – další úkoly',
                'description' => '[[image:situace]]<p>Do mobilu vám přijde zpráva :</p><p>„Dobrý den, váš kontakt jsme dostali přes doporučení od vašich přátel.<br>Nabízíme jednoduchý přivýdělek z domova.</p><p>Stačí otevírat zaslaná videa, přidávat hodnocení a za každý úkol dostanete zaplaceno.</p><p>Za první úkol: 200 Kč.“</p><p>Pod zprávou vidíte další zprávy:<br>„Hotovo 👍“<br>„200 Kč přišlo.“<br>„Dnes už mám 600 Kč.“</p><p>Na konci zprávy stojí:<br>„Volná místa rychle mizí. Pokud chcete začít dnes, ozvěte se do 5 minut.“</p>',
                'safety_card' => null,
                'image' => [
                    'key' => 'situace',
                    'path' => 'images/situations/22f72e17-c8c7-45d2-9356-b0e31ef04cd5.webp',
                    'alt' => 'Pokračování brigády – další úkoly',
                ],
                'options' => [
                    [
                        'name' => 'Zprávu smažu, nezdá se mi to',
                        'right' => true,
                        'evaluation' => 'Výborně!<br><br>Smazat tuhle zprávu byl ten nejlepší tah.<br><br><strong>Tímto krokem jste odrazili hned tři triky:</strong><br><br><strong>Lživé doporučení:</strong> Věta, že mají kontakt „od vašich přátel“, je čistá lež. Šmejdi ji používají jen proto, abyste ztratil ostražitost.<br><br><strong>Falešný potlesk:</strong> Ty zprávy na konci typu „Hotovo 👍“ a „Peníze přišly“ si podvodník napsal sám. Má to vypadat jako spokojená diskuse, ale je to jen návnada.<br><br><strong>Pětiminutový nátlak:</strong> Umělý spěch („ozvěte se do 5 minut“) vás má donutit jednat ve zmatku a bez přemýšlení.',
                    ],
                    [
                        'name' => 'Zkusím první úkol',
                        'right' => false,
                        'evaluation' => 'Zadržte! Chápu, že 200 Kč za pár minut klikání zní lákavě a člověk si říká, že za zkoušku nic nedá. Jenže, co by se stalo, kdybyste do toho šel?<br><br><strong>Skutečná návnada:</strong> Podvodníci vám těch prvních 200 Kč na účet klidně opravdu pošlou. Udělají to schválně, abyste získal pocit, že je to bezpečné, a začal jim důvěřovat.<br><br><strong>Past jménem „bílý kůň“:</strong> V dalších dnech vám zadají „úkoly“, kde budete muset na svůj účet přijímat peníze a posílat je dál. V tu chvíli se z vás stává <strong>bílý kůň</strong> – člověk, kterého šmejdi zneužívají k praní špinavých peněz ukradených jiným obětem.<br><br><strong>Problém s policií:</strong> Jakmile na podvod přijde banka nebo policie, zablokují účet <strong>vám</strong>. Policie bude stíhat vás, protože váš účet je jediná stopa, kterou mají. Argument, že jste o ničem nevěděl, vás neochrání – neznalost zákona totiž neomlouvá.<br><br>Zkusme to znovu a bezpečněji.',
                    ],
                    [
                        'name' => 'Chci více informací, na zprávu odepíšu',
                        'right' => false,
                        'evaluation' => 'Zadržte! Chtít víc informací a prověřit si situaci je logický krok. U podvodných SMS je ale samotná odpověď chybou.<br><br><strong>Co se stane, když na takovou zprávu odpovíte?</strong><br><br><strong>Rozsvítíte zelenou:</strong> Podvodníkům ve vteřině potvrdíte: „Pozor, na tomto čísle žije reálný člověk, který zprávy čte a reaguje na ně.“<br><br><strong>Zápis na seznam terčů:</strong> Vaše číslo okamžitě získá nálepku „aktivní“. Podvodníci si ho mezi sebou nasdílejí a od té chvíle vás začnou bombardovat mnohem větším množstvím podvodných SMS a falešných telefonátů.<br><br><strong>Past se zaklapne:</strong> Jakmile odepíšete, vstoupíte do rozhovoru s vyškoleným manipulátorem (nebo naprogramovaným botem). Ten má připravené odpovědi na jakoukoli vaši otázku a udělá vše pro to, aby vás dotlačil k registraci nebo poslání peněz.<br><br><strong>Hlavní pravidlo:</strong> S autory podvodných SMS se nevyjednává a víc informací se od nich nežádá – poslali by vám jen další lži. Nejbezpečnější obrana je vůbec s nimi nezačínat mluvit.<br><br>Zkusme to znovu a bezpečněji.',
                    ],
                ],
            ],
            [
                'button' => 3,
                'difficulty' => 3,
                'perex' => 'Brigáda – žádost o údaje karty',
                'description' => '[[image:situace]]<p>Do mobilu vám přijde zpráva s profilovou fotkou muže v pracovním prostředí:</p><p>„Dobrý den, tady Novák z Centra finančních převodů. Dostal jsem na vás kontakt přes vaši bankovní poradkyni, prý jste spolehlivý.</p><p>Potřebujeme dnes rychle dokončit převod peněz pro zahraničního klienta.</p><p>Na váš účet přijde 48 000 Kč, které jen přepošlete dál podle instrukcí.</p><p>Za pomoc dostanete 2 000 Kč.</p><p>Abychom to mohli uskutečnit ještě dnes, pošlete prosím:<br>– číslo účtu<br>– jméno k účtu<br>– číslo platební karty<br>– datum platnosti<br>– CVC/CVV kód“</p>',
                'safety_card' => null,
                'image' => [
                    'key' => 'situace',
                    'path' => 'images/situations/011c8b2c-9b27-478f-b252-f9ce6014e286.webp',
                    'alt' => 'Brigáda – žádost o údaje karty',
                ],
                'options' => [
                    [
                        'name' => 'Požádám o více informací, odepíšu na zprávu',
                        'right' => false,
                        'evaluation' => 'Zadržte. Chtít víc informací je sice logický krok, ale v této situaci je samotná odpověď riskem.<br><br><strong>Proč je tato zpráva extrémně nebezpečná a proč neodpovídat?</strong><br><br>Pokud vám chce někdo poslat peníze, stačí mu jen číslo vašeho účtu. Jakmile po vás někdo chce <strong>číslo karty, platnost a třímístný CVC/CVV kód ze zadní strany</strong>, nechce vám peníze poslat, ale chce je z vaší karty <strong>ukrást</strong>. S těmito údaji může podvodník okamžitě vybrat vaše konto.<br><br>Žádné „Centrum finančních převodů“ neexistuje. Profilová fotka je ukradená z internetu a věta o vaší bankovní poradkyni je čistá lež. Banka by vaše číslo cizímu člověku nikdy nedala, porušila by tím zákon.<br><br>Když na zprávu odpovíte, podvodníkům potvrdíte, že jste zprávu četli a o nabídce přemýšlíte. Okamžitě se dostanete na seznam aktivních kontaktů a začnou na vás zkoušet další podvody.<br><br><strong>Hlavní pravidlo:</strong> Údaje z platební karty (hlavně CVC kód) slouží výhradně k tomu, když vy sami platíte. Nikdy je nikomu neposílejte v SMS.<br><br>Zkusme to znovu a bezpečněji.',
                    ],
                    [
                        'name' => 'Pošlu údaje k mojí kartě',
                        'right' => false,
                        'evaluation' => 'POZOR! Tohle je to nejnebezpečnější rozhodnutí, které vás může v jedné vteřině připravit o všechny úspory.<br><br><strong>Proč je odeslání údajů z karty tak nebezpečné?</strong><br><br>Číslo karty, platnost a hlavně třímístný <strong>CVC/CVV kód</strong> ze zadní strany slouží výhradně k placení. Podvodník je nepotřebuje k tomu, aby vám peníze poslal, ale aby je z vaší karty ukradnul. K přijetí peněz stačí jen čisté číslo účtu.<br><br>Jakmile tyto údaje do SMS napíšete, šmejdi s nimi začnou okamžitě nakupovat na internetu nebo peníze převedou do zahraničí. Než stihnete kartu zablokovat, účet může být prázdný.<br><br>Celý příběh o „panu Novákovi“, luxusní odměně a vaší bankovní poradkyni byla čistá lež.<br><br><strong>Hlavní pravidlo:</strong> Údaje ze své karty zadávejte <strong>jen vy sami</strong> při nákupu například v ověřeném e-shopu. Nikdy, ale opravdu <strong>nikdy</strong> je neposílejte nikomu v textové zprávě.<br><br>Zkusme to znovu.',
                    ],
                    [
                        'name' => 'Nic neposílám a ověřím si to u své banky',
                        'right' => true,
                        'evaluation' => 'Výborně!<br><br><strong>Co všechno jste tímto skvělým krokem zvládli?</strong><br><br>Správně víte, že k přijetí peněz stačí jen číslo účtu. Chtít po vás číslo karty a třímístný CVC kód je jako žádat klíče od vašeho domácího trezoru.<br><br>Celé to divadlo o „panu Novákovi“ a vaší bankovní poradkyni byla jen lež, která měla snížit vaši ostražitost. Banka by vaše kontakty cizímu člověku nikdy nedala.<br><br>Místo dohadování se s neznámým šmejdem jste zvolili jedinou správnou cestu – kontrolu u své skutečné banky.<br><br><strong>Hlavní pravidlo:</strong> Pokud máte jakékoli pochybnosti o svých penězích, vždy kontaktujte svou banku sami přes její oficiální telefonní číslo. Nikdy nevěřte lidem, kteří vám sami píší z neznámých čísel.<br><br>Skvělá práce.',
                    ],
                ],
            ],

            // ===== ČTVRTÝ herní kámen =====
            [
                'button' => 4,
                'difficulty' => 1,
                'perex' => 'Zájemce z bazaru – první kontakt',
                'description' => '[[image:situace]]<p>Na Facebooku prodáváte sekačku na trávu.</p><p>Krátce po zveřejnění nabídky vám přijde zpráva od zájemce:</p><p>„Dobrý den, sekačku koupím.<br>Bydlím daleko, proto objednám DPD kurýra, který sekačku vyzvedne přímo u vás doma, takže ji nemusíte nikam vozit.</p><p>DPD vám pošle potvrzení dopravy a formulář k přijetí platby za prodané zboží.<br>Stačí kliknout na odkaz, potvrdit objednávku kurýra a vyplnit číslo platební karty, datum platnosti a CVC kód.“</p><p>Pod zprávou je odkaz.</p>',
                'safety_card' => null,
                'image' => [
                    'key' => 'situace',
                    'path' => 'images/situations/0e68dd3a-1488-4461-8815-6888369c670e.webp',
                    'alt' => 'Zájemce z bazaru – první kontakt',
                ],
                'options' => [
                    [
                        'name' => 'Odkaz neotevřu a zprávu ignoruji',
                        'right' => true,
                        'evaluation' => 'Skvěle, ignorovat tuhle zprávu byl ten nejlepší krok.<br><br><strong>Tímto rozhodnutím jste prokoukl tři velké bazarové lži:</strong><br><br><strong>Zneužití známé značky:</strong> Podvodníci se schválně zaštiťují jmény jako DPD, Zásilkovna nebo PPL. Vědí, že těmto firmám věříte, a doufají, že kvůli tomu ztratíte ostražitost.<br><br><strong>Obrácená logika placení:</strong> Tohle je nejdůležitější pravidlo online bazarů – když vám chce někdo poslat peníze za zboží, stačí mu vaše <strong>číslo účtu</strong>. Nikdy k tomu nepotřebuje údaje z vaší platební karty, a už vůbec ne třímístný CVC kód ze zadní strany.<br><br><strong>Falešné pohodlí:</strong> Nabídka, že kupující všechno zařídí, zaplatí a pošle kurýra až k vašim dveřím, zní lákavě. Je to ale jen psychologický trik, abyste získal pocit, že nemáte žádnou starost, a bez přemýšlení kliknul na odkaz.<br><br><strong>Před čím jste zachránil své peníze?</strong> Zaslaný odkaz by vás dovedl na falešnou stránku, která by vypadala jako web DPD. V dobré víře byste na ni zadali svoje údaje, které by podvodníci zneužili a ukradli vaše peníze z účtu.',
                    ],
                    [
                        'name' => 'Na odkaz kliknu a případně vyplním, co bude potřeba',
                        'right' => false,
                        'evaluation' => 'Poplach! Tohle je to nejnebezpečnější rozhodnutí, které vás v reálném životě může během jediné minuty připravit o veškeré celoživotní úspory.<br><br><strong>Proč je odeslání těchto údajů katastrofa?</strong><br><br>Číslo karty, platnost a hlavně třímístný <strong>CVC kód</strong> podvodník je nepotřebuje k tomu, aby vám peníze poslal, ale aby je z vaší karty ukradnul. K přijetí platby za sekačku přitom stačí čisté číslo vašeho bankovního účtu.<br><br>Jakmile údaje do formuláře zadáte, šmejdi na nic nečekají. Okamžitě s nimi začnou nakupovat na internetu nebo peníze převedou do zahraničí. Než stihnete kartu zablokovat, váš účet může být úplně prázdný.<br><br>Celá ta pohádka o tom, jak kupující všechno zařídí, objedná DPD kurýra a vy nemáte žádné starosti, byla jen návnada. Měla vás ukolébat, abyste ztratili ostražitost a dobrovolně jim vydali přístup ke svému kontu. Stránka, na kterou jste klikli, byl jen dokonalý padělek.<br><br><strong>Hlavní pravidlo:</strong> Při prodeji na internetových bazarech dávejte zájemcům <strong>výhradně číslo svého bankovního účtu</strong>. Nikdy, ale opravdu nikdy nezadávejte údaje ze své karty do odkazů, které vám pošle cizí člověk v chatu.<br><br>Zkusme to znovu.',
                    ],
                    [
                        'name' => 'Odepíšu a zeptám se zájemce na více podrobností',
                        'right' => false,
                        'evaluation' => 'Zadržte! Chtít se zeptat na podrobnosti je sice logický krok, ale v prostředí internetových bazarů se ale psaním dalších zpráv nevědomky chytáte do pasti.<br><br><strong>Co by se stalo, kdybyste se zájemcem začali diskutovat?</strong><br><br><strong>Vyškolení manipulátoři (nebo roboti):</strong> Na druhé straně často nesedí skutečný nakupující, ale naprogramovaný robot (bot) nebo vyškolený podvodník. Na jakoukoliv vaši pochybnost mají připravenou okamžitou, lákavou odpověď, která vás má jen uklidnit.<br><br><strong>Falešné důkazy:</strong> Když jim napíšete, že se vám to nezdá, klidně vám pošlou zfalšované obrázky, „oficiální“ potvrzení od DPD nebo vymyšlené návody, které tvrdí, že tento postup je nový, moderní a naprosto bezpečný.<br><br><strong>Útok na city:</strong> Podvodníci umí skvěle hrát na city. Začnou tvrdit, že jsou maminky na mateřské, že už dopravu zaplatili a vy je teď chcete okrást o peníze. Sází na to, že vám jich bude líto, ztratíte ostražitost a na odkaz nakonec kliknete.<br><br><strong>Hlavní pravidlo:</strong> S lidmi, kteří po vás chtějí údaje z karty kvůli tomu, aby vám poslali peníze, se vůbec nevyjednává. Jakmile zájemce poruší základní pravidlo bezpečného prodeje, okamžitě s ním ukončete konverzaci.<br><br>Zkusme to znovu a bezpečnější cestou.',
                    ],
                ],
            ],
            [
                'button' => 4,
                'difficulty' => 2,
                'perex' => 'Zájemkyně z bazaru – platba předem',
                'description' => '[[image:situace]]<p>Na Facebooku prodáváte sekačku na trávu.</p><p>Krátce po zveřejnění nabídky vám přijde zpráva od zájemkyně:</p><p>„Dobrý den, sekačku beru.<br>Jsem teď v práci, proto jsem už zaplatila přes Zásilkovnu i dopravu a objednala kurýra k vám domů.</p><p>Tady je odkaz na potvrzení objednávky a přijetí platby.<br>Prosím potvrďte to do 5 minut, jinak se objednávka zruší, peníze se mi vrátí a budu to celé muset vyplňovat znovu.“</p><p>Pod zprávou je odkaz.</p><p>Běží odpočet času.</p>',
                'safety_card' => null,
                'image' => [
                    'key' => 'situace',
                    'path' => 'images/situations/229a9dff-21b5-415b-b552-91eaa9c48db7.webp',
                    'alt' => 'Zájemkyně z bazaru – platba předem',
                ],
                'options' => [
                    [
                        'name' => 'Odkaz neotevřu a zprávu ignoruji',
                        'right' => true,
                        'evaluation' => 'Výborně! Nenechali jste se zahnat do kouta časovým nátlakem ani falešnou slušností.<br><br><strong>Tímto rozhodnutím jste prokoukli tři triky:</strong><br><br>Odpočet času vás má vystresovat. Podvodníci dobře vědí, že v časové tísni lidé zmatkují, dělají chyby a jednají bez přemýšlení.<br><br>Věta o tom, že to paní bude muset „vyplňovat znovu“, ve vás má vyvolat pocit, že někomu zbytečně přiděláváte starosti. Je to ale jen psychologické citové vydírání.<br><br>Šmejdi se schovávají za známou a oblíbenou značku, aby získali vaši důvěru. Zásilkovna ale takovým způsobem peníze nikdy lidem neposílá.<br><br><strong>Před čím jste zachránili své peníze?</strong> Zaslaný odkaz by vás zavedl na věrnou kopii webu Zásilkovny. Tam by po vás chtěli vyplnit kompletní údaje z platební karty, aby vám prý „přišly peníze“. Jakmile byste je ale zadali, podvodníci by vám z účtu ukradli peníze.<br><br><strong>Hlavní pravidlo:</strong> Kupující na internetovém bazaru vám nikdy nemůže poslat peníze na vaši kartu přes žádný odkaz. Pro příjem platby mu dejte výhradně číslo svého bankovního účtu.<br><br>Ignorováním zprávy jste udělali ten nejbezpečnější krok a ochránili své úspory!',
                    ],
                    [
                        'name' => 'Na odkaz kliknu a vyplním, co bude potřeba',
                        'right' => false,
                        'evaluation' => 'Poplach! Tohle je to nejnebezpečnější rozhodnutí, které vás v reálném životě může během jediné minuty připravit o veškeré celoživotní úspory.<br><br><strong>Proč je splnění tohoto požadavku naprostá katastrofa?</strong><br><br>Jakmile do toho odkazu vyplníte číslo karty, platnost a třímístný <strong>CVC kód</strong>, předali jste podvodníkům kompletní přístup ke svým penězům. Tyto údaje kupující nepotřebuje k tomu, aby vám peníze poslal, ale slouží výhradně k tomu, aby je z vaší karty ukradnul.<br><br>Podvodníci na nic nečekají. Jakmile údaje vyplníte, okamžitě s nimi začnou nakupovat na internetu nebo peníze převedou do zahraničí. Než stihnete kartu zablokovat, váš účet může být úplně prázdný.<br><br>Celý ten pětiminutový odpočet času a naříkání paní, jak to bude muset vyplňovat znovu, byl jen psychologický útok. Šmejdi záměrně vyvolávají stres a pocit viny, aby vás donutili jednat zbrkle a bez přemýšlení.<br><br><strong>Hlavní pravidlo:</strong> Při prodeji na internetových bazarech dávejte zájemcům <strong>výhradně číslo svého bankovního účtu</strong>. Nikdy, ale opravdu nikdy nezadávejte údaje ze své karty do odkazů, které vám pošle cizí člověk v chatovací zprávě.<br><br>Zkusme to znovu.',
                    ],
                    [
                        'name' => 'Odepíšete zájemkyni a zeptáte se jí na podrobnosti',
                        'right' => false,
                        'evaluation' => 'Zadržte! Chtít víc informací dává smysl. Jenže v prostředí online bazarů je psaní dalších zpráv přesně to, co podvodnice potřebuje, abyste se chytili do pasti.<br><br><strong>Proč je vyjednávání v této situaci velká chyba?</strong><br><br><strong>Stroj na lži:</strong> Na druhé straně s velkou pravděpodobností nesedí reálná nakupující, ale naprogramovaný počítačový robot (bot) nebo vyškolený manipulátor. Na jakoukoliv vaši otázku mají okamžitou a velmi věrohodnou odpověď. Klidně vám pošlou i zfalšovaný obrázek s „oficiálním návodem“ od Zásilkovny.<br><br><strong>Stupňování nátlaku:</strong> Když vyjádříte pochybnosti, podvodnice nezačne jednat rozumně. Naopak na vás zaklekne ještě víc: „Čas běží, už zbývají jen 2 minuty, dělejte prosím, přijdeme o peníze!“ Udělá všechno pro to, aby vás udržela ve stresu a vy jste přestali racionálně přemýšlet.<br><br><strong>Potvrzení zájmu:</strong> Tím, že odpovíte, jí dáváte najevo, že o obchod stojíte a bude nátlak zvyšovat.<br><br><strong>Hlavní pravidlo:</strong> S lidmi, kteří na vás na bazaru zkouší časový nátlak a posílají odkazy na vyplnění karty, se vůbec nediskutuje. Žádné pravdivé informace z nich nedostanete. Nejbezpečnější je konverzaci okamžitě ukončit.<br><br>Zkusme to znovu.',
                    ],
                ],
            ],
            [
                'button' => 4,
                'difficulty' => 3,
                'perex' => 'Zájemkyně z bazaru – odkaz na platbu',
                'description' => '[[image:situace]]<p>Na Facebooku prodáváte sekačku na trávu.</p><p>Ozve se vám zájemkyně a působí velmi důvěryhodně:</p><p>„Dobrý den, je sekačka stále dostupná?“„Jak je stará?“<br>„Je plně funkční?“ „Máte k ní i koš?“<br>„Prosím, poslala byste mi ještě detail motoru?“ “Přesně takovou hledám pro rodiče na zahradu.“</p><p>Po chvíli napíše: „Beru ji👍 Jsem teď v práci, takže jsem už rovnou zaplatila přes Zásilkovnu i dopravu a objednala kurýra k vám domů, ať s tím nemáte starosti.</p><p>Tady je odkaz na potvrzení přijetí platby. Stačí potvrdit objednávku, vyplnit číslo platební karty, datum platnosti a CVC kód, aby vám mohly být peníze připsány.</p><p>Prosím udělejte to do 3 minut, jinak rezervace propadne a budu to celé muset vyplňovat znovu 😕“</p><p>Pod zprávou je odkaz.</p>',
                'safety_card' => null,
                'image' => [
                    'key' => 'situace',
                    'path' => 'images/situations/b31e8e9f-7129-42d3-a182-b171f50a1517.webp',
                    'alt' => 'Zájemkyně z bazaru – odkaz na platbu',
                ],
                'options' => [
                    [
                        'name' => 'Odkaz neotevřu a konverzaci rovnou ukončím',
                        'right' => true,
                        'evaluation' => 'Výborně!<br><br><strong>Víte, co všechno na vás podvodnice zkoušela? </strong><br><br>Otázky na stáří sekačky, funkčnost nebo fotku motoru mají vyvolat dojem, že jednáte s poctivým člověkem.<br><br>Příběh o rodičích a zahradě útočí na vaše city. Máte pocit, že děláte dobrý skutek pro milou dceru, což oslabuje vaši přirozenou ostražitost.<br><br>Jakmile dojde na peníze, ze slušné paní je najednou diktátor. Limit 3 minuty vás má vystresovat, abyste ve spěchu zapomněli na veškerá bezpečnostní pravidla.<br><br><strong>Před čím jste zachránili své peníze?</strong> Zaslaný odkaz by vás zavedl na falešnou stránku Zásilkovny. Tam by po vás chtěli CVC kód a číslo karty, aby vám prý „přišly peníze“. Místo příjmu by vám ale ukradli vaše úspory.<br><br><strong>Hlavní pravidlo:</strong> Pro příjem peněz na bazaru stačí vždy jen číslo vašeho bankovního účtu. Jakmile někdo chce údaje z karty nebo vás honí časem, okamžitě konverzaci ukončete.',
                    ],
                    [
                        'name' => 'Kliknu na odkaz a vyplním, co je potřeba',
                        'right' => false,
                        'evaluation' => 'Poplach! Tohle je to nejnebezpečnější rozhodnutí, které vás v reálném životě může během jediné minuty připravit o všechny celoživotní úspory.<br><br>Podvodníci v tomto případě použili propracované metody. Nejdříve vás ukolébali milým zájmem a pak vás bleskově dorazili časovým stresem:<br><br><strong>Maska poctivého kupujícího:</strong> Otázky na funkčnost sekačky nebo detail motoru byly jen sehrané divadlo. Šmejdi dobře vědí, že když se chovají jako běžní, slušní zákazníci, zcela ztratíte ostražitost.<br><br><strong>Citové vydírání:</strong> Příběh o rodičích na zahradě a stížnosti, jak to paní bude muset vyplňovat znovu, zaútočily na vaši dobrotu a ochotu pomoci.<br><br>Tím, že jste do falešného formuláře vyplnili číslo karty, platnost a třímístný <strong>CVC kód</strong>, jste podvodníkům dobrovolně otevřeli své bankovní konto. S těmito údaji mohou ukrást peníze z vašeho účtu.<br><br><strong>Hlavní pravidlo:</strong> Když na internetovém bazaru zboží prodáváte, peníze vám mohou přijít <strong>výhradně na číslo vašeho bankovního účtu</strong>. Nikdy, ale opravdu nikdy nezadávejte údaje ze své karty do odkazů, které vám pošle cizí člověk.<br><br>Zkusme to znovu.',
                    ],
                    [
                        'name' => 'Napíšu zájemkyni, že mi odkaz nefunguje',
                        'right' => false,
                        'evaluation' => 'Zadržte, kapitáne!<br><br>Váš instinkt nezadávat údaje hned je správný, ale odpověď „odkaz nefunguje“ drží komunikaci otevřenou.<br><br>Co by se teď mohlo stát?<br>Druhá strana vám pošle nový odkaz, začne vás navádět dál nebo zvýší tlak, abyste vše rychle dokončili. Podvod tak pokračuje.<br><br>Proč to působí důvěryhodně?<br>Nejprve přichází běžné dotazy, které vypadají jako skutečný zájem. Poté pohodlí: „Všechno už jsem zařídila.“ Nakonec časový tlak: „Máte 3 minuty.“ Kombinace důvěry, emocí a spěchu snížuje vaši pozornost.<br><br>Jaká je realita?<br>Při prodeji nepotřebujete pro přijetí peněz zadávat číslo platební karty, datum platnosti ani CVC kód. Běžná platba může přijít převodem na účet. Pokud někdo chce údaje z karty přes odkaz, je to varovný signál.<br><br>Co vás má zastavit?<br>Odkaz v soukromé zprávě, tlak na rychlost a požadavek na údaje z karty.<br><br>Správný postup:<br>Na odkaz nereagovat, nic nevyplňovat a komunikaci ukončit.<br><br>Zkuste to znovu.',
                    ],
                ],
            ],

            // ===== PÁTÝ herní kámen =====
            [
                'button' => 5,
                'difficulty' => 1,
                'perex' => 'Falešná výhra – SMS',
                'description' => '[[image:situace]]<p>Brzy ráno Vás probudí pípnutí sms na Vašem telefonu. Proberete se a podíváte se na ni: “Gratulujeme! Vyhrál jste balíček zdravotních služeb zdarma. Pro vyzvednutí výhry klikněte zde: lsfdjgh17ONTkjxnf2gfj4</p>',
                'safety_card' => null,
                'image' => [
                    'key' => 'situace',
                    'path' => 'images/situations/afd3797c-1ed6-48bb-80c6-c1bf523deec3.webp',
                    'alt' => 'Falešná výhra – SMS',
                ],
                'options' => [
                    [
                        'name' => 'Odkaz neotevřu a zprávu ignoruji',
                        'right' => true,
                        'evaluation' => 'Výborně!<br><br><strong>Tímto krokem jste prohlédli podvod, který v sází na tyto věci:</strong><br><br><strong>Útok na rozespalost:</strong> Podvodníci záměrně posílají tyto SMS brzy ráno nebo v noci. Spoléhají na to, že vás pípnutí probudí, vy budete ještě rozespalí, vaše pozornost bude oslabená a kliknete na odkaz dřív, než se vám plně nastartuje kritické myšlení.<br><br><strong>Zneužití tématu zdraví:</strong> Šmejdi moc dobře vědí, že zdraví je pro starší lidi prioritou. Sliby „zdravotních služeb zdarma“ nebo „příspěvků na léky“ používají jako tu nejlákavější návnadu.<br><br><strong>Nesmyslný odkaz:</strong> Oficiální instituce (jako vaše pojišťovna nebo lékař) by vám nikdy neposlaly odkaz složený ze změti náhodných znaků a písmen. Vždy by tam byla jejich jasná internetová adresa.<br><br><strong>Před čím jste se zachránili?</strong> Tento podezřelý odkaz by se z vás pokusil buď vylákat osobní a rodná čísla (phishing), nebo by vám do telefonu stáhl virus.<br><br><strong>Hlavní pravidlo:</strong> V online světě platí jeden železný zákon – <strong>nikdy nemůžete vyhrát v soutěži, do které jste se sami vědomě nepřihlásili.</strong>',
                    ],
                    [
                        'name' => 'Na odkaz kliknu a vyzvednu si výhru',
                        'right' => false,
                        'evaluation' => 'Poplach! Tohle je velmi nebezpečný krok, který vás v reálném životě může okamžitě připravit o peníze nebo kontrolu nad telefonem.<br><br><strong>Proč je kliknutí s úmyslem „vyzvednout výhru“ obrovský risk?</strong><br><br>Šmejdi vás schválně probudili brzy ráno. Spoléhají na to, že rozespalý člověk je snazší oběť.<br><br>Ta podivná změť písmen v odkazu je jasný varovný signál. Stránka, na kterou byste se dostali, by po vás pod záminkou „doručení výhry“ chtěla vyplnit osobní údaje, rodné číslo, nebo dokonce zaplatit malý poplatek za poštovné kartou.<br><br>Už samotné kliknutí na takto podezřelý odkaz může do vašeho mobilu tajně stáhnout škodlivý program (virus).<br><br><strong>Hlavní pravidlo:</strong> V online světě platí jeden železný zákon – <strong>nikdy nemůžete vyhrát v soutěži, do které jste se sami vědomě nepřihlásili</strong>. Pokud vám z ničeho nic přijde zpráva o výhře, je to na 100 % podvod.<br><br>Zkusme to znovu.',
                    ],
                    [
                        'name' => 'Na zprávu odpovím a požádám o víc informací.',
                        'right' => false,
                        'evaluation' => 'Zadržte!<br><br>Chtít víc informací a nejprve si situaci ověřit dává sice v běžném životě smysl, ale ve světě podezřelých SMS zpráv je odpověď nebezpečná.<br><br><strong>Co se stane, když na takovou SMS odpovíte?</strong><br><br>Podvodníkům (nebo jejich automatickým rozesílacím systémům) ve vteřině potvrdíte: „Pozor, na tomto čísle žije reálný člověk, který zprávy čte a reaguje na ně.“<br><br>Vaše telefonní číslo okamžitě získá nálepku „aktivní“. Šmejdi si ho mezi sebou nasdílejí nebo ho prodají v databázích dalším podvodníkům. Od té chvíle vás začnou bombardovat mnohem větším množstvím podvodných SMS a falešných telefonátů.<br><br>Tyto hromadné zprávy většinou rozesílají naprogramované počítačové skripty (boti). Žádné lidské odpovědi ani skutečných podrobností se nedočkáte – systém vám buď neodpoví vůbec, nebo vám automaticky pošle ten samý nebezpečný odkaz znovu.<br><br>Nejbezpečnější obrana je na zprávu vůbec neodpovídat a rovnou ji smazat.<br><br>Zkusme to znovu.',
                    ],
                ],
            ],
            [
                'button' => 5,
                'difficulty' => 2,
                'perex' => 'Falešný balíček – aktivace',
                'description' => '<p>Brzy ráno Vás probudí SMS:</p><p>„Vážený pane Nováku, jako dlouhodobý klient jste získal nárok na balíček zdravotních služeb zdarma. Aktivujte si jej zde: www.zdravi-servis.cz/aktivace</p>',
                'safety_card' => null,
                'options' => [
                    [
                        'name' => 'Odkaz neotevřu a zprávu ignoruji',
                        'right' => true,
                        'evaluation' => 'Výborně!<br><br><strong>Tímto rozhodnutím jste prokoukli tři velmi rafinované triky:</strong><br><br>Oslovení „Vážený pane Nováku“  má vyvolat okamžitý pocit, že zpráva je oficiální a adresovaná vám. Podvodníci ale dnes běžně nakupují uniklé databáze z internetu, kde jsou jména spárovaná s telefonními čísly.<br><br>Slib „balíčku za dlouhodobé klientsví“ útočí na emoce. Každého potěší, když ho někdo ocení za věrnost. Šmejdi navíc moc dobře vědí, že téma zdraví je pro lidi prioritou, takže ho používají jako tu nejlákavější návnadu.<br><br>Adresa www.zdravi-servis.cz nevypadá na první pohled jako změť nesmyslných znaků, takže působí celkem důvěryhodně. Je to ale jen maskovaná past, kterou si podvodníci zaregistrovali speciálně pro tento útok.<br><br><strong>Před čím jste zachránili své soukromí a peníze?</strong> Tento odkaz by vás zavedl na falešný zdravotní portál. Pod záminkou „aktivace balíčku“ by z vás buď vytáhli citlivé osobní údaje (rodné číslo, číslo pojištěnce), nebo by vás rovnou přiměli k přihlášení přes Bankovní identitu kvůli údajnému „ověření totožnosti“. Tím by získali plný přístup k vašemu bankovnímu účtu.',
                    ],
                    [
                        'name' => 'Na odkaz kliknu a balíček si aktivuji',
                        'right' => false,
                        'evaluation' => 'Poplach! Tohle rozhodnutí vás v reálném životě může během několika vteřin připravit o přístup k celému bankovnímu účtu.<br><br><strong>Proč je pokus o „aktivaci“ přes tento odkaz obrovský risk?</strong><br><br><strong>Past zvaná Bankovní identita:</strong> Abyste si mohli balíček na falešném webu „aktivovat“, stránka po vás bude pod záminkou ověření totožnosti chtít přihlášení přes <strong>Bankovní identitu</strong> (nebo zadání údajů z karty). Jakmile tam tyto údaje vyplníte, nepřejdete do žádného klientského programu, ale předáte podvodníkům kompletní přístupové údaje do svého bankovního účtu. Šmejdi pak mohou okamžitě ukrást vaše úspory nebo si na vaše jméno vzít úvěr.<br><br>To, že vás SMS oslovila jménem, nebyla náhoda.  Podvodníci dnes běžně nakupují uniklé databáze z internetu, kde jsou jména spárovaná s telefonními čísly. Spolehli se na to, že když uvidíte své jméno, ztratíte ostražitost a uvěříte, že zpráva je oficiální.<br><br>Adresa www.zdravi-servis.cz sice vypadá na první pohled docela běžně, ale nákup reálně znějící domény není problém. Skutečné pojišťovny nebo zdravotní instituce takto náhle bonusy přes textové zprávy nerozdávají.<br><br>Zkusme to znovu.',
                    ],
                    [
                        'name' => 'Na zprávu odpovím a požádám o více informací',
                        'right' => false,
                        'evaluation' => 'Zadržte! Snaha zjistit víc informací a situaci si nejprve ověřit zní logicky. U podvodných SMS zpráv je ale už samotná odpověď rizikem.<br><br><strong>Co se stane, když na takovou SMS odpovíte?</strong><br><br>Podvodníkům (nebo jejich rozesílacím automatům) svou odpovědí ve vteřině potvrdíte to nejdůležitější: „Pozor, toto telefonní číslo funguje a jeho majitel zprávy čte a reaguje na ně.“<br><br>Vaše číslo okamžitě získá větší hodnotu. Šmejdi si ho označí jako „aktivní“ a často ho prodají dalším podvodníkům. Množství útoků cílených na vaše číslo může vzrůstat.<br><br>Skutečné podrobnosti o údajném balíčku se nedozvíte. Buď vám naprogramovaný robot obratem pošle ten samý nebezpečný odkaz znovu, nebo si vás převezme vyškolený manipulátor, který vás začne aktivně přesvědčovat, ať na odkaz kliknete.<br><br>Zkusme to znovu.',
                    ],
                ],
            ],
            [
                'button' => 5,
                'difficulty' => 3,
                'perex' => 'Falešný balíček – časový tlak',
                'description' => '<p>Brzy ráno Vám přijde SMS:<br> „Vážený pane Novák: byl Vám přidělen zdravotní balíček v rámci programu prevence. Pro více informací a aktivaci klikněte zde: www.zdravi-program.cz/overeni“ plus časomíra</p>',
                'safety_card' => null,
                'options' => [
                    [
                        'name' => 'Odkaz neotevřu a zprávu ignoruji',
                        'right' => true,
                        'evaluation' => 'Výborně!<br><br><strong>Tímto rozhodnutím jste prokoukli tři triky podvodníků:</strong><br><br>Výrazy jako „program prevence“ nebo „potvrzení“ mají vyvolat dojem, že komunikujete se státem, ministerstvem nebo svou pojišťovnou. Podvodníci téma zdraví zneužívají záměrně, protože vědí, že na něj lidé nejvíce slyší.<br><br>Slovní spojení „byl Vám přidělen“ ve vás má vyvolat pocit, že na balíček máte automatické právo a byla by obrovská škoda o takovou výhodu přijít. Tento trik má za úkol oslabit vaši přirozenou ostražitost.<br><br>Web www.zdravi-program.cz sice na první pohled nevypadá jako změť nesmyslných znaků, ale je to jen rychlá zástěrka, kterou si podvodníci sami zaregistrovali. Skutečné instituce takto náhle bonusy přes SMS s odkazem k ověření nikdy nerozdávají.<br><br><strong>Před čím jste zachránili své soukromí a peníze?</strong> Tento odkaz by vás zavedl na falešný web. Pod záminkou „ověření a aktivace“ by z vás buď vytáhli citlivé osobní údaje, nebo by vás rovnou přiměli k přihlášení přes Bankovní identitu. Tím by podvodníci získali plný přístup k vašemu bankovnímu účtu a mohli by ho vykrást.<br><br>Pokud máte jakékoli pochybnosti, vždy si nabídku ověřte sami – například zavoláním na oficiální infolinku své pojišťovny.',
                    ],
                    [
                        'name' => 'Na odkaz kliknu a balíček si aktivuji',
                        'right' => false,
                        'evaluation' => 'Poplach! Tohle rozhodnutí vás v reálném životě může během několika málo vteřin připravit o veškeré úspory na bankovním účtu.<br><br><strong>Proč je pokus o „aktivaci“ přes tento odkaz obrovský risk?</strong><br><br>Abyste si mohli balíček na falešném webu „aktivovat“ a „ověřit svou totožnost“, stránka po vás bude chtít přihlášení přes <strong>Bankovní identitu</strong>. Jakmile tam své údaje zadáte, nepřejdete do žádného zdravotního programu, ale předáte podvodníkům kompletní údaje od svého bankovnictví. Šmejdi pak mohou okamžitě ukrást vaše peníze nebo si na vaše jméno vzít úvěr.<br><br>Výrazy jako „program prevence“ nebo „potvrzení“ mají vyvolat dojem, že komunikujete se státní institucí nebo pojišťovnou. Podvodníci téma zdraví zneužívají záměrně, protože vědí, že na něj lidé nejvíce slyší a mají tendenci takovým zprávám věřit.<br><br>Slovní spojení „byl Vám přidělen“ ve vás má vyvolat pocit, že na balíček máte automatické právo a byla by obrovská škoda o takovou výhodu přijít.<br><br><strong>Hlavní pravidlo:</strong> Své přihlašovací údaje do banky nebo Bankovní identitu používejte <strong>výhradně tehdy</strong>, když do bankovnictví vstupujete vy sami – například přes oficiální staženou aplikaci v telefonu nebo ručně zadanou adresu banky v prohlížeči. Nikdy se nepřihlašujte přes odkazy, které vám přišly v SMS.<br><br>Zkusme to znovu.',
                    ],
                    [
                        'name' => 'Na zprávu odpovím a požádám o více informací',
                        'right' => false,
                        'evaluation' => 'Zadržte! Snaha zjistit víc informací a situaci si nejprve ověřit zní sice logicky, ale ve světě podezřelých SMS zpráv je odpověď nebezpečná.<br><br><strong>Co se stane, když na takovou SMS odpovíte?</strong><br><br>Automatickým rozesílacím systémům (botům), které tyto zprávy hromadně rozesílají, okamžitě potvrdíte: „Pozor, toto telefonní číslo funguje, jeho majitel zprávy čte a reaguje na ně.“<br><br>Vaše číslo v tu ránu získá v obrovskou hodnotu. Šmejdi si ho označí jako „aktivní“ a často ho prodají dalším podvodníkům..<br><br>Žádné skutečné podrobnosti o „programu prevence“ se nedozvíte. Buď vám nikdo neodpoví nebo se vám ozve podvodník, který bude zvyšovat nátlak na vás.<br><br>Nejbezpečnější obrana je na zprávu vůbec neodpovídat a rovnou ji smazat.<br><br>Zkusme to znovu.',
                    ],
                ],
            ],
        ];
    }
};
