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
 * Naplní Ostrov klamavých zpráv – 5 herních kamenů (tlačítek),
 * každý se třemi situacemi o obtížnosti 1–3 (15 otázek celkem).
 * Otázky jsou verze 3, bez skupiny.
 */
return new class extends Migration {
    public function up(): void
    {
        $island = Island::query()->where('image', 'deceptive_news.webp')->first();

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
        $island = Island::query()->where('image', 'deceptive_news.webp')->first();

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
                'perex' => 'Časopis – článek o bolesti kloubů',
                'description' => '[[image:situace]]<p>Při listování časopisem narazíte na článek:</p><p>„Paní Marie objevila jednoduchý způsob, jak se zbavit bolesti kloubů.<br>Už po 7 dnech se cítila jako znovuzrozená.“</p><p>V textu stojí:<br>„Tuto přírodní metodu může vyzkoušet každý senior.“</p><p>Pod článkem je obrázek usměvavé seniorky a nápis:<br>„Doporučujeme čtenářům 60+.“</p><p>Na konci článku je uvedeno:<br>„Pro více informací volejte bezplatnou linku 800 123 456.“</p>',
                'safety_card' => null,
                'image' => [
                    'key' => 'situace',
                    'path' => 'images/situations/b189c791-0898-4a82-b3be-242b61c0060e.webp',
                    'alt' => 'Časopis – článek o bolesti kloubů',
                ],
                'options' => [
                    [
                        'name' => 'Zavolám na bezplatnou linku',
                        'right' => false,
                        'evaluation' => 'Pozor, tudy cesta nevede.<br><br>Na druhé straně linky nečeká lékař, ale vyškolený prodejce.<br><br>Jeho jediným cílem je využít vašeho strachu o zdraví a vmanipulovat vás do nákupu předraženého a neúčinného přípravku.<br><br>Skutečné léky vám musí předepsat lékař, ne prodejce po telefonu.<br><br>Zkuste to znovu.',
                    ],
                    [
                        'name' => 'Zkusím si produkt ověřit jinde',
                        'right' => true,
                        'evaluation' => 'Snaha ověřovat informace je skvělá, ale pozor.<br><br>Tyto podvodné články jsou často tak rafinované, že když zadáte název léku do vyhledávače, vyběhnou na vás desítky dalších falešných stránek a recenzí, které produkt chválí.<br><br>Nejbezpečnější je takové sliby rovnou ignorovat.',
                    ],
                    [
                        'name' => 'Článek ignoruji, je to reklama',
                        'right' => true,
                        'evaluation' => 'Skvělá práce!<br><br>Správně jste rozpoznal, že se jedná o takzvaný textový inzerát, který se pouze maskuje jako běžný článek.<br><br>Hraje na city a nabízí zázračná řešení tam, kde moderní medicína postupuje pomalu.<br><br>Ignorovat takové nabídky je ta nejlepší obrana.',
                    ],
                ],
            ],
            [
                'button' => 1,
                'difficulty' => 2,
                'perex' => 'Časopis – článek s odborníkem',
                'description' => '[[image:situace]]<p>Při listování časopisem narazíte na článek:</p><p>„Lékaři upozorňují: bolest kloubů nemusí být běžnou součástí stáří.“</p><p>V textu stojí:<br>„Podle odborníka MUDr. Petra H. může seniorům pomoci přírodní kúra Flexi Senior.“</p><p>Dole pod článkem je malý rámeček:<br>„Speciální nabídka pro čtenáře: první balení za zvýhodněnou cenu.“</p><p>Pod rámečkem je uvedeno:<br>„Objednávejte telefonicky na čísle 800 123 456. Při objednávce nahlaste kód SENIOR.“</p><p>Článek není jasně označený jako reklama.</p>',
                'safety_card' => null,
                'image' => [
                    'key' => 'situace',
                    'path' => 'images/situations/f5f91f90-5963-4238-a01d-06200057dc28.webp',
                    'alt' => 'Časopis – článek s odborníkem',
                ],
                'options' => [
                    [
                        'name' => 'Zavolám na bezplatnou linku',
                        'right' => false,
                        'evaluation' => 'Pozor, tudy cesta nevede.<br><br>Na druhé straně linky nečeká lékař, ale vyškolený prodejce.<br><br>Jeho jediným cílem je využít vašeho strachu o zdraví a vmanipulovat vás do nákupu předraženého a neúčinného přípravku.<br><br>Skutečné léky vám musí předepsat lékař, ne prodejce po telefonu.<br><br>Zkuste to znovu.',
                    ],
                    [
                        'name' => 'Zkusím si produkt ověřit jinde',
                        'right' => true,
                        'evaluation' => 'Snaha ověřovat informace je skvělá, ale pozor.<br><br>Tyto podvodné články jsou často tak rafinované, že když zadáte název léku do vyhledávače, vyběhnou na vás desítky dalších falešných stránek a recenzí, které produkt chválí.<br><br>Nejbezpečnější je takové sliby rovnou ignorovat.',
                    ],
                    [
                        'name' => 'Článek ignoruji, je to reklama',
                        'right' => true,
                        'evaluation' => 'Skvělá práce!<br><br>Správně jste rozpoznal, že se jedná o takzvaný textový inzerát, který se pouze maskuje jako běžný článek.<br><br>Hraje na city a nabízí zázračná řešení tam, kde moderní medicína postupuje pomalu.<br><br>Ignorovat takové nabídky je ta nejlepší obrana.',
                    ],
                ],
            ],
            [
                'button' => 1,
                'difficulty' => 3,
                'perex' => 'Časopis – přírodní kúra Flexilan',
                'description' => '[[image:situace]]<p>Při listování časopisem narazíte na článek:</p><p>„Lékaři upozorňují: bolest kloubů nemusí být běžnou součástí stáří.“</p><p>V textu stojí:<br>„Podle odborníka MUDr. Petra H. může seniorům pomoci přírodní kúra Flexi Senior.“</p><p>Dole pod článkem je malý rámeček:<br>„Speciální nabídka pro prvních 50 čtenářů: první balení za zvýhodněnou cenu. Nabídka platí pouze tento týden.“</p><p>Pod rámečkem je uvedeno:<br>„Objednávejte telefonicky na čísle 800 123 456. Při objednávce nahlaste kód SENIOR.“</p><p>Článek není označený jako reklama. <strong>Časomíra:</strong> 1 minuta</p>',
                'safety_card' => null,
                'image' => [
                    'key' => 'situace',
                    'path' => 'images/situations/34d680ad-2949-49eb-833e-e3b6f204a17d.webp',
                    'alt' => 'Časopis – přírodní kúra Flexilan',
                ],
                'options' => [
                    [
                        'name' => 'Zavolám na bezplatnou linku',
                        'right' => false,
                        'evaluation' => 'Pozor, tudy cesta nevede.<br><br>Na druhé straně linky nečeká lékař, ale vyškolený prodejce.<br><br>Jeho jediným cílem je využít vašeho strachu o zdraví a vmanipulovat vás do nákupu předraženého a neúčinného přípravku.<br><br>Skutečné léky vám musí předepsat lékař, ne prodejce po telefonu.<br><br>Zkuste to znovu.',
                    ],
                    [
                        'name' => 'Zkusím si produkt ověřit jinde',
                        'right' => true,
                        'evaluation' => 'Snaha ověřovat informace je skvělá, ale pozor.<br><br>Tyto podvodné články jsou často tak rafinované, že když zadáte název léku do vyhledávače, vyběhnou na vás desítky dalších falešných stránek a recenzí, které produkt chválí.<br><br>Nejbezpečnější je takové sliby rovnou ignorovat.',
                    ],
                    [
                        'name' => 'Článek ignoruji, je to reklama',
                        'right' => true,
                        'evaluation' => 'Skvělá práce!<br><br>Správně jste rozpoznal, že se jedná o takzvaný textový inzerát, který se pouze maskuje jako běžný článek.<br><br>Hraje na city a nabízí zázračná řešení tam, kde moderní medicína postupuje pomalu.<br><br>Ignorovat takové nabídky je ta nejlepší obrana.',
                    ],
                ],
            ],

            // ===== DRUHÝ herní kámen =====
            [
                'button' => 2,
                'difficulty' => 1,
                'perex' => 'Řetězový e-mail o důchodech',
                'description' => '[[image:situace]]<p>Do e-mailu vám přijde zpráva od známé:</p><p>„POZOR!!! Od příštího měsíce vláda schválila nový poplatek pro důchodce. V televizi o tom mlčí.“</p><p>Na konci zprávy stojí:</p><p>„Pošlete to všem známým, než to smažou.“</p>',
                'safety_card' => null,
                'image' => [
                    'key' => 'situace',
                    'path' => 'images/situations/6ec55d31-4155-433d-8c7b-45733d6f43d9.webp',
                    'alt' => 'Řetězový e-mail o důchodech',
                ],
                'options' => [
                    [
                        'name' => 'Zprávu hned přepošlu známým',
                        'right' => false,
                        'evaluation' => 'Zadržte!<br><br>Přesně tohle tvůrci e-mailů chtějí.<br><br>Tento text obsahuje <strong>šokující a senzační zprávu, která má vyvolat strach, vztek a nespravedlnost</strong>.<br><br>Je záměrně napsaná tak nečekaně, aby vás okamžitě emočně zasáhla a vy jste v panice klikli na tlačítko přeposlat.<br><br>Tím, že zprávu bez přemýšlení přepošlete, se nevědomky stáváte šiřitelem lží a zbytečné paniky mezi svými blízkými.<br><br>Zkuste to znovu.',
                    ],
                    [
                        'name' => 'Zkusím si informaci nejdřív ověřit.',
                        'right' => true,
                        'evaluation' => 'Ověřování je skvělý nápad.<br><br>Ale pozor - podvodníci na internetu schválně vytvářejí desítky falešných stránek.<br><br>Když zadáte text do vyhledávače, snadno spadnete do pasti, kde si lidé v diskusích tuto lež vzájemně potvrzují.<br><br>Odborníci proto radí jednoduché pravidlo<strong> tří zdrojů</strong>: zpráva je pravdivá, když ji nezávisle na sobě potvrzují aspoň tři seriózní zpravodajské weby.<br><br>U důchodů se vždy podívejte přímo na oficiální stránky ministerstva (mpsv.cz).',
                    ],
                    [
                        'name' => 'Zprávu smažu a dál nešířím',
                        'right' => true,
                        'evaluation' => 'Vynikající!<br><br>Tento e-mail zaútočil na vaše emoce pomocí strachu, využil hru na spiknutí o mlčení médií a přidal naléhavou výzvu k akci, abyste jednali ve spěchu a bez přemýšlení.<br><br>Správně jste vyhodnotili, že vládní poplatky se neschvalují tajně ze dne na den.<br><br>Smazáním zprávy jste ochránili klid svých známých i svůj vlastní',
                    ],
                ],
            ],
            [
                'button' => 2,
                'difficulty' => 2,
                'perex' => 'Přeposlaný e-mail o důchodech',
                'description' => '[[image:situace]]<p>Do e-mailu vám přijde přeposlaná zpráva od známé:</p><p>„Ahoj, posílám informaci od kamarádky, která pracuje na úřadě. Prý se chystá změna ve vyplácení důchodů a někteří lidé mohou přijít o část peněz.“</p><p>Na konci zprávy stojí:</p><p>„Raději to pošli dál všem seniorům, co znáš,, ať se na to připraví.“</p>',
                'safety_card' => null,
                'image' => [
                    'key' => 'situace',
                    'path' => 'images/situations/a0045212-69d9-4439-a888-198e31566eed.webp',
                    'alt' => 'Přeposlaný e-mail o důchodech',
                ],
                'options' => [
                    [
                        'name' => 'Zprávu hned přepošlu známým',
                        'right' => false,
                        'evaluation' => 'Zadržte!<br><br>Přesně tohle tvůrci e-mailů chtějí.<br><br>Tento text obsahuje <strong>šokující a senzační zprávu, která má vyvolat strach, vztek a nespravedlnost</strong>.<br><br>Je záměrně napsaná tak nečekaně, aby vás okamžitě emočně zasáhla a vy jste v panice klikli na tlačítko přeposlat.<br><br>Tím, že zprávu bez přemýšlení přepošlete, se nevědomky stáváte šiřitelem lží a zbytečné paniky mezi svými blízkými.<br><br>Zkuste to znovu.',
                    ],
                    [
                        'name' => 'Zkusím si informaci nejdřív ověřit.',
                        'right' => true,
                        'evaluation' => 'Ověřování je skvělý nápad.<br><br>Ale pozor - podvodníci na internetu schválně vytvářejí desítky falešných stránek.<br><br>Když zadáte text do vyhledávače, snadno spadnete do pasti, kde si lidé v diskusích tuto lež vzájemně potvrzují.<br><br>Odborníci proto radí jednoduché pravidlo<strong> tří zdrojů</strong>: zpráva je pravdivá, když ji nezávisle na sobě potvrzují aspoň tři seriózní zpravodajské weby.<br><br>U důchodů se vždy podívejte přímo na oficiální stránky ministerstva (mpsv.cz).',
                    ],
                    [
                        'name' => 'Zprávu smažu a dál nešířím',
                        'right' => true,
                        'evaluation' => 'Vynikající!<br><br>Tento e-mail zaútočil na vaše emoce pomocí strachu, využil hru na spiknutí o mlčení médií a přidal naléhavou výzvu k akci, abyste jednali ve spěchu a bez přemýšlení.<br><br>Správně jste vyhodnotili, že vyplácení důchodů se neschvaluje tajně ze dne na den.<br><br>Smazáním zprávy jste ochránili klid svých známých i svůj vlastní',
                    ],
                ],
            ],
            [
                'button' => 2,
                'difficulty' => 3,
                'perex' => 'Naléhavý e-mail o důchodech',
                'description' => '[[image:situace]]<p>Do e-mailu vám přijde přeposlaná zpráva od známé:</p><p>„Už je rozhodnuto. Od července se změní vyplácení důchodů a mnoho seniorů o peníze přijde. Tuto informaci mám od mého známého z úřadu, ale veřejně se o tom nesmí mluvit.“</p><p>Dále ve zprávě stojí:</p><p>„Média mlčí a politici to tají. Přepošlete tento e-mail rychle alespoň 10 lidem, dokud není pozdě.“</p>',
                'safety_card' => null,
                'image' => [
                    'key' => 'situace',
                    'path' => 'images/situations/3a9b3ea1-7f66-42f7-8abb-ee3cc86f127d.webp',
                    'alt' => 'Naléhavý e-mail o důchodech',
                ],
                'options' => [
                    [
                        'name' => 'Zprávu hned přepošlu známým',
                        'right' => false,
                        'evaluation' => 'Zadržte!<br><br>Přesně tohle tvůrci e-mailů chtějí.<br><br>Tento text obsahuje <strong>šokující a senzační zprávu, která má vyvolat strach, vztek a nespravedlnost</strong>.<br><br>Je záměrně napsaná tak nečekaně, aby vás okamžitě emočně zasáhla a vy jste v panice klikli na tlačítko přeposlat.<br><br>Tím, že zprávu bez přemýšlení přepošlete, se nevědomky stáváte šiřitelem lží a zbytečné paniky mezi svými blízkými.<br><br>Zkuste to znovu.',
                    ],
                    [
                        'name' => 'Zkusím si informaci nejdřív ověřit.',
                        'right' => true,
                        'evaluation' => 'Ověřování je skvělý nápad.<br><br>Ale pozor - podvodníci na internetu schválně vytvářejí desítky falešných stránek.<br><br>Když zadáte text do vyhledávače, snadno spadnete do pasti, kde si lidé v diskusích tuto lež vzájemně potvrzují.<br><br>Odborníci proto radí jednoduché pravidlo<strong> tří zdrojů</strong>: zpráva je pravdivá, když ji nezávisle na sobě potvrzují aspoň tři seriózní zpravodajské weby.<br><br>U důchodů se vždy podívejte přímo na oficiální stránky ministerstva (mpsv.cz).',
                    ],
                    [
                        'name' => 'Zprávu smažu a dál nešířím',
                        'right' => true,
                        'evaluation' => 'Vynikající!<br><br>Tento e-mail zaútočil na vaše emoce pomocí strachu, využil hru na spiknutí o mlčení médií a přidal naléhavou výzvu k akci, abyste jednali ve spěchu a bez přemýšlení.<br><br>Správně jste vyhodnotili, že vyplácení důchodů se neschvaluje tajně ze dne na den.<br><br>Smazáním zprávy jste ochránili klid svých známých i svůj vlastní',
                    ],
                ],
            ],

            // ===== TŘETÍ herní kámen =====
            [
                'button' => 3,
                'difficulty' => 1,
                'perex' => 'Falešný zpravodajský web',
                'description' => '[[image:situace]]<p>Při prohlížení internetu se vám zobrazí článek, který vypadá jako zpráva:</p><p>„ŠOKUJÍCÍ ZMĚNA: Senioři mohou od příštího měsíce přijít o část příspěvků.“</p><p>Web se jmenuje:<br>„České zprávy 24“</p><p>Adresa stránky je:<br>www.ceske-zpravy24-info.com</p><p>V článku stojí:<br>„Média o tom zatím mlčí.“</p>',
                'safety_card' => null,
                'image' => [
                    'key' => 'situace',
                    'path' => 'images/situations/bc402e93-2cc6-4eca-b517-068859eefed6.webp',
                    'alt' => 'Falešný zpravodajský web',
                ],
                'options' => [
                    [
                        'name' => 'Článek nasdílím, ať o tom ostatní vědí',
                        'right' => false,
                        'evaluation' => 'Zadržte!<br><br>Přesně na to podvodníci čekají.<br><br>Tento web <strong>záměrně napodobuje názvy důvěryhodných a známých médií</strong>, aby ve vás vyvolal pocit, že čtete skutečné zprávy.<br><br>Titulek útočí na vaše emoce (strach a nejistotu).<br><br>Vždy se podívejte na samotnou adresu webu (URL) úplně nahoře v prohlížeči.<br><br>Pokud je podezřele dlouhá, zkomolená nebo končí divnou koncovkou, buďte obezřetní. Pravděpodobně jde o podvodný web.',
                    ],
                    [
                        'name' => 'Zkusím si informaci nejdřív ověřit.',
                        'right' => true,
                        'evaluation' => 'Snaha ověřovat informace je chvályhodná!<br><br>Má to ale háček.<br><br>Podvodníci jsou rafinovaní a často si zakládají celou síť podobných stránek. Když zadáte tento vymyšlený titulek do vyhledávače, snadno narazíte na další falešné články, které se vás budou snažit přesvědčit, že je to pravda.<br><br>Odborníci proto radí <strong>pravidlo tří zdrojů</strong>: zprávu berte vážně jen tehdy, když ji nezávisle potvrdí alespoň tři velká a obecně známá média (např. Česká televize, Český rozhlas atd.).<br><br>U sociálních dávek pak zamiřte rovnou na oficiální web ministerstva.',
                    ],
                    [
                        'name' => 'Stránku zavřu, web vypadá podezřele',
                        'right' => true,
                        'evaluation' => 'Vynikající!<br><br>Tento web záměrně napodobuje vzhled seriózního zpravodajství, aby zneužil vaši důvěru.<br><br>Jeho titulek měl za úkol ve vás vyvolat strach a obavy z budoucnosti.<br><br>Vy jste to odhalili.<br><br>Skvělá práce',
                    ],
                ],
            ],
            [
                'button' => 3,
                'difficulty' => 2,
                'perex' => 'Falešný zpravodajský web – „nový zákon"',
                'description' => '[[image:situace]]<p>Při prohlížení internetu se vám zobrazí článek, který vypadá jako zpráva:</p><p>„Nový zákon změní život seniorů. Někteří mohou přijít o tisíce korun.“</p><p>Web se jmenuje:<br>„České zprávy 24“</p><p>Adresa stránky je:<br>www.ct24-zpravy-dnes.online</p><p>V článku stojí:<br>„Podle zdrojů z ministerstva se změna dotkne lidí, kteří si včas nezkontrolují své údaje.“</p><p>Pod článkem jsou komentáře:<br>„Tohle mi poslala kamarádka, je to pravda.“<br>„V televizi o tom mlčí.“<br>„Sdílejte, ať se to lidé dozví.“</p>',
                'safety_card' => null,
                'image' => [
                    'key' => 'situace',
                    'path' => 'images/situations/e6b2994b-7d18-4408-897d-15bb2387ec35.webp',
                    'alt' => 'Falešný zpravodajský web – „nový zákon"',
                ],
                'options' => [
                    [
                        'name' => 'Článek nasdílím, ať o tom ostatní vědí',
                        'right' => false,
                        'evaluation' => 'Zadržte!<br><br>Přesně na to podvodníci čekají.<br><br>Tento web <strong>záměrně napodobuje názvy důvěryhodných a známých médií</strong>, aby ve vás vyvolal pocit, že čtete skutečné zprávy.<br><br>Titulek útočí na vaše emoce (strach a nejistotu).<br><br>Vždy se podívejte na samotnou adresu webu (URL) úplně nahoře v prohlížeči.<br><br>Pokud je podezřele dlouhá, zkomolená nebo končí divnou koncovkou, buďte obezřetní. Pravděpodobně jde o podvodný web.',
                    ],
                    [
                        'name' => 'Zkusím si informaci nejdřív ověřit.',
                        'right' => true,
                        'evaluation' => 'Snaha ověřovat informace je chvályhodná!<br><br>Má to ale háček.<br><br>Podvodníci jsou rafinovaní a často si zakládají celou síť podobných stránek. Když zadáte tento vymyšlený titulek do vyhledávače, snadno narazíte na další falešné články, které se vás budou snažit přesvědčit, že je to pravda.<br><br>Odborníci proto radí <strong>pravidlo tří zdrojů</strong>: zprávu berte vážně jen tehdy, když ji nezávisle potvrdí alespoň tři velká a obecně známá média (např. Česká televize, Český rozhlas atd.).<br><br>U sociálních dávek pak zamiřte rovnou na oficiální web ministerstva.',
                    ],
                    [
                        'name' => 'Stránku zavřu, web vypadá podezřele',
                        'right' => true,
                        'evaluation' => 'Vynikající!<br><br>Tento web záměrně napodobuje vzhled seriózního zpravodajství, aby zneužil vaši důvěru.<br><br>Jeho titulek měl za úkol ve vás vyvolat strach a obavy z budoucnosti.<br><br>Vy jste to odhalili.<br><br>Skvělá práce',
                    ],
                ],
            ],
            [
                'button' => 3,
                'difficulty' => 3,
                'perex' => 'Falešný zpravodajský web – časový tlak',
                'description' => '[[image:situace]]<p>Při prohlížení internetu se vám zobrazí článek, který vypadá jako zpráva:</p><p>„Nový zákon změní život seniorů. Někteří mohou už tento měsíc přijít o tisíce korun.“</p><p>Web se jmenuje: „České zprávy 24“</p><p>Adresa stránky je:<br>www.ct24-zpravy-dnes.online</p><p>V článku stojí:<br>„Podle zdrojů z ministerstva se změna dotkne lidí, kteří si včas nezkontrolují své údaje.“</p><p>Pod článkem jsou komentáře:<br>„Tohle mi poslala kamarádka, je to pravda.“<br>„V televizi o tom mlčí.“<br>„Sdílejte, ať se to lidé dozví.“</p><p><strong>Časomíra:</strong> 1 minuta</p>',
                'safety_card' => null,
                'image' => [
                    'key' => 'situace',
                    'path' => 'images/situations/5195f753-772a-4abc-8f52-5d290cacc91d.webp',
                    'alt' => 'Falešný zpravodajský web – časový tlak',
                ],
                'options' => [
                    [
                        'name' => 'Článek nasdílím, ať o tom ostatní vědí',
                        'right' => false,
                        'evaluation' => 'Zadržte!<br><br>Přesně na to podvodníci čekají.<br><br>Tento web <strong>záměrně napodobuje názvy důvěryhodných a známých médií</strong>, aby ve vás vyvolal pocit, že čtete skutečné zprávy.<br><br>Titulek útočí na vaše emoce (strach a nejistotu).<br><br>Vždy se podívejte na samotnou adresu webu (URL) úplně nahoře v prohlížeči.<br><br>Pokud je podezřele dlouhá, zkomolená nebo končí divnou koncovkou, buďte obezřetní. Pravděpodobně jde o podvodný web.',
                    ],
                    [
                        'name' => 'Zkusím si informaci nejdřív ověřit.',
                        'right' => true,
                        'evaluation' => 'Snaha ověřovat informace je chvályhodná!<br><br>Má to ale háček.<br><br>Podvodníci jsou rafinovaní a často si zakládají celou síť podobných stránek. Když zadáte tento vymyšlený titulek do vyhledávače, snadno narazíte na další falešné články, které se vás budou snažit přesvědčit, že je to pravda.<br><br>Odborníci proto radí <strong>pravidlo tří zdrojů</strong>: zprávu berte vážně jen tehdy, když ji nezávisle potvrdí alespoň tři velká a obecně známá média (např. Česká televize, Český rozhlas atd.).<br><br>U sociálních dávek pak zamiřte rovnou na oficiální web ministerstva.',
                    ],
                    [
                        'name' => 'Stránku zavřu, web vypadá podezřele',
                        'right' => true,
                        'evaluation' => 'Vynikající!<br><br>Tento web záměrně napodobuje vzhled seriózního zpravodajství, aby zneužil vaši důvěru.<br><br>Jeho titulek měl za úkol ve vás vyvolat strach a obavy z budoucnosti.<br><br>Vy jste to odhalili.<br><br>Skvělá práce',
                    ],
                ],
            ],

            // ===== ČTVRTÝ herní kámen =====
            [
                'button' => 4,
                'difficulty' => 1,
                'perex' => 'FB příspěvek – prázdné regály',
                'description' => '[[image:situace]]<p>Při prohlížení Facebooku se vám zobrazí příspěvek s fotkou prázdného regálu v obchodě.</p><p>Text příspěvku:</p><p>„Takto to dnes vypadá v supermarketu. Zboží mizí z regálů a nikdo o tom nemluví.“</p><p>Pod příspěvkem stojí:</p><p>„Sdílejte, ať se lidé připraví.“</p>',
                'safety_card' => null,
                'image' => [
                    'key' => 'situace',
                    'path' => 'images/situations/8bac8beb-a880-4a21-8868-3463997ad7be.webp',
                    'alt' => 'FB příspěvek – prázdné regály',
                ],
                'options' => [
                    [
                        'name' => 'Příspěvek nasdílím dál',
                        'right' => false,
                        'evaluation' => 'Zadržte!<br><br>Přesně to je cílem autora. Tato šokující fotka útočí na vaše nejzákladnější emoce – strach, že nebude dost jídla.<br><br>Vytvořit takovou lež je dnes, v době umělé inteligence (AI), nesmírně jednoduché.<br><br>Podvodník už nemusí ani fotit prázdný regál pozdě večer. Stačí, když počítači zadá, co chce vidět, a umělá inteligence mu během pár vteřin vygeneruje dokonale realistický obrázek zdevastovaného obchodu.<br><br>A proč to lidé dělají? Buď chtějí záměrně vyvolat paniku, nebo chtějí na své stránky přitáhnout tisíce lidí, protože ze zvědavých kliknutí mají peníze z reklamy.<br><br>Sdílením jim jen pomáháte. Zkuste to znovu.',
                    ],
                    [
                        'name' => 'Zkusím si v komentářích ověřit, jestli to lidé potvrzují.',
                        'right' => false,
                        'evaluation' => 'Snaha ověřit si situaci je správná, ale <strong>komentáře na sociálních sítích jsou tou nejméně bezpečnou cestou</strong>.<br><br>Podvodné příspěvky často přitahují další vyděšené lidi nebo falešné internetové profily (tzv. boty), kteří lež v diskuzi záměrně potvrzují.<br><br>Vzniká tak falešný dojem, že se to děje všude. Informace vždy ověřujte jen v seriózním zpravodajství.<br><br>Zkuste to znovu.',
                    ],
                    [
                        'name' => 'Příspěvku nevěřím',
                        'right' => true,
                        'evaluation' => 'Vynikající!<br><br>Vaše intuice a nedůvěra byly zcela na místě.<br><br>Tento příspěvek útočil na lidský strach z nedostatku a zneužil hru na spiknutí tvrzením, že ‚nikdo o tom nemluví‘.<br><br>V dnešní době umělé inteligence (AI) je nesmírně snadné vytvořit jakoukoliv falešnou fotku nebo video za pár vteřin.<br><br>Autoři to dělají buď proto, aby záměrně šířili paniku, nebo aby na šokující obsah přitáhli lidi a vydělali na reklamě.<br><br>Tím, že jste si zachoval chladnou hlavu a zprávu ignoroval, jste nad nimi zvítězili.<br><br>Skvělá práce.',
                    ],
                ],
            ],
            [
                'button' => 4,
                'difficulty' => 2,
                'perex' => 'FB příspěvek – Kaufland a nedostatek',
                'description' => '[[image:situace]]<p>Při prohlížení Facebooku se vám zobrazí příspěvek s fotkou prázdných regálů v obchodě.</p><p>Text příspěvku:</p><p>„Takto to teď vypadá v Kauflandu. Mouka, olej a cukr mizí z obchodů.“</p><p>Dále v příspěvku stojí:</p><p>„Moje známá tam pracuje a říkala, že zásoby se odvážejí pryč. Pro Čechy brzy nic nezůstane.“</p><p>Fotka nemá uvedené datum ani místo pořízení.</p>',
                'safety_card' => null,
                'image' => [
                    'key' => 'situace',
                    'path' => 'images/situations/d745eefb-ab1c-45e6-9ecb-84a967354beb.webp',
                    'alt' => 'FB příspěvek – Kaufland a nedostatek',
                ],
                'options' => [
                    [
                        'name' => 'Příspěvek nasdílím dál',
                        'right' => false,
                        'evaluation' => 'Zadržte!<br><br>Přesně to je cílem autora. Tato šokující fotka útočí na vaše nejzákladnější emoce – strach, že nebude dost jídla.<br><br>Vytvořit takovou lež je dnes, v době umělé inteligence (AI), nesmírně jednoduché.<br><br>Podvodník už nemusí ani fotit prázdný regál pozdě večer. Stačí, když počítači zadá, co chce vidět, a umělá inteligence mu během pár vteřin vygeneruje dokonale realistický obrázek zdevastovaného obchodu.<br><br>A proč to lidé dělají? Buď chtějí záměrně vyvolat paniku, nebo chtějí na své stránky přitáhnout tisíce lidí, protože ze zvědavých kliknutí mají peníze z reklamy.<br><br>Sdílením jim jen pomáháte.',
                    ],
                    [
                        'name' => 'Zkusím si v komentářích ověřit, jestli to lidé potvrzují.',
                        'right' => false,
                        'evaluation' => 'Snaha ověřit si situaci je správná, ale <strong>komentáře na sociálních sítích jsou tou nejméně bezpečnou cestou</strong>.<br><br>Podvodné příspěvky často přitahují další vyděšené lidi nebo falešné internetové profily (tzv. boty), kteří lež v diskuzi záměrně potvrzují.<br><br>Vzniká tak falešný dojem, že se to děje všude. Informace vždy ověřujte jen v seriózním zpravodajství.<br><br>Zkuste to znovu.',
                    ],
                    [
                        'name' => 'Příspěvku nevěřím',
                        'right' => true,
                        'evaluation' => 'Vynikající!<br><br>Vaše intuice a nedůvěra byly zcela na místě.<br><br>Tento příspěvek útočil na lidský strach z nedostatku a zneužil hru na spiknutí tvrzením, že ‚nikdo o tom nemluví‘.<br><br>V dnešní době umělé inteligence (AI) je nesmírně snadné vytvořit jakoukoliv falešnou fotku nebo video za pár vteřin.<br><br>Autoři to dělají buď proto, aby záměrně šířili paniku, nebo aby na šokující obsah přitáhli lidi a vydělali na reklamě.<br><br>Tím, že jste si zachoval chladnou hlavu a zprávu ignoroval, jste nad nimi zvítězili.<br><br>Skvělá práce.',
                    ],
                ],
            ],
            [
                'button' => 4,
                'difficulty' => 3,
                'perex' => 'FB příspěvek – konspirace o zásobách',
                'description' => '[[image:situace]]<p>Při prohlížení Facebooku se vám zobrazí příspěvek s fotkou prázdných regálů v obchodě.</p><p>Text příspěvku:</p><p>„Takto to dnes vypadá v Kauflandu. Mouka, olej a cukr mizí z regálů. Zásoby se odvážejí do zahraničí a pro Čechy brzy nic nezůstane.“</p><p>Pod příspěvkem jsou komentáře:</p><p>„U nás už taky skoro nic není.“<br>„Rychle nakupujte zásoby.“<br>„Sdílejte, než to smažou.“</p><p>Fotka nemá uvedené datum ani přesné místo pořízení.</p>',
                'safety_card' => null,
                'image' => [
                    'key' => 'situace',
                    'path' => 'images/situations/d84e2460-3040-46cd-baa7-93b1484e88a2.webp',
                    'alt' => 'FB příspěvek – konspirace o zásobách',
                ],
                'options' => [
                    [
                        'name' => 'Příspěvek nasdílím dál',
                        'right' => false,
                        'evaluation' => 'Zadržte!<br><br>Přesně to je cílem autora. Tato šokující fotka útočí na vaše nejzákladnější emoce – strach, že nebude dost jídla.<br><br>Vytvořit takovou lež je dnes, v době umělé inteligence (AI), nesmírně jednoduché.<br><br>Podvodník už nemusí ani fotit prázdný regál pozdě večer. Stačí, když počítači zadá, co chce vidět, a umělá inteligence mu během pár vteřin vygeneruje dokonale realistický obrázek zdevastovaného obchodu.<br><br>A proč to lidé dělají? Buď chtějí záměrně vyvolat paniku, nebo chtějí na své stránky přitáhnout tisíce lidí, protože ze zvědavých kliknutí mají peníze z reklamy.<br><br>Sdílením jim jen pomáháte.',
                    ],
                    [
                        'name' => 'Zkusím si v komentářích ověřit, jestli to lidé potvrzují.',
                        'right' => false,
                        'evaluation' => 'Snaha ověřit si situaci je správná, ale <strong>komentáře na sociálních sítích jsou tou nejméně bezpečnou cestou</strong>.<br><br>Podvodné příspěvky často přitahují další vyděšené lidi nebo falešné internetové profily (tzv. boty), kteří lež v diskuzi záměrně potvrzují.<br><br>Vzniká tak falešný dojem, že se to děje všude. Informace vždy ověřujte jen v seriózním zpravodajství.<br><br>Zkuste to znovu.',
                    ],
                    [
                        'name' => 'Příspěvku nevěřím',
                        'right' => true,
                        'evaluation' => 'Vynikající!<br><br>Vaše intuice a nedůvěra byly zcela na místě.<br><br>Tento příspěvek útočil na lidský strach z nedostatku a zneužil hru na spiknutí tvrzením, že ‚nikdo o tom nemluví‘.<br><br>V dnešní době umělé inteligence (AI) je nesmírně snadné vytvořit jakoukoliv falešnou fotku nebo video za pár vteřin.<br><br>Autoři to dělají buď proto, aby záměrně šířili paniku, nebo aby na šokující obsah přitáhli lidi a vydělali na reklamě.<br><br>Tím, že jste si zachoval chladnou hlavu a zprávu ignoroval, jste nad nimi zvítězili.<br><br>Skvělá práce.',
                    ],
                ],
            ],

            // ===== PÁTÝ herní kámen =====
            [
                'button' => 5,
                'difficulty' => 1,
                'perex' => 'FB příspěvek – hořící úřad',
                'description' => '[[image:situace]]<p>Text příspěvku:</p><p>„Právě teď hoří městský úřad. Úřady o tom mlčí.“</p><p>Pod příspěvkem stojí:</p><p>„Sdílejte, ať se lidé dozví pravdu.“</p>',
                'safety_card' => null,
                'image' => [
                    'key' => 'situace',
                    'path' => 'images/situations/a835b2c0-d439-4ab4-8e44-f68538acd1dd.webp',
                    'alt' => 'FB příspěvek – hořící úřad',
                ],
                'options' => [
                    [
                        'name' => 'Příspěvek hned nasdílím dál',
                        'right' => false,
                        'evaluation' => 'Zadržte!<br><br>Přesně to je cílem autora.<br><br>Tento příspěvek útočí na vaše emoce – na strach o bezpečí ve vašem okolí a pocit bezprostředního ohrožení.<br><br>Slova ‚právě teď‘ vás mají vystresovat, abyste jednali ve strachu a bez uvážení.<br><br>Vytvořit takový podvod je dnes díky umělé inteligenci (AI) otázkou chvilky.<br><br>Počítač dokáže během pár vteřin vygenerovat dokonale věrohodné plameny na jakékoliv budově.<br><br>Že je zpráva v pořádku a důvěryhodná, poznáte podle toho, že ji oficiálně vydají záchranné složky. Při skutečném požáru by informace okamžitě visela na oficiálním webu nebo sociálních sítích Hasičského záchranného sboru (HZS), Policie ČR nebo samotného města. Seriózní zpráva navíc nikdy netvrdí, že „úřady mlčí“, ale naopak obsahuje jasné pokyny, co mají obyvatelé v okolí dělat.<br><br>A proč to lidé dělají? Buď chtějí vyvolat chaos, pobavit se na úkor druhých nebo jen zneužívají lidskou zvědavost, aby získali tisíce kliknutí, ze kterých mají peníze z reklamy. Sdílením jim jen pomáháte.<br><br>Zkuste to znovu',
                    ],
                    [
                        'name' => 'Pro jistotu zavolám rodině, ať dnes raději nechodí do centra.',
                        'right' => false,
                        'evaluation' => 'Vaše starost o bezpečí rodiny je pochopitelná.<br><br>Podvodníci však přesně s tímto strachem kalkulují. Chytře vytvořili dojem, že nebezpečí hrozí „právě teď“.<br><br>Než rodinu vystresujete, věnujte minutu klidnému ověření informace.<br><br>Skutečnou a důvěryhodnou zprávu poznáte tak, že o ní okamžitě informují oficiální zdroje.<br><br>Podívejte se na internetové stránky Hasičského záchranného sboru, Policie ČR nebo vašeho města. Pokud tam o žádném požáru ani zásahu v centru není ani zmínka, jde velmi pravděpodobně o poplašnou zprávu vytvořenou umělou inteligencí (AI).<br><br>Autoři těchto podvodů se často jen baví na úkor druhých nebo chtějí vyvolat chaos. Nenechte je, aby vám brali klid.<br><br>Zkuste to znovu.',
                    ],
                    [
                        'name' => 'Příspěvek budu ignorovat, je to poplašná zpráva.',
                        'right' => true,
                        'evaluation' => 'Vynikající!<br><br>Tento příspěvek zaútočil na emoce spojené s bezpečím a využil trik s vytvořením falešného spěchu („právě teď“), aby vás donutil jednat v panice.<br><br>Správně jste prohlédli, že v dnešní době umělé inteligence (AI) je nesmírně snadné vygenerovat jakýkoliv dramatický obrázek nebo video.<br><br>Že je zpráva o nebezpečí pravdivá, poznáte jedině podle toho, že ji oficiálně vydají záchranné složky – tedy Hasičský záchranný sbor, Policie ČR nebo samotné město na svých oficiálních stránkách.<br><br>Skutečné úřady by při katastrofě nikdy nemlčely, ale okamžitě by organizovaly pomoc a varovaly občany.<br><br>Tím, že jste zprávu ignorovali, jste překazil plán autorů, kteří chtěli pouze vyvolat chaos nebo vydělat na internetové reklamě.<br><br>Skvělá práce!',
                    ],
                ],
            ],
            [
                'button' => 5,
                'difficulty' => 2,
                'perex' => 'FB příspěvek – fotografie požáru',
                'description' => '[[image:situace]]<p>Při prohlížení Facebooku se vám zobrazí příspěvek s fotografií hořící budovy a lidí na ulici.</p><p>Text příspěvku:</p><p>„Takhle to dnes vypadá v centru města. Hoří budova úřadu a lidé utíkají pryč.“</p><p>Dále v příspěvku stojí:</p><p>„Média zatím nic neříkají. Moje známá říká, že oblast uzavírá policie.“</p><p>U fotografie není uveden autor ani přesné místo pořízení.</p>',
                'safety_card' => null,
                'image' => [
                    'key' => 'situace',
                    'path' => 'images/situations/a1fb8ca3-ceb1-48e3-b2bb-8789457d7cd4.webp',
                    'alt' => 'FB příspěvek – fotografie požáru',
                ],
                'options' => [
                    [
                        'name' => 'Příspěvek hned nasdílím dál',
                        'right' => false,
                        'evaluation' => 'Zadržte!<br><br>Přesně to je cílem autora.<br><br>Tento příspěvek útočí na vaše emoce – na strach o bezpečí ve vašem okolí a pocit bezprostředního ohrožení.<br><br>Slova jako ‚právě teď‘ a ´média mlčí´vás mají vystresovat, abyste jednali ve strachu a bez uvážení.<br><br>Vytvořit takový podvod je dnes díky umělé inteligenci (AI) otázkou chvilky.<br><br>Počítač dokáže během pár vteřin vygenerovat dokonale věrohodné plameny na jakékoliv budově.<br><br>Že je zpráva v pořádku a důvěryhodná, poznáte podle toho, že ji oficiálně vydají záchranné složky. Při skutečném požáru by informace okamžitě visela na oficiálním webu nebo sociálních sítích Hasičského záchranného sboru (HZS), Policie ČR nebo samotného města. Seriózní zpráva navíc nikdy netvrdí, že „úřady mlčí“, ale naopak obsahuje jasné pokyny, co mají obyvatelé v okolí dělat.<br><br>A proč to lidé dělají? Buď chtějí vyvolat chaos, pobavit se na úkor druhých nebo jen zneužívají lidskou zvědavost, aby získali tisíce kliknutí, ze kterých mají peníze z reklamy. Sdílením jim jen pomáháte.<br><br>Zkuste to znovu',
                    ],
                    [
                        'name' => 'Pro jistotu zavolám rodině, ať dnes raději nechodí do centra.',
                        'right' => false,
                        'evaluation' => 'Vaše starost o bezpečí rodiny je pochopitelná.<br><br>Podvodníci však přesně s tímto strachem kalkulují. Chytře vytvořili dojem, že nebezpečí hrozí „právě teď“.<br><br>Než rodinu vystresujete, věnujte minutu klidnému ověření informace.<br><br>Skutečnou a důvěryhodnou zprávu poznáte tak, že o ní okamžitě informují oficiální zdroje.<br><br>Podívejte se na internetové stránky Hasičského záchranného sboru, Policie ČR nebo vašeho města. Pokud tam o žádném požáru ani zásahu v centru není ani zmínka, jde velmi pravděpodobně o poplašnou zprávu vytvořenou umělou inteligencí (AI).<br><br>Autoři těchto podvodů se často jen baví na úkor druhých nebo chtějí vyvolat chaos. Nenechte je, aby vám brali klid.<br><br>Zkuste to znovu.',
                    ],
                    [
                        'name' => 'Příspěvek budu ignorovat, je to poplašná zpráva.',
                        'right' => true,
                        'evaluation' => 'Vynikající!<br><br>Tento příspěvek zaútočil na emoce spojené s bezpečím a využil trik s vytvořením falešného spěchu („právě teď“), aby vás donutil jednat v panice.<br><br>Správně jste prohlédli, že v dnešní době umělé inteligence (AI) je nesmírně snadné vygenerovat jakýkoliv dramatický obrázek nebo video.<br><br>Že je zpráva o nebezpečí pravdivá, poznáte jedině podle toho, že ji oficiálně vydají záchranné složky – tedy Hasičský záchranný sbor, Policie ČR nebo samotné město na svých oficiálních stránkách.<br><br>Skutečné úřady by při katastrofě nikdy nemlčely, ale okamžitě by organizovaly pomoc a varovaly občany.<br><br>Tím, že jste zprávu ignorovali, jste překazil plán autorů, kteří chtěli pouze vyvolat chaos nebo vydělat na internetové reklamě.<br><br>Skvělá práce!',
                    ],
                ],
            ],
            [
                'button' => 5,
                'difficulty' => 3,
                'perex' => 'FB příspěvek – dramatická fotografie',
                'description' => '[[image:situace]]<p>Při prohlížení Facebooku se vám zobrazí příspěvek s dramatickou fotografií hořící budovy a lidí v panice.</p><p>Text příspěvku:</p><p>„Právě teď v centru města! Hoří budova úřadu, policie uzavírá okolí a lidé utíkají pryč.“</p><p>Pod příspěvkem jsou komentáře:</p><p>„Moje známá říká, že evakuují celé okolí.“</p><p>„Proč o tom televize nemluví?“<br>„Sdílejte rychle, ať se lidé zachrání.“</p><p>U fotografie není uveden autor, přesné místo ani zdroj.</p><p><strong>Časomíra:</strong> 1 minuta</p>',
                'safety_card' => null,
                'image' => [
                    'key' => 'situace',
                    'path' => 'images/situations/ae26f9e5-45c1-4877-9bc8-cb7a9b39f662.webp',
                    'alt' => 'FB příspěvek – dramatická fotografie',
                ],
                'options' => [
                    [
                        'name' => 'Příspěvek nasdílím dál',
                        'right' => false,
                        'evaluation' => 'Zadržte!<br><br>Přesně to je cílem autora.<br><br>Tento příspěvek útočí na vaše emoce – na strach o bezpečí ve vašem okolí a pocit bezprostředního ohrožení.<br><br>Vytváření dojmu, že se to děje “právě teď”, že existují “očitá svědectví” chtějí, abychom jednali ve strachu a bez uvážení.<br><br>Vytvořit takový podvod je dnes díky umělé inteligenci (AI) otázkou chvilky.<br><br>Počítač dokáže během pár vteřin vygenerovat dokonale věrohodné plameny na jakékoliv budově.<br><br>Že je zpráva v pořádku a důvěryhodná, poznáte podle toho, že ji oficiálně vydají záchranné složky. Při skutečném požáru by informace okamžitě visela na oficiálním webu nebo sociálních sítích Hasičského záchranného sboru (HZS), Policie ČR nebo samotného města. Seriózní zpráva navíc nikdy netvrdí, že „úřady mlčí“, ale naopak obsahuje jasné pokyny, co mají obyvatelé v okolí dělat.<br><br>A proč to lidé dělají? Buď chtějí vyvolat chaos, pobavit se na úkor druhých nebo jen zneužívají lidskou zvědavost, aby získali tisíce kliknutí, ze kterých mají peníze z reklamy. Sdílením jim jen pomáháte.<br><br>Zkuste to znovu',
                    ],
                    [
                        'name' => 'Pro jistotu zavolám rodině, ať dnes raději nechodí do centra.',
                        'right' => false,
                        'evaluation' => 'Vaše starost o bezpečí rodiny je pochopitelná.<br><br>Podvodníci však přesně s tímto strachem kalkulují. Chytře vytvořili dojem, že nebezpečí hrozí „právě teď“.<br><br>Než rodinu vystresujete, věnujte minutu klidnému ověření informace.<br><br>Skutečnou a důvěryhodnou zprávu poznáte tak, že o ní okamžitě informují oficiální zdroje.<br><br>Podívejte se na internetové stránky Hasičského záchranného sboru, Policie ČR nebo vašeho města. Pokud tam o žádném požáru ani zásahu v centru není ani zmínka, jde velmi pravděpodobně o poplašnou zprávu vytvořenou umělou inteligencí (AI).<br><br>Autoři těchto podvodů se často jen baví na úkor druhých nebo chtějí vyvolat chaos. Nenechte je, aby vám brali klid.<br><br>Zkuste to znovu.',
                    ],
                    [
                        'name' => 'Příspěvek budu ignorovat, je to poplašná zpráva.',
                        'right' => true,
                        'evaluation' => 'Vynikající!<br><br>Tento příspěvek zaútočil na emoce spojené s bezpečím a využil trik s vytvořením falešného spěchu („právě teď“), aby vás donutil jednat v panice.<br><br>Správně jste prohlédli, že v dnešní době umělé inteligence (AI) je nesmírně snadné vygenerovat jakýkoliv dramatický obrázek nebo video.<br><br>Že je zpráva o nebezpečí pravdivá, poznáte jedině podle toho, že ji oficiálně vydají záchranné složky – tedy Hasičský záchranný sbor, Policie ČR nebo samotné město na svých oficiálních stránkách.<br><br>Skutečné úřady by při katastrofě nikdy nemlčely, ale okamžitě by organizovaly pomoc a varovaly občany.<br><br>Tím, že jste zprávu ignorovali, jste překazil plán autorů, kteří chtěli pouze vyvolat chaos nebo vydělat na internetové reklamě.<br><br>Skvělá práce!',
                    ],
                ],
            ],
        ];
    }
};
