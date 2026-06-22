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
 * Naplní Ostrov klamavých zpráv z dodaného scénáře (docx).
 *
 * 5 herních kamenů (pozic), každý se třemi obtížnostmi 1–3 (15 situací), plus
 * bonusová „bezpečná" otázka.
 *
 * Mapování ze scénáře:
 *  - sloupec Obtížnost      → question.difficulty_id
 *  - sloupec Situace        → question.description (text + obrázek situace)
 *  - sloupec Tlačítka       → questionOption.name
 *  - sloupec Odpověď systému (Plné/Tlumené světlo) → questionOption.right
 *  - sloupec Reakce strážce → questionOption.evaluation
 *  - sloupec Karta bezpečí  → situations.safety_card
 *
 * Herní časomíra (question.settings.time_limit = 59 s) je u kamenů 1, 3 a 5 /
 * obtížnosti 3 („Časomíra: 59 sekund"). Bonusová otázka se ukládá jako Question
 * s bonus=true, BEZ vazby na situations (difficulty_id je povinné, proto 1).
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
                $question = $this->createQuestion($data);

                Situation::forceCreate([
                    'island_id' => $island->id,
                    'question_id' => $question->id,
                    'position' => $data['button'],
                    'title' => null,
                    'safety_card' => $data['safety_card'],
                ]);
            }

            $this->createQuestion($this->bonusQuestion(), bonus: true);
        });
    }

    public function down(): void
    {
        $island = Island::query()->where('image', 'deceptive_news.webp')->first();

        if (!$island instanceof Island) {
            return;
        }

        $situationQuestionIds = Situation::query()
            ->where('island_id', $island->id)
            ->pluck('question_id')
            ->all();

        $bonusQuestionIds = QuestionImage::query()
            ->whereIn('path', [$this->bonusQuestion()['image']['path']])
            ->pluck('question_id')
            ->all();

        $questionIds = array_values(array_unique([...$situationQuestionIds, ...$bonusQuestionIds]));

        QuestionImage::query()->whereIn('question_id', $questionIds)->delete();
        QuestionOption::query()->whereIn('question_id', $questionIds)->delete();
        Question::query()->whereIn('id', $questionIds)->delete();
    }

    /**
     * @param array<string, mixed> $data
     */
    private function createQuestion(array $data, bool $bonus = false): Question
    {
        $question = Question::forceCreate([
            'version' => Version::Three,
            'question_group_id' => null,
            'difficulty_id' => $data['difficulty'],
            'bonus' => $bonus,
            'perex' => $data['perex'],
            'description' => $data['description'] . '<br><br>[[image:situace]]',
            'first_wrong_answer_evaluation' => '',
            'second_wrong_answer_evaluation' => '',
            'settings' => $data['settings'] ?? null,
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

        QuestionImage::forceCreate([
            'question_id' => $question->id,
            'key' => 'situace',
            'path' => $data['image']['path'],
            'alt' => $data['image']['alt'],
            'position' => 0,
        ]);

        return $question;
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function situations(): array
    {
        // Reakce strážce se v rámci jednoho kamene opakují napříč obtížnostmi –
        // držíme je v proměnných, ať se neduplikuje text.
        $medCall = 'Pozor, tudy cesta nevede. Na druhé straně linky nečeká lékař, ale vyškolený prodejce. Jeho jediným cílem je využít vašeho strachu o zdraví a vmanipulovat vás do nákupu předraženého a neúčinného přípravku. Skutečné léky vám musí předepsat lékař, ne prodejce po telefonu.';
        $medVerify = 'Snaha ověřovat informace je skvělá, ale pozor. Tyto podvodné články jsou často tak rafinované, že když zadáte název léku do vyhledávače, vyběhnou na vás desítky dalších falešných stránek a recenzí, které produkt chválí. Nejbezpečnější je takové sliby rovnou ignorovat.';
        $medIgnore = 'Skvělá práce! Správně jste rozpoznal, že se jedná o takzvaný textový inzerát, který se pouze maskuje jako běžný článek. Hraje na city a nabízí zázračná řešení tam, kde moderní medicína postupuje pomalu. Ignorovat takové nabídky je ta nejlepší obrana.';
        $medCard = 'Zázračné léky nefungují. Pokud někdo slibuje zázračné vyléčení, bude to podvod.';

        $chainForward = 'Zadržte! Přesně tohle tvůrci e-mailů chtějí. Tento text obsahuje šokující a senzační zprávu, která má vyvolat strach, vztek a pocit nespravedlnosti. Je záměrně napsaná tak, aby vás okamžitě emočně zasáhla a vy jste v panice klikli na tlačítko přeposlat. Tím, že zprávu bez přemýšlení přepošlete, se nevědomky stáváte šiřitelem lží a zbytečné paniky mezi svými blízkými.';
        $chainVerify = 'Ověřování je skvělý nápad. Ale pozor – podvodníci na internetu schválně vytvářejí desítky falešných stránek. Když zadáte text do vyhledávače, snadno spadnete do pasti, kde si lidé v diskusích tuto lež vzájemně potvrzují. Odborníci proto radí jednoduché pravidlo tří zdrojů: zpráva je pravdivá, když ji nezávisle na sobě potvrzují aspoň tři seriózní zpravodajské weby. U důchodů se vždy podívejte přímo na oficiální stránky ministerstva (mpsv.cz).';
        $chainCard = 'Řetězové e-maily plné strachu dál neposílejte. Výzvy typu „Sdílejte, než to smažou" nebo „Média mlčí" jsou znakem podvodu, jehož cílem je jen vyvolat paniku.';

        $newsShare = 'Zadržte! Přesně na to podvodníci čekají. Tyto weby záměrně napodobují názvy důvěryhodných a známých médií, aby ve vás vyvolaly pocit, že čtete skutečné zprávy. Titulek útočí na vaše emoce (strach a nejistotu). Vždy se podívejte na samotnou adresu webu (URL) úplně nahoře v prohlížeči. Pokud je podezřele dlouhá, zkomolená nebo končí divnou koncovkou, buďte obezřetní – pravděpodobně jde o podvodný web.';
        $newsVerify = 'Snaha ověřovat informace je chvályhodná! Má to ale háček. Podvodníci jsou rafinovaní a často si zakládají celou síť podobných stránek. Když zadáte tento vymyšlený titulek do vyhledávače, snadno narazíte na další falešné články, které se vás budou snažit přesvědčit, že je to pravda. Odborníci proto radí pravidlo tří zdrojů: zprávu berte vážně jen tehdy, když ji nezávisle potvrdí alespoň tři velká a obecně známá média (např. Česká televize, Český rozhlas). U sociálních dávek pak zamiřte rovnou na oficiální web ministerstva.';
        $newsClose = 'Vynikající! Tento web záměrně napodobuje vzhled seriózního zpravodajství, aby zneužil vaši důvěru. Jeho titulek měl za úkol ve vás vyvolat strach a obavy z budoucnosti. Vy jste to odhalili. Skvělá práce!';
        $newsCard = 'Šokující titulky a anonymní komentáře pod článkem nejsou spolehlivý zdroj informací.';

        $aiShare = 'Zadržte! Přesně to je cílem autora. Tato šokující fotka útočí na vaše nejzákladnější emoce – strach, že nebude dost jídla. Vytvořit takovou lež je dnes, v době umělé inteligence (AI), nesmírně jednoduché. Podvodník už nemusí ani fotit prázdný regál – stačí, když počítači zadá, co chce vidět, a AI mu během pár vteřin vygeneruje dokonale realistický obrázek zdevastovaného obchodu. A proč to lidé dělají? Buď chtějí záměrně vyvolat paniku, nebo na své stránky přitáhnout tisíce lidí, protože ze zvědavých kliknutí mají peníze z reklamy. Sdílením jim jen pomáháte.';
        $aiComments = 'Snaha ověřit si situaci je správná, ale komentáře na sociálních sítích jsou tou nejméně bezpečnou cestou. Podvodné příspěvky často přitahují další vyděšené lidi nebo falešné profily (tzv. boty), kteří lež v diskuzi záměrně potvrzují. Vzniká tak falešný dojem, že se to děje všude. Informace vždy ověřujte jen v seriózním zpravodajství.';
        $aiIgnore = 'Vynikající! Vaše intuice a nedůvěra byly zcela na místě. Tento příspěvek útočil na lidský strach z nedostatku a zneužil hru na spiknutí tvrzením, že „nikdo o tom nemluví". V dnešní době umělé inteligence (AI) je nesmírně snadné vytvořit jakoukoliv falešnou fotku nebo video za pár vteřin. Autoři to dělají buď proto, aby šířili paniku, nebo aby na šokující obsah přitáhli lidi a vydělali na reklamě. Tím, že jste si zachoval chladnou hlavu a zprávu ignoroval, jste nad nimi zvítězili. Skvělá práce.';
        $aiCard = 'Fotografie na internetu už dnes nejsou spolehlivým důkazem, počítač je umí snadno uměle vytvořit.';

        $fireShare = 'Zadržte! Přesně to je cílem autora. Tento příspěvek útočí na vaše emoce – na strach o bezpečí ve vašem okolí a pocit bezprostředního ohrožení. Slova „právě teď" vás mají vystresovat, abyste jednali ve strachu a bez uvážení. Vytvořit takový podvod je dnes díky umělé inteligenci (AI) otázkou chvilky – počítač dokáže během pár vteřin vygenerovat věrohodné plameny na jakékoliv budově. Že je zpráva důvěryhodná, poznáte podle toho, že ji oficiálně vydají záchranné složky. Při skutečném požáru by informace okamžitě visela na oficiálním webu Hasičského záchranného sboru (HZS), Policie ČR nebo města. Seriózní zpráva navíc nikdy netvrdí, že „úřady mlčí", ale obsahuje jasné pokyny, co mají obyvatelé dělat.';
        $fireFamily = 'Vaše starost o bezpečí rodiny je pochopitelná. Podvodníci však přesně s tímto strachem kalkulují – chytře vytvořili dojem, že nebezpečí hrozí „právě teď". Než rodinu vystresujete, věnujte minutu klidnému ověření. Skutečnou zprávu poznáte tak, že o ní okamžitě informují oficiální zdroje. Podívejte se na stránky Hasičského záchranného sboru, Policie ČR nebo vašeho města. Pokud tam o žádném požáru ani zásahu není ani zmínka, jde velmi pravděpodobně o poplašnou zprávu vytvořenou umělou inteligencí (AI).';
        $fireIgnore = 'Vynikající! Tento příspěvek zaútočil na emoce spojené s bezpečím a využil trik s falešným spěchem („právě teď"), aby vás donutil jednat v panice. Správně jste prohlédli, že v dnešní době umělé inteligence (AI) je nesmírně snadné vygenerovat jakýkoliv dramatický obrázek nebo video. Že je zpráva o nebezpečí pravdivá, poznáte jedině podle toho, že ji oficiálně vydají záchranné složky – Hasičský záchranný sbor, Policie ČR nebo samotné město. Skutečné úřady by při katastrofě nikdy nemlčely, ale okamžitě by varovaly občany. Skvělá práce!';
        $fireCard = 'Zprávy o katastrofách na sociálních sítích vždy ověřujte u oficiálních zdrojů.';

        return [
            // ============ 1. herní kámen – zázračné léky v inzerátech ============
            [
                'button' => 1, 'difficulty' => 1, 'perex' => 'Článek o zázračném léku',
                'description' => 'Při listování časopisem narazíte na článek:<br><br>„Paní Marie objevila jednoduchý způsob, jak se zbavit bolesti kloubů. Už po 7 dnech se cítila jako znovuzrozená."<br><br>V textu stojí „Tuto přírodní metodu může vyzkoušet každý senior." Pod článkem je obrázek usměvavé seniorky a nápis „Doporučujeme čtenářům 60+." Na konci článku je uvedeno: „Pro více informací volejte bezplatnou linku 800 123 456."',
                'safety_card' => $medCard,
                'image' => ['path' => 'images/situations/38265600-06ab-4cbc-bb89-147dfef25fd3.webp', 'alt' => 'Inzerát maskovaný jako článek o zázračném léku'],
                'options' => [
                    ['name' => 'Zavolám na bezplatnou linku.', 'right' => false, 'evaluation' => $medCall],
                    ['name' => 'Zkusím si produkt ověřit jinde.', 'right' => true, 'evaluation' => $medVerify],
                    ['name' => 'Článek ignoruji, je to reklama.', 'right' => true, 'evaluation' => $medIgnore],
                ],
            ],
            [
                'button' => 1, 'difficulty' => 2, 'perex' => 'Inzerát na přírodní kúru',
                'description' => 'Při listování časopisem narazíte na článek:<br><br>„Lékaři upozorňují: bolest kloubů nemusí být běžnou součástí stáří."<br><br>V textu stojí „Podle odborníka MUDr. Petra H. může seniorům pomoci přírodní kúra Flexi Senior." Dole pod článkem je malý rámeček: „Speciální nabídka pro čtenáře: první balení za zvýhodněnou cenu." Pod ním „Objednávejte telefonicky na čísle 800 123 456. Při objednávce nahlaste kód SENIOR." Článek není jasně označený jako reklama.',
                'safety_card' => $medCard,
                'image' => ['path' => 'images/situations/4cae3dd4-8466-446f-ac7b-9cc7c14f98a3.webp', 'alt' => 'Inzerát na přírodní kúru s objednávkovým kódem'],
                'options' => [
                    ['name' => 'Zavolám na bezplatnou linku.', 'right' => false, 'evaluation' => $medCall],
                    ['name' => 'Zkusím si produkt ověřit jinde.', 'right' => true, 'evaluation' => $medVerify],
                    ['name' => 'Článek ignoruji, je to reklama.', 'right' => true, 'evaluation' => $medIgnore],
                ],
            ],
            [
                'button' => 1, 'difficulty' => 3, 'perex' => 'Inzerát s časově omezenou nabídkou',
                'settings' => ['time_limit' => 59],
                'description' => 'Při listování časopisem narazíte na článek:<br><br>„Lékaři upozorňují: bolest kloubů nemusí být běžnou součástí stáří."<br><br>V textu stojí „Podle odborníka MUDr. Petra H. může seniorům pomoci přírodní kúra Flexi Senior." Dole je malý rámeček: „Speciální nabídka pro prvních 50 čtenářů: první balení za zvýhodněnou cenu. Nabídka platí pouze tento týden." Pod ním „Objednávejte telefonicky na čísle 800 123 456. Při objednávce nahlaste kód SENIOR." Článek není označený jako reklama.',
                'safety_card' => $medCard,
                'image' => ['path' => 'images/situations/f37d84d3-b675-4712-8af5-e4583cb2f72c.webp', 'alt' => 'Inzerát na kúru s časově omezenou nabídkou'],
                'options' => [
                    ['name' => 'Zavolám na bezplatnou linku.', 'right' => false, 'evaluation' => $medCall],
                    ['name' => 'Zkusím si produkt ověřit jinde.', 'right' => true, 'evaluation' => $medVerify],
                    ['name' => 'Článek ignoruji, je to reklama.', 'right' => true, 'evaluation' => $medIgnore],
                ],
            ],

            // ============ 2. herní kámen – řetězové e-maily ============
            [
                'button' => 2, 'difficulty' => 1, 'perex' => 'Poplašný řetězový e-mail',
                'description' => 'Do e-mailu vám přijde zpráva od známé:<br><br>„POZOR!!! Od příštího měsíce vláda schválila nový poplatek pro důchodce. V televizi o tom mlčí."<br><br>Na konci zprávy stojí: „Pošlete to všem známým, než to smažou."',
                'safety_card' => $chainCard,
                'image' => ['path' => 'images/situations/cf9bb4b6-e91b-42bc-a906-13a08a602856.webp', 'alt' => 'Poplašný řetězový e-mail o novém poplatku'],
                'options' => [
                    ['name' => 'Zprávu hned přepošlu známým.', 'right' => false, 'evaluation' => $chainForward],
                    ['name' => 'Zkusím si informaci nejdřív ověřit.', 'right' => true, 'evaluation' => $chainVerify],
                    ['name' => 'Zprávu smažu a dál nešířím.', 'right' => true, 'evaluation' => 'Vynikající! Tento e-mail zaútočil na vaše emoce pomocí strachu, využil hru na spiknutí o mlčení médií a přidal naléhavou výzvu k akci, abyste jednali ve spěchu a bez přemýšlení. Správně jste vyhodnotili, že vládní poplatky se neschvalují tajně ze dne na den. Smazáním zprávy jste ochránili klid svých známých i svůj vlastní.'],
                ],
            ],
            [
                'button' => 2, 'difficulty' => 2, 'perex' => 'Přeposlaná zpráva o důchodech',
                'description' => 'Do e-mailu vám přijde přeposlaná zpráva od známé:<br><br>„Ahoj, posílám informaci od kamarádky, která pracuje na úřadě. Prý se chystá změna ve vyplácení důchodů a někteří lidé mohou přijít o část peněz."<br><br>Na konci zprávy stojí: „Raději to pošli dál všem seniorům, co znáš, ať se na to připraví."',
                'safety_card' => $chainCard,
                'image' => ['path' => 'images/situations/832ab248-36de-476c-b14c-99644c1f1661.webp', 'alt' => 'Přeposlaná zpráva o změně ve vyplácení důchodů'],
                'options' => [
                    ['name' => 'Zprávu hned přepošlu známým.', 'right' => false, 'evaluation' => $chainForward],
                    ['name' => 'Zkusím si informaci nejdřív ověřit.', 'right' => true, 'evaluation' => $chainVerify],
                    ['name' => 'Zprávu smažu a dál nešířím.', 'right' => true, 'evaluation' => 'Vynikající! Tento e-mail zaútočil na vaše emoce pomocí strachu, využil hru na spiknutí o mlčení médií a přidal naléhavou výzvu k akci, abyste jednali ve spěchu a bez přemýšlení. Správně jste vyhodnotili, že vyplácení důchodů se neschvaluje tajně ze dne na den. Smazáním zprávy jste ochránili klid svých známých i svůj vlastní.'],
                ],
            ],
            [
                'button' => 2, 'difficulty' => 3, 'perex' => 'Řetězový e-mail s výzvou',
                'description' => 'Do e-mailu vám přijde přeposlaná zpráva od známé:<br><br>„Už je rozhodnuto. Od července se změní vyplácení důchodů a mnoho seniorů o peníze přijde. Tuto informaci mám od mého známého z úřadu, ale veřejně se o tom nesmí mluvit."<br><br>Dále ve zprávě stojí: „Média mlčí a politici to tají. Přepošlete tento e-mail rychle alespoň 10 lidem, dokud není pozdě."',
                'safety_card' => $chainCard,
                'image' => ['path' => 'images/situations/ddc111f4-0450-47f8-be76-74d97db3e2b9.webp', 'alt' => 'Řetězový e-mail s výzvou k rychlému přeposlání'],
                'options' => [
                    ['name' => 'Zprávu hned přepošlu známým.', 'right' => false, 'evaluation' => $chainForward],
                    ['name' => 'Zkusím si informaci nejdřív ověřit.', 'right' => true, 'evaluation' => $chainVerify],
                    ['name' => 'Zprávu smažu a dál nešířím.', 'right' => true, 'evaluation' => 'Vynikající! Tento e-mail zaútočil na vaše emoce pomocí strachu, využil hru na spiknutí o mlčení médií a přidal naléhavou výzvu k akci, abyste jednali ve spěchu a bez přemýšlení. Správně jste vyhodnotili, že vyplácení důchodů se neschvaluje tajně ze dne na den. Smazáním zprávy jste ochránili klid svých známých i svůj vlastní.'],
                ],
            ],

            // ============ 3. herní kámen – falešné zpravodajské weby ============
            [
                'button' => 3, 'difficulty' => 1, 'perex' => 'Falešný zpravodajský web',
                'description' => 'Při prohlížení internetu se vám zobrazí článek, který vypadá jako zpráva:<br><br>„ŠOKUJÍCÍ ZMĚNA: Senioři mohou od příštího měsíce přijít o část příspěvků."<br><br>Web se jmenuje „SenioriNews", adresa stránky je www.seniorinews.cz. V článku stojí „Média o tom zatím mlčí."',
                'safety_card' => $newsCard,
                'image' => ['path' => 'images/situations/822db2f9-a153-4a04-afa2-f9dcdb9ba572.webp', 'alt' => 'Falešný zpravodajský web SenioriNews'],
                'options' => [
                    ['name' => 'Článek nasdílím přátelům, ať o tom ostatní vědí.', 'right' => false, 'evaluation' => $newsShare],
                    ['name' => 'Zkusím si informaci nejdřív ověřit.', 'right' => true, 'evaluation' => $newsVerify],
                    ['name' => 'Stránku zavřu, web vypadá podezřele.', 'right' => true, 'evaluation' => $newsClose],
                ],
            ],
            [
                'button' => 3, 'difficulty' => 2, 'perex' => 'Web napodobující médium',
                'description' => 'Při prohlížení internetu se vám zobrazí článek, který vypadá jako zpráva:<br><br>„Nový zákon změní život seniorů. Někteří mohou přijít o tisíce korun."<br><br>Web se jmenuje „České zprávy", adresa je www.ceskezpravy.cz/novy-zakon-zmeni-zivot-senioru. V článku stojí „Podle zdrojů z ministerstva se změna dotkne lidí, kteří si včas nezkontrolují své údaje." Pod článkem jsou komentáře „Tohle mi poslala kamarádka, je to pravda.", „V televizi o tom mlčí.", „Sdílejte, ať se to lidé dozví."',
                'safety_card' => $newsCard,
                'image' => ['path' => 'images/situations/2f252e88-e2b0-4dbb-94e3-ba01c28ebb67.webp', 'alt' => 'Web napodobující seriózní médium'],
                'options' => [
                    ['name' => 'Článek nasdílím, ať o tom ostatní vědí.', 'right' => false, 'evaluation' => $newsShare],
                    ['name' => 'Zkusím si informaci nejdřív ověřit.', 'right' => true, 'evaluation' => $newsVerify],
                    ['name' => 'Stránku zavřu, web vypadá podezřele.', 'right' => true, 'evaluation' => $newsClose],
                ],
            ],
            [
                'button' => 3, 'difficulty' => 3, 'perex' => 'Falešný web s komentáři',
                'settings' => ['time_limit' => 59],
                'description' => 'Při prohlížení internetu se vám zobrazí článek, který vypadá jako zpráva:<br><br>„Nový zákon změní život seniorů. Někteří mohou už tento měsíc přijít o tisíce korun."<br><br>Web se jmenuje „Domácí přehled 24", adresa je www.domaci-prehled24.cz. V článku stojí „Podle zdrojů z ministerstva se změna dotkne lidí, kteří si včas nezkontrolují své údaje." Pod článkem jsou komentáře „Tohle mi poslala kamarádka, je to pravda.", „V televizi o tom mlčí.", „Sdílejte, ať se to lidé dozví."',
                'safety_card' => $newsCard,
                'image' => ['path' => 'images/situations/c9bc2f54-fb74-4e15-a0d4-94166da4a2ca.webp', 'alt' => 'Falešný web s falešnými komentáři'],
                'options' => [
                    ['name' => 'Článek nasdílím, ať o tom ostatní vědí.', 'right' => false, 'evaluation' => $newsShare],
                    ['name' => 'Zkusím si informaci nejdřív ověřit.', 'right' => true, 'evaluation' => $newsVerify],
                    ['name' => 'Stránku zavřu, web vypadá podezřele.', 'right' => true, 'evaluation' => $newsClose],
                ],
            ],

            // ============ 4. herní kámen – AI fotky a panika o nedostatku ============
            [
                'button' => 4, 'difficulty' => 1, 'perex' => 'Fotka prázdného regálu',
                'description' => 'Při prohlížení Facebooku se vám zobrazí příspěvek s fotkou prázdného regálu v obchodě.<br><br>Text příspěvku: „Takto to dnes vypadá v supermarketu. Zboží mizí z regálů a nikdo o tom nemluví."<br><br>Pod příspěvkem stojí: „Sdílejte, ať se lidé připraví."',
                'safety_card' => $aiCard,
                'image' => ['path' => 'images/situations/e3b383f1-9e3b-4314-a611-02eb59df80d7.webp', 'alt' => 'Příspěvek s fotkou prázdného regálu'],
                'options' => [
                    ['name' => 'Příspěvek nasdílím dál.', 'right' => false, 'evaluation' => $aiShare],
                    ['name' => 'Zkusím si v komentářích ověřit, jestli to lidé potvrzují.', 'right' => false, 'evaluation' => $aiComments],
                    ['name' => 'Příspěvku nevěřím.', 'right' => true, 'evaluation' => $aiIgnore],
                ],
            ],
            [
                'button' => 4, 'difficulty' => 2, 'perex' => 'Mizející zásoby v obchodě',
                'description' => 'Při prohlížení Facebooku se vám zobrazí příspěvek s fotkou prázdných regálů v obchodě.<br><br>Text příspěvku: „Takto to teď vypadá v Marketu Plus. Mouka, olej a cukr mizí z obchodů."<br><br>Dále v příspěvku stojí: „Moje známá tam pracuje a říkala, že zásoby se odvážejí pryč. Pro Čechy brzy nic nezůstane." Fotka nemá uvedené datum ani místo pořízení.',
                'safety_card' => $aiCard,
                'image' => ['path' => 'images/situations/8c5ed5d6-5dd0-4994-a0ee-25a867c94dcc.webp', 'alt' => 'Příspěvek o mizejících zásobách'],
                'options' => [
                    ['name' => 'Příspěvek nasdílím dál.', 'right' => false, 'evaluation' => $aiShare],
                    ['name' => 'Zkusím si v komentářích ověřit, jestli to lidé potvrzují.', 'right' => false, 'evaluation' => $aiComments],
                    ['name' => 'Příspěvku nevěřím.', 'right' => true, 'evaluation' => $aiIgnore],
                ],
            ],
            [
                'button' => 4, 'difficulty' => 3, 'perex' => 'Panika o nedostatku zásob',
                'description' => 'Při prohlížení Facebooku se vám zobrazí příspěvek s fotkou prázdných regálů v obchodě.<br><br>Text příspěvku: „Takto to dnes vypadá v Marketu Plus. Mouka, olej a cukr mizí z regálů. Zásoby se odvážejí do zahraničí a pro Čechy brzy nic nezůstane."<br><br>Pod příspěvkem jsou komentáře „U nás už taky skoro nic není.", „Rychle nakupujte zásoby.", „Sdílejte, než to smažou." Fotka nemá uvedené datum ani přesné místo pořízení.',
                'safety_card' => $aiCard,
                'image' => ['path' => 'images/situations/69714b84-7520-405a-9269-918c7cbdc8cf.webp', 'alt' => 'Panický příspěvek o nedostatku zásob'],
                'options' => [
                    ['name' => 'Příspěvek nasdílím dál.', 'right' => false, 'evaluation' => $aiShare],
                    ['name' => 'Zkusím si v komentářích ověřit, jestli to lidé potvrzují.', 'right' => false, 'evaluation' => $aiComments],
                    ['name' => 'Příspěvku nevěřím.', 'right' => true, 'evaluation' => $aiIgnore],
                ],
            ],

            // ============ 5. herní kámen – falešné zprávy o katastrofě ============
            [
                'button' => 5, 'difficulty' => 1, 'perex' => 'Poplašná zpráva o požáru',
                'description' => 'Na Facebooku se vám zobrazí příspěvek.<br><br>Text příspěvku: „Právě teď hoří městský úřad. Úřady o tom mlčí."<br><br>Pod příspěvkem stojí: „Sdílejte, ať se lidé dozví pravdu."',
                'safety_card' => $fireCard,
                'image' => ['path' => 'images/situations/587cc4f9-88e5-4358-af9d-132d82c64e0a.webp', 'alt' => 'Poplašná zpráva o hořícím úřadu'],
                'options' => [
                    ['name' => 'Příspěvek hned nasdílím dál.', 'right' => false, 'evaluation' => $fireShare],
                    ['name' => 'Pro jistotu zavolám rodině, ať dnes raději nechodí do centra.', 'right' => false, 'evaluation' => $fireFamily],
                    ['name' => 'Příspěvek budu ignorovat, je to poplašná zpráva.', 'right' => true, 'evaluation' => $fireIgnore],
                ],
            ],
            [
                'button' => 5, 'difficulty' => 2, 'perex' => 'Fotka hořícího úřadu',
                'description' => 'Při prohlížení Facebooku se vám zobrazí příspěvek s fotografií hořící budovy a lidí na ulici.<br><br>Text příspěvku: „Takhle to dnes vypadá v centru města. Hoří budova úřadu a lidé utíkají pryč."<br><br>Dále v příspěvku stojí: „Média zatím nic neříkají. Moje známá říká, že oblast uzavírá policie." U fotografie není uveden autor ani přesné místo pořízení.',
                'safety_card' => $fireCard,
                'image' => ['path' => 'images/situations/f4f18887-ef3e-43b5-888a-1252dc32d28e.webp', 'alt' => 'Fotografie hořícího úřadu na Facebooku'],
                'options' => [
                    ['name' => 'Příspěvek hned nasdílím dál.', 'right' => false, 'evaluation' => $fireShare],
                    ['name' => 'Pro jistotu zavolám rodině, ať dnes raději nechodí do centra.', 'right' => false, 'evaluation' => $fireFamily],
                    ['name' => 'Příspěvek budu ignorovat, je to poplašná zpráva.', 'right' => true, 'evaluation' => $fireIgnore],
                ],
            ],
            [
                'button' => 5, 'difficulty' => 3, 'perex' => 'Dramatická zpráva o požáru',
                'settings' => ['time_limit' => 59],
                'description' => 'Při prohlížení Facebooku se vám zobrazí příspěvek s dramatickou fotografií hořící budovy a lidí v panice.<br><br>Text příspěvku: „Právě teď v centru města! Hoří budova úřadu, policie uzavírá okolí a lidé utíkají pryč."<br><br>Pod příspěvkem jsou komentáře „Moje známá říká, že evakuují celé okolí.", „Proč o tom televize nemluví?", „Sdílejte rychle, ať se lidé zachrání." U fotografie není uveden autor, přesné místo ani zdroj.',
                'safety_card' => $fireCard,
                'image' => ['path' => 'images/situations/fd6b75bb-0827-44f9-8e02-2090a16f459b.webp', 'alt' => 'Dramatická zpráva o požáru s komentáři'],
                'options' => [
                    ['name' => 'Příspěvek nasdílím dál.', 'right' => false, 'evaluation' => $fireShare],
                    ['name' => 'Pro jistotu zavolám rodině, ať dnes raději nechodí do centra.', 'right' => false, 'evaluation' => $fireFamily],
                    ['name' => 'Příspěvek budu ignorovat, je to poplašná zpráva.', 'right' => true, 'evaluation' => $fireIgnore],
                ],
            ],
        ];
    }

    /**
     * Bonusová „bezpečná" otázka – běžné oznámení města o odstávce vody.
     * @return array<string, mixed>
     */
    private function bonusQuestion(): array
    {
        return [
            'difficulty' => 1,
            'perex' => 'Oznámení o odstávce vody',
            'description' => 'Ve schránce najdete oznámení od města:<br><br>„Město upozorňuje obyvatele ulice Javorová na plánovanou odstávku vody. Odstávka proběhne v úterý 14. května od 8:00 do 14:00 z důvodu opravy vodovodního potrubí."<br><br>Dole na oznámení je uvedeno: „Zveřejnil Odbor správy města dne 6. května 2026. Více informací najdete na webu města nebo na zákaznické lince vodáren 800 123 456."',
            'image' => ['path' => 'images/situations/98646519-3332-4a73-9ac0-439079ccf04d.webp', 'alt' => 'Oznámení města o plánované odstávce vody'],
            'options' => [
                [
                    'name' => 'Připravím si vodu na dobu odstávky a případně si informaci ověřím na webu města.',
                    'right' => true,
                    'evaluation' => implode('<br><br>', [
                        'Výborně, kapitáne! Tohle je bezpečná a běžná informační situace. Oznámení je konkrétní: uvádí ulici, datum, čas, důvod odstávky i kontakt na vodárny.',
                        'Nikdo po vás nechce peníze, osobní údaje ani rychlé sdílení. Zpráva také nepoužívá dramatická slova jako „tajné", „skandál" nebo „média mlčí".',
                        'Hlavní pravidlo: bezpečná zpráva bývá konkrétní, klidná a ověřitelná (např. na webu města nebo na stránkách dodavatele).',
                    ]),
                ],
                [
                    'name' => 'Vyfotím oznámení a napíšu na Facebook: „Zase nám tajně vypínají vodu! Sdílejte!"',
                    'right' => false,
                    'evaluation' => implode('<br><br>', [
                        'Zadržte, kapitáne! Rozumíme, že odstávka vody může být nepříjemná a člověk má chuť upozornit ostatní. V tomto případě je ale lepší zprávu sdílet klidně a bez dramatického komentáře.',
                        'Oznámení uvádí konkrétní čas, místo, důvod i kontakt na ověření. Nepíše se v něm, že by město něco tajilo. Když k ověřené informaci přidáme silná slova jako „tajně" nebo „sdílejte", může to u ostatních zbytečně vyvolat strach.',
                        'Hlavní pravidlo: i pravdivou informaci je dobré předávat klidně a přesně.',
                    ]),
                ],
                [
                    'name' => 'Oznámení rovnou vyhodím, protože podobné zprávy jsou určitě podvod.',
                    'right' => false,
                    'evaluation' => implode('<br><br>', [
                        'Pozor, kapitáne! Opatrnost je důležitá, ale tady jste byli až příliš přísní.',
                        'Toto oznámení má znaky důvěryhodné informace: uvádí konkrétní ulici, datum, čas, důvod odstávky a kontakt na ověření. Nechce po vás peníze, osobní údaje ani okamžitou reakci.',
                        'Cílem bezpečnosti není nevěřit ničemu, ale poznat rozdíl mezi manipulací a běžnou ověřitelnou zprávou. Ne každá nepříjemná zpráva je klamavá.',
                    ]),
                ],
            ],
        ];
    }
};
