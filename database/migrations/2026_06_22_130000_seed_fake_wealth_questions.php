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
 * Naplní Ostrov falešného bohatství z dodaného scénáře (docx).
 *
 * 5 herních kamenů (pozic), každý se třemi obtížnostmi 1–3 (15 situací), plus
 * bonusová „bezpečná" otázka.
 *
 * Mapování ze scénáře:
 *  - sloupec Obtížnost     → question.difficulty_id
 *  - sloupec Situace       → question.description (text + obrázek situace)
 *  - sloupec Tlačítka      → questionOption.name
 *  - sloupec Odpověď systému (Plné/Tlumené světlo) → questionOption.right
 *  - sloupec Reakce strážce → questionOption.evaluation
 *  - sloupec Karta bezpečí → situations.safety_card
 *
 * Herní časomíra (question.settings.time_limit) je jen u 5. kamene / obtížnosti 3
 * („plus časomíra"), 59 s. Bonusová otázka se ukládá jako Question s bonus=true,
 * BEZ vazby na situations (difficulty_id je povinné, proto 1).
 *
 * Pozn.: u 5. kamene / obtížnosti 1 chyběly ve scénáři texty dvou tlačítek
 * i „Odpověď systému"; tlačítka jsou dorekonstruovaná podle reakcí strážce a
 * podle obdobných situací (5. kámen obtížnost 2 a 3), správnost odvozena z tónu
 * reakce.
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
        $island = Island::query()->where('image', 'fake_wealth.webp')->first();

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
     * @param array{
     *     difficulty: int,
     *     perex: string,
     *     description: string,
     *     settings?: array<string, mixed>,
     *     image: array{path: string, alt: string},
     *     options: list<array{name: string, right: bool, evaluation: string}>,
     * } $data
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
        return [
            // ============ 1. herní kámen – podezřele výhodný nákup ============
            [
                'button' => 1,
                'difficulty' => 1,
                'perex' => 'Reklama na Facebooku',
                'description' => 'Reklama na Facebooku:<br><br>„LIKVIDACE SKLADU – POSLEDNÍ KUSY! Robotický vysavač CleanBot SmartClean. Původní cena: 9 990 Kč. Dnes pouze: 1 290 Kč. Zbývají poslední 2 kusy."<br><br>Pod reklamou je tlačítko „Koupit nyní", které vás přesměruje na stránku obchodu.',
                'safety_card' => 'Extrémní sleva a tlak na rychlé rozhodnutí jsou varovné signály.',
                'image' => ['path' => 'images/situations/1843d233-a87f-459c-a493-b20fdda51fcf.webp', 'alt' => 'Reklama na výhodný nákup na Facebooku'],
                'options' => [
                    [
                        'name' => 'Tato sleva je super, produkt objednám hned.',
                        'right' => false,
                        'evaluation' => implode('<br><br>', [
                            'Zadržte, kapitáne! Tady byste najeli rovnou na útes. Vaše peníze by nejspíš zmizely na neznámém účtu a vysavač by nikdy nedorazil nebo by neměl deklarovanou kvalitu.',
                            'Podvodníci v této reklamě použili tři klasické psychologické triky:',
                            'Podezřele obří sleva: vysavač za 10 000 Kč prostě nikdo reálně neprodá za tisícovku.',
                            'Umělý tlak („Poslední 2 kusy!"): časový nátlak vás má vyplašit a donutit zaplatit hned, aniž byste situaci racionálně zvážili.',
                            'Neznámý původ: reklamu na Facebooku si dnes může za pár korun zaplatit úplně kdokoliv. Není to žádná záruka, že jde o skutečný obchod.',
                            'Když nevíte, u koho nakupujete, obchod si prověřte: hledejte zkušenosti ostatních lidí na nezávislých portálech (např. Heureka) a nakoukněte na seznam rizikových e-shopů na webu České obchodní inspekce (ČOI) nebo na portál dTest.',
                        ]),
                    ],
                    [
                        'name' => 'Vyhledám si produkt i jinde na internetu.',
                        'right' => true,
                        'evaluation' => implode('<br><br>', [
                            'Skvělá práce! Nenechali jste se strhnout lákavou reklamou a zachoval jste chladnou hlavu.',
                            'Když si produkt vyhledáte sami (třeba na Heurece nebo Zboží.cz), ihned uvidíte jeho reálnou cenu všude na trhu a rychle zjistíte, že takto obří slevu nikdo jiný nenabízí. To je jasný důkaz, že reklama byla podvod.',
                            'Hlavní pravidlo: nejlepší obranou je ověřit si nabídku mimo samotnou reklamu.',
                        ]),
                    ],
                    [
                        'name' => 'Podívám se na stránku.',
                        'right' => false,
                        'evaluation' => implode('<br><br>', [
                            'V pořádku, kapitáne – samotným pohledem se ještě nic nestalo a vaše peníze jsou zatím v bezpečí. Teď ale buďte ve střehu.',
                            'Když už na stránce jste, okamžitě zkontrolujte tři věci. Adresa webu: je tam správný název značky, nebo podivná zkomolenina? Kontakty: má e-shop jasné sídlo firmy a IČO? Umělý spěch: tiká na vás odpočet času, že sleva za pár minut končí?',
                            'Hlavní pravidlo: prohlížet si web klidně můžete, ale nikdy tam nezadávejte své jméno, adresu ani číslo karty, dokud si nejste stoprocentně jistí, komu patří. Zkusme to raději znovu a bezpečněji.',
                        ]),
                    ],
                ],
            ],
            [
                'button' => 1,
                'difficulty' => 2,
                'perex' => 'Podezřelý e-shop',
                'description' => 'Chcete si koupit robotický vysavač. Do vyhledávání zadáte „Philips SmartClean sleva". Mezi prvními výsledky se objeví obchod ALZAA Elektro. Název připomíná známý obchod Alza, stránka má logo, fotky výrobku a působí jako běžný e-shop.<br><br>U produktu svítí nabídka: „Původní cena: 9 990 Kč, dnes pouze: 1 390 Kč", a pod cenou „Mimořádná akce – doprodej skladu", „Zbývají poslední kusy".<br><br>Na stránce ale není jasné, kdo obchod provozuje – chybí adresa i telefon. Obchod nabízí jen platbu převodem předem.',
                'safety_card' => 'Všímejte si varovných signálů prodejců a nabídku si vždy ověřte jinde.',
                'image' => ['path' => 'images/situations/78b47a67-d275-4f6b-8dfe-f94a5efad118.webp', 'alt' => 'Podezřelý e-shop napodobující známou značku'],
                'options' => [
                    [
                        'name' => 'Sleva je super, objednávku zaplatím převodem.',
                        'right' => false,
                        'evaluation' => implode('<br><br>', [
                            'Zadržte, kapitáne! Tímto rozhodnutím byste poslali peníze na účet, o kterém nevíte, komu patří. Zboží by s velkou pravděpodobností vůbec nedorazilo a peníze by nebylo možné získat zpět.',
                            'Napodobení důvěry: název „ALZAA Elektro" je záměrně podobný známé značce, aby ve vás vyvolal pocit, že nakupujete u někoho, koho už znáte.',
                            'Výhodná cena a spěch: nízká cena a nápisy o doprodeji vás tlačí k rychlému rozhodnutí.',
                            'Nejasná identita prodejce: na stránce není jasné, kdo obchod provozuje, nemáte možnost si ho ověřit.',
                            'Riziková platba: obchod nabízí pouze převod předem. Peníze posíláte přímo na účet a nemáte žádnou ochranu.',
                            'Dobrá strategie je obchod ověřit mimo jeho stránku – například podle recenzí (Heureka) nebo varování (Česká obchodní inspekce, dTest).',
                        ]),
                    ],
                    [
                        'name' => 'Produkt si jen prohlédnu, zatím neobjednávám.',
                        'right' => true,
                        'evaluation' => implode('<br><br>', [
                            'V pořádku, kapitáne – pouhým prohlížením stránky o žádné peníze nepřijdete. Je skvělé, že nespěcháte s objednávkou. Když už se ale díváte, tahle stránka přímo křičí, že je to past.',
                            'Falešné jméno: obchod se jmenuje ALZAA Elektro. Podvodníci schválně zkopírovali vzhled slavné Alzy a sází na to, že si jednoho písmenka navíc nevšimnete.',
                            'Úplná anonymita: chybí adresa, telefon i IČO. Nemáte tušení, komu posíláte peníze a kde případně zboží reklamovat.',
                            'Pouze platba předem: stránka vás nutí poslat peníze dřív, než cokoli uvidíte. To je pro podvodníky nejjednodušší způsob, jak vás okrást.',
                            'Hlavní pravidlo: dívat se můžete, ale jakmile e-shop schovává své kontakty a vyžaduje pouze platbu předem, okamžitě pryč.',
                        ]),
                    ],
                    [
                        'name' => 'Obchod si nejprve prověřím.',
                        'right' => true,
                        'evaluation' => implode('<br><br>', [
                            'Výborně, kapitáne! Nenechal jste se zaslepit slevou a všiml jste si, že tady něco velmi nesedí.',
                            'Prověřit si e-shop na Heurece, dTestu nebo u České obchodní inspekce je dobrý nápad, ale má to háček. Podvodné stránky vznikají a mizí každý den. Že o webu nenajdete špatné zprávy, neznamená, že je bezpečný.',
                            'Vy jste ale správně odhalil spoustu varovných prvků přímo na stránce: parazitování na názvu „ALZAA", úplnou anonymitu (chybí IČO, adresa i telefon) a platbu jen předem.',
                            'Hlavní pravidlo: jakmile e-shop tají, kdo ho provozuje, a zároveň chce platbu předem, rovnou stránku zavřete.',
                        ]),
                    ],
                ],
            ],
            [
                'button' => 1,
                'difficulty' => 3,
                'perex' => 'Podezřelý e-shop s odpočtem',
                'description' => 'Chcete si koupit robotický vysavač. Do vyhledávání zadáte „Philips SmartClean sleva". Mezi prvními výsledky se objeví obchod ALZAA Elektro. Název připomíná známý obchod Alza, stránka má logo i fotky výrobku.<br><br>U produktu svítí nabídka „Původní cena: 9 990 Kč, dnes pouze: 1 390 Kč", a pod cenou „Mimořádná akce – doprodej skladu", „Zbývají poslední kusy". Na stránce není jasné, kdo obchod provozuje – chybí adresa i telefon. Obchod nabízí jen platbu převodem předem.<br><br>Navíc se objeví odpočet: „Akce končí za 1:59".',
                'safety_card' => 'Časový nátlak je varovný signál.',
                'image' => ['path' => 'images/situations/dd0395d3-2091-45d2-9b70-ffbd73d82173.webp', 'alt' => 'Podezřelý e-shop s odpočtem akce'],
                'options' => [
                    [
                        'name' => 'Sleva je super, objednávku zaplatím převodem.',
                        'right' => false,
                        'evaluation' => implode('<br><br>', [
                            'Zadržte, kapitáne! Tímto rozhodnutím byste poslali peníze na účet, o kterém nevíte, komu patří. Zboží by s velkou pravděpodobností vůbec nedorazilo a peníze by nebylo možné získat zpět.',
                            'Napodobení důvěry: název podobný známému obchodu ve vás vyvolává pocit, že nakupujete bezpečně.',
                            'Časový tlak: odpočet „1:59" je klíčový. Má vás donutit jednat okamžitě a nedat vám prostor situaci promyslet.',
                            'Nejasná identita a riziková platba: nevíte, kdo obchod provozuje, a přesto byste poslali peníze převodem předem na neznámý účet.',
                            'Seriózní e-shop vás nebude nutit zaplatit během dvou minut. Dobrá strategie je obchod ověřit mimo jeho stránku – podle recenzí (Heureka) nebo varování (Česká obchodní inspekce, dTest).',
                        ]),
                    ],
                    [
                        'name' => 'Produkt si jen prohlédnu, zatím neobjednávám.',
                        'right' => true,
                        'evaluation' => implode('<br><br>', [
                            'V pořádku, kapitáne – pouhým prohlížením stránky o žádné peníze nepřijdete. Když už se ale díváte, tahle stránka přímo křičí, že je to past.',
                            'Falešné jméno: obchod se jmenuje ALZAA Elektro – napodobenina slavné Alzy.',
                            'Úplná anonymita: chybí adresa, telefon i IČO.',
                            'Pouze platba předem a tlak na čas: odpočet zvyšuje šanci, že budete jednat pod tlakem.',
                            'Hlavní pravidlo: jakmile e-shop schovává kontakty, vyžaduje platbu předem a tlačí vás do okamžité reakce – rychle pryč.',
                        ]),
                    ],
                    [
                        'name' => 'Obchod se mi nezdá. Ještě ho prověřím.',
                        'right' => true,
                        'evaluation' => implode('<br><br>', [
                            'Výborně, kapitáne! Nenechal jste se zaslepit slevou a všiml jste si, že tady něco velmi nesedí.',
                            'Prověřit si e-shop je dobrý nápad, ale podvodné stránky vznikají a mizí každý den – že o webu nenajdete špatné zprávy, neznamená, že je bezpečný.',
                            'Vy jste ale správně odhalil varovné prvky přímo na stránce: parazitování na názvu, úplnou anonymitu, platbu jen předem a časový nátlak.',
                            'Hlavní pravidlo: jakmile e-shop tají, kdo ho provozuje, chce platbu předem a spěchá na objednávku – rovnou stránku zavřete.',
                        ]),
                    ],
                ],
            ],

            // ============ 2. herní kámen – investiční podvod ============
            [
                'button' => 2,
                'difficulty' => 1,
                'perex' => 'Investiční reklama',
                'description' => 'Při prohlížení Facebooku se vám zobrazí reklama:<br><br>„Matka samoživitelka z Ostravy našla způsob, jak si vydělávat z domova. Dnes má pasivní příjem až 50 000 Kč týdně."<br><br>V textu stojí: „Stačilo začít s částkou 5 000 Kč." Pod reklamou jsou komentáře „Už jsem vydělal 60 tisíc.", „Funguje to." a tlačítko „Zjistit více".',
                'safety_card' => 'Sliby rychlého výdělku jsou varování.',
                'image' => ['path' => 'images/situations/6a506d50-d3ec-4b34-996f-b5bd70635879.webp', 'alt' => 'Investiční reklama na Facebooku'],
                'options' => [
                    [
                        'name' => 'Kliknu na „Zjistit více", nabídka je zajímavá.',
                        'right' => false,
                        'evaluation' => implode('<br><br>', [
                            'Zadržte, kapitáne! Kliknutím na reklamu byste vstoupili do procesu, který vás postupně dovede k zadání údajů a odeslání peněz.',
                            'Silný příběh: „matka samoživitelka" má ve vás vyvolat pocit, že jde o běžného člověka – když to zvládla ona, zvládnete to taky.',
                            'Slib snadného zisku: vysoký příjem bez námahy má vyvolat dojem jednoduché příležitosti.',
                            'Sociální důkaz: komentáře mají vytvořit pocit, že to funguje i ostatním. Často jsou ale falešné nebo vytvořené podvodníkem.',
                            'Sliby rychlého a vysokého výdělku bez rizika nejsou realistické. Bezpečný postup je takovou reklamu neotevírat a informace si ověřit z nezávislých zdrojů.',
                        ]),
                    ],
                    [
                        'name' => 'Ignoruji reklamu.',
                        'right' => true,
                        'evaluation' => implode('<br><br>', [
                            'Výborně, kapitáne! Tím, že jste na reklamu vůbec nereagovali, jste zastavili celý proces hned na začátku.',
                            'Reklama je navržená tak, aby ve vás vyvolala zájem a přiměla vás kliknout – využívá silný příběh, vysoký výdělek a komentáře. Po kliknutí by pokračovala další manipulace, například výzvy k registraci nebo první „investici".',
                            'Pokud něco slibuje vysoký výdělek bez práce a bez rizika, je potřeba zbystřit. Takové nabídky nejsou realistické.',
                        ]),
                    ],
                    [
                        'name' => 'Nejsem si jistý, radši se poradím s rodinou.',
                        'right' => true,
                        'evaluation' => implode('<br><br>', [
                            'Výborně! Rozhodli jste se nezůstat na to sami a to je v této situaci velmi důležité.',
                            'Příběh „matky samoživitelky" a vysoký výdělek ve vás mají vyvolat pocit, že jde o běžnou a dosažitelnou věc; komentáře „funguje to" mají dojem ještě posílit. Když jste se rozhodli poradit s někým dalším, získali jste odstup a možnost situaci zhodnotit klidněji.',
                            'Seriózní investice neslibují vysoké zisky bez rizika a nešíří se přes anonymní reklamy na sociálních sítích. Nejbezpečnější je takovou reklamu vůbec neotevírat.',
                        ]),
                    ],
                ],
            ],
            [
                'button' => 2,
                'difficulty' => 2,
                'perex' => 'Investiční e-mail',
                'description' => 'Do e-mailu vám přijde zpráva „Důležité: Nová investiční příležitost pro klienty českých bank".<br><br>Odesílatel: financni-info@novinki-news.com<br><br>Text: „Nový investiční systém umožňuje vydělávat až 30 000 Kč týdně. Banky se snaží tuto informaci skrýt. Stačí začít s částkou 5 000 Kč." Tlačítko „Otevřít investiční účet".',
                'safety_card' => 'Na investiční nabídky z neznámých e-mailů neklikejte ani neodpovídejte.',
                'image' => ['path' => 'images/situations/4a2ccea7-3b9e-46c8-9df5-7726f3428207.webp', 'alt' => 'Investiční e-mail od neznámého odesílatele'],
                'options' => [
                    [
                        'name' => 'Kliknu na „Otevřít investiční účet".',
                        'right' => false,
                        'evaluation' => implode('<br><br>', [
                            'Zadržte, kapitáne! Kliknutím na odkaz byste se dostali na stránku, která vás vyzve k zadání osobních údajů nebo první „investice". Peníze by odešly podvodníkovi a nebylo by možné je získat zpět.',
                            'Falešná autorita: zmínka o „klientech českých bank" má vyvolat pocit oficiální nabídky.',
                            'Slib vysokého výdělku: částka „30 000 Kč týdně" má vzbudit pocit výjimečné příležitosti.',
                            'Utajovaná informace: věta „banky se to snaží skrýt" má vyvolat dojem, že jste se dostali k něčemu výjimečnému.',
                            'Seriózní investice se neposílají neznámým e-mailem a neslibují vysoké zisky bez rizika. Bezpečný postup je na takové odkazy neklikat a nabídku si ověřit z jiného zdroje.',
                        ]),
                    ],
                    [
                        'name' => 'Chci více informací, na e-mail odepíšu.',
                        'right' => false,
                        'evaluation' => implode('<br><br>', [
                            'Zadržte, kapitáne! Odpovědí na e-mail byste podvodníkovi potvrdili, že je na druhé straně skutečný člověk.',
                            'Podvodník by začal komunikaci rozvíjet – vysvětloval by „výhody", odpovídal na vaše otázky a snažil se získat vaši důvěru. Čím déle komunikace trvá, tím víc může působit důvěryhodně.',
                            'Seriózní investiční nabídky nepřicházejí z neznámých e-mailů a nevyžadují osobní komunikaci s neznámým odesílatelem. Bezpečný postup je na takové e-maily vůbec neodpovídat.',
                        ]),
                    ],
                    [
                        'name' => 'Ověřím si nabídku jinde.',
                        'right' => true,
                        'evaluation' => implode('<br><br>', [
                            'Výborně, kapitáne! Podobné nabídky si můžete ověřit třeba přes Registr České národní banky (ČNB). Každá firma, která v Česku legálně nabízí investice, musí mít licenci od ČNB. Na webu ČNB (cnb.cz) je seznam varování a blokovaných podvodných firem.',
                            'Selský rozum a matematika: slibují vám 30 000 Kč týdně z pouhých 5 000 Kč? Kdyby takový stroj na peníze existoval, banky by dávno zkrachovaly. Garantovaný obří zisk bez rizika neexistuje.',
                            'Pokud máte pochybnosti, stačí zavolat na oficiální linku své vlastní banky a zeptat se. A pokud o nabídku nestojíte, můžete e-mail rovnou smazat nebo ignorovat.',
                        ]),
                    ],
                ],
            ],
            [
                'button' => 2,
                'difficulty' => 3,
                'perex' => 'Investiční video',
                'description' => 'Do e-mailu vám přijde zpráva „Důležité video: Nová investiční příležitost pro občany České republiky".<br><br>Odesílatel: investice-info@financni-system24.com<br><br>V e-mailu je video, které vypadá jako reportáž z ČT24. Vystupuje v něm známý český politik a mluví o investiční platformě, která údajně vydělává vysoké částky. Pod videem stojí „Registrace je otevřena jen krátce.", „Zbývá posledních 8 míst.", „Dokončete registraci do 10 minut." a tlačítko „Dokončit registraci".',
                'safety_card' => 'Videa mohou být upravená nebo zcela falešná. Zbystřete a všímejte si varovných signálů.',
                'image' => ['path' => 'images/situations/901e7d64-fcbc-48cf-8bee-51abcde01f33.webp', 'alt' => 'Falešné investiční video se známou osobností'],
                'options' => [
                    [
                        'name' => 'Rychle kliknu na „Dokončit registraci".',
                        'right' => false,
                        'evaluation' => implode('<br><br>', [
                            'Zadržte, kapitáne! Tímto krokem byste se dostali na stránku, kde by po vás mohli chtít osobní údaje nebo první investici. Peníze či údaje by skončily u podvodníka.',
                            'Falešná důvěryhodnost: video připomíná známou zpravodajskou stanici a vystupuje v něm známý politik, aby to vyvolalo pocit ověřené informace.',
                            'Známá osobnost: lidé častěji důvěřují informaci spojené s někým známým nebo autoritativním.',
                            'Časový tlak: „posledních 8 míst" a „10 minut" vás mají donutit jednat rychle, bez ověření.',
                            'Video se známou osobností nemusí být pravé – podvodníci dnes dokážou upravit obraz i hlas. Bezpečný postup je neklikat, nevyplňovat údaje a informaci si ověřit mimo e-mail.',
                        ]),
                    ],
                    [
                        'name' => 'Chci více informací, na e-mail odepíšu.',
                        'right' => false,
                        'evaluation' => implode('<br><br>', [
                            'Odpovědí byste podvodníkovi potvrdili, že e-mail používá skutečný člověk, který je ochotný reagovat.',
                            'Podvodník by mohl navázat osobní komunikaci, odpovídat na vaše otázky, posílat další „důkazy" a postupně budovat důvěru. Video přitom může být upravené nebo zcela nepravdivé a i tady vás může tlačit k rychlému rozhodnutí.',
                            'Neznámý e-mail s investiční nabídkou není důvod k navazování komunikace. Bezpečný postup je neodpovídat a informace si ověřit jinou cestou.',
                        ]),
                    ],
                    [
                        'name' => 'Ověřím informace na věrohodných zdrojích a e-mail smažu.',
                        'right' => true,
                        'evaluation' => implode('<br><br>', [
                            'Výborně, kapitáne! Nenechali jste se přesvědčit samotným videem ani tlakem na rychlou registraci.',
                            'Nepodlehli jste falešné autoritě – to, že video vypadá jako známá reportáž nebo v něm vystupuje známá osoba, ještě neznamená, že je pravé. Odolali jste i časovému tlaku; „poslední místa" a odpočet mají jediný cíl: zabránit vám v klidném ověření.',
                            'Informace jste ověřili mimo e-mail – například na oficiálních stránkách důvěryhodných médií, banky nebo České národní banky. Smazáním e-mailu jste navíc přerušili další pokusy o manipulaci.',
                        ]),
                    ],
                ],
            ],

            // ============ 3. herní kámen – bílý kůň ============
            [
                'button' => 3,
                'difficulty' => 1,
                'perex' => 'Nabídka přivýdělku (SMS)',
                'description' => 'Do mobilu vám přijde zpráva z neznámého čísla:<br><br>„Dobrý den, nabízíme jednoduchý přivýdělek z domova. Hledáme lidi, kteří budou otevírat zaslaná videa, sledovat je podle pokynů a potvrzovat splnění jednoduchých úkolů. Za každý splněný úkol dostanete zaplaceno. Za první vyzkoušení získáte 200 Kč. Denně si můžete vydělat až 4 000–10 000 Kč."',
                'safety_card' => 'Vysoký výdělek za jednoduché klikání bývá podvod.',
                'image' => ['path' => 'images/situations/378cbf2e-2f73-489e-8265-151489ea4f14.webp', 'alt' => 'SMS s nabídkou přivýdělku z domova'],
                'options' => [
                    [
                        'name' => 'Kliknu a rovnou splním úkol.',
                        'right' => false,
                        'evaluation' => implode('<br><br>', [
                            'Zadržte, kapitáne! Tady jde o mnohem víc než jen o ztrátu času. Tato zdánlivě nevinná „brigáda" je rafinovaná past, kde se nevědomky můžete dostat do vážného křížku se zákonem.',
                            'Háček s návnadou: podvodníci vám prvních 200 Kč klidně pošlou – schválně, aby získali vaši důvěru.',
                            'Role „bílého koně": v dalších krocích po vás budou chtít, abyste přijímal platby na svůj účet a posílal je dál. Tím z vás udělají nástroj, přes který perou špinavé peníze ukradené jiným obětem.',
                            'Neznalost neomlouvá: pokud přes váš účet protečou ukradené peníze, policie zaklepe na dveře vám. Za legalizaci výnosů z trestné činnosti hrozí reálné tresty.',
                            'Hlavní pravidlo: nikdo na světě vám nedá 10 000 Kč denně za pouhé klikání na videa. Jakmile po vás neznámý člověk z SMS chce operace s penězi, okamžitě zprávu smažte. Zkusme to znovu a bezpečněji.',
                        ]),
                    ],
                    [
                        'name' => 'Je to zajímavé, kliknu na odkaz, ale jen se podívám, úkol neplním.',
                        'right' => false,
                        'evaluation' => implode('<br><br>', [
                            'Chválím vaši opatrnost, kapitáne. Jenže u SMS od cizích čísel skrývá obrovské riziko už samotné kliknutí na odkaz.',
                            'Past na váš mobil (malware): odkaz vás může přesměrovat na stránku, která se pokusí do telefonu tajně stáhnout škodlivý program. Ten pak může sledovat, co děláte, nebo se dostat do mobilního bankovnictví.',
                            'Chycení do databáze: jakmile na odkaz kliknete, podvodníci uvidí, že je číslo aktivní a majitel na zprávy kliká. Tím jim potvrdíte, že jste ideální terč.',
                            'Hlavní pravidlo: u podezřelých SMS platí stopka hned na začátku – na odkaz vůbec neklikat a zprávu rovnou smazat. Zkusme to znovu a tentokrát bez risku.',
                        ]),
                    ],
                    [
                        'name' => 'Zprávu smažu, nezdá se mi to.',
                        'right' => true,
                        'evaluation' => implode('<br><br>', [
                            'Výborně, kapitáne! Odolal jste zvědavosti i slibům snadného výdělku a nepřítele jste zneškodnil tím nejúčinnějším způsobem – smazáním zprávy.',
                            'Vyhnul jste se dvěma velkým hrozbám. Virům v telefonu: tím, že jste neklikl na odkaz, jste zabránil stažení škodlivého programu. Pasti na „bílého koně": podvodníci často pošlou malou částku pro získání důvěry a následně by po vás chtěli přeposílat peníze na cizí účty, čímž by vás zapojili do praní špinavých peněz – a odpovědnost byste nesl vy.',
                        ]),
                    ],
                ],
            ],
            [
                'button' => 3,
                'difficulty' => 2,
                'perex' => 'Přivýdělek z „doporučení"',
                'description' => 'Do mobilu vám přijde zpráva:<br><br>„Dobrý den, váš kontakt jsme dostali přes doporučení od vašich přátel. Nabízíme jednoduchý přivýdělek z domova. Stačí otevírat zaslaná videa, přidávat hodnocení a za každý úkol dostanete zaplaceno. Za první úkol: 200 Kč."<br><br>Pod zprávou vidíte další zprávy „Hotovo 👍", „200 Kč přišlo.", „Dnes už mám 600 Kč." a na konci „Volná místa rychle mizí. Pokud chcete začít dnes, ozvěte se do 5 minut."',
                'safety_card' => 'Na neznámé nabídky práce pod časovým tlakem neodpovídejte.',
                'image' => ['path' => 'images/situations/bf2a37ca-3c3e-45d0-89b5-71966d902ec3.webp', 'alt' => 'SMS s nabídkou přivýdělku a falešnými reakcemi'],
                'options' => [
                    [
                        'name' => 'Zprávu smažu, nezdá se mi to.',
                        'right' => true,
                        'evaluation' => implode('<br><br>', [
                            'Výborně! Smazat tuhle zprávu byl ten nejlepší tah. Odrazili jste hned tři triky.',
                            'Lživé doporučení: věta, že mají kontakt „od vašich přátel", je čistá lež. Používají ji jen proto, abyste ztratil ostražitost.',
                            'Falešný potlesk: zprávy „Hotovo 👍" a „Peníze přišly" si podvodník napsal sám. Má to vypadat jako spokojená diskuse, ale je to návnada.',
                            'Pětiminutový nátlak: umělý spěch („ozvěte se do 5 minut") vás má donutit jednat ve zmatku a bez přemýšlení.',
                        ]),
                    ],
                    [
                        'name' => 'Zkusím první úkol.',
                        'right' => false,
                        'evaluation' => implode('<br><br>', [
                            'Zadržte! Chápu, že 200 Kč za pár minut klikání zní lákavě. Jenže co by se stalo, kdybyste do toho šel?',
                            'Skutečná návnada: prvních 200 Kč vám klidně opravdu pošlou – schválně, abyste získal pocit, že je to bezpečné.',
                            'Past jménem „bílý kůň": v dalších dnech vám zadají úkoly, kde budete na svůj účet přijímat peníze a posílat je dál. V tu chvíli se z vás stává bílý kůň zneužívaný k praní špinavých peněz.',
                            'Problém s policií: jakmile na podvod přijde banka nebo policie, zablokují účet vám a budou stíhat vás. Argument, že jste o ničem nevěděl, vás neochrání – neznalost zákona neomlouvá. Zkusme to znovu a bezpečněji.',
                        ]),
                    ],
                    [
                        'name' => 'Chci více informací, na zprávu odepíšu.',
                        'right' => false,
                        'evaluation' => implode('<br><br>', [
                            'Zadržte! Chtít víc informací je logický krok, ale u podvodných SMS je samotná odpověď chybou.',
                            'Rozsvítíte zelenou: podvodníkům ve vteřině potvrdíte, že na čísle žije reálný člověk, který zprávy čte.',
                            'Zápis na seznam terčů: vaše číslo získá nálepku „aktivní", podvodníci si ho nasdílejí a začnou vás bombardovat dalšími SMS a falešnými telefonáty.',
                            'Past se zaklapne: jakmile odepíšete, vstoupíte do rozhovoru s vyškoleným manipulátorem (nebo botem), který má připravené odpovědi a dotlačí vás k registraci nebo poslání peněz.',
                            'Hlavní pravidlo: s autory podvodných SMS se nevyjednává. Zkusme to znovu a bezpečněji.',
                        ]),
                    ],
                ],
            ],
            [
                'button' => 3,
                'difficulty' => 3,
                'perex' => 'Žádost o převod peněz',
                'description' => 'Do mobilu vám přijde zpráva s profilovou fotkou muže v pracovním prostředí:<br><br>„Dobrý den, tady Novák z Centra finančních převodů. Dostal jsem na vás kontakt přes vaši bankovní poradkyni, prý jste spolehlivý. Potřebujeme dnes rychle dokončit převod peněz pro zahraničního klienta. Na váš účet přijde 48 000 Kč, které jen přepošlete dál podle instrukcí. Za pomoc dostanete 2 000 Kč. Abychom to mohli uskutečnit ještě dnes, pošlete prosím: číslo účtu, jméno k účtu, číslo platební karty, datum platnosti a CVC/CVV kód."',
                'safety_card' => 'Údaje z platební karty nikdy neposílejte cizím lidem přes zprávy.',
                'image' => ['path' => 'images/situations/e327a35a-637e-4af9-b947-4ed5f9db39ed.webp', 'alt' => 'SMS žádající o převod peněz a údaje z karty'],
                'options' => [
                    [
                        'name' => 'Požádám o více informací, odepíšu na zprávu.',
                        'right' => false,
                        'evaluation' => implode('<br><br>', [
                            'Zadržte. Chtít víc informací je logický krok, ale v této situaci je samotná odpověď riskem.',
                            'Pokud vám chce někdo poslat peníze, stačí mu jen číslo vašeho účtu. Jakmile po vás někdo chce číslo karty, platnost a třímístný CVC/CVV kód ze zadní strany, nechce vám peníze poslat, ale chce je z vaší karty ukrást.',
                            'Žádné „Centrum finančních převodů" neexistuje. Profilová fotka je ukradená z internetu a věta o vaší bankovní poradkyni je čistá lež. Banka by vaše číslo cizímu člověku nikdy nedala.',
                            'Když na zprávu odpovíte, potvrdíte podvodníkům, že o nabídce přemýšlíte, a začnou na vás zkoušet další podvody. Hlavní pravidlo: údaje z karty slouží jen tehdy, když vy sami platíte. Zkusme to znovu a bezpečněji.',
                        ]),
                    ],
                    [
                        'name' => 'Pošlu údaje k mojí kartě.',
                        'right' => false,
                        'evaluation' => implode('<br><br>', [
                            'POZOR! Tohle je to nejnebezpečnější rozhodnutí, které vás může v jedné vteřině připravit o všechny úspory.',
                            'Číslo karty, platnost a hlavně třímístný CVC/CVV kód slouží výhradně k placení. Podvodník je nepotřebuje k tomu, aby vám peníze poslal, ale aby je z vaší karty ukradl. K přijetí peněz stačí jen číslo účtu.',
                            'Jakmile tyto údaje napíšete, šmejdi s nimi začnou okamžitě nakupovat na internetu nebo peníze převedou do zahraničí. Než stihnete kartu zablokovat, účet může být prázdný.',
                            'Celý příběh o „panu Novákovi" a vaší bankovní poradkyni byla čistá lež. Hlavní pravidlo: údaje ze své karty nikdy nikomu neposílejte v textové zprávě. Zkusme to znovu.',
                        ]),
                    ],
                    [
                        'name' => 'Nic neposílám a ověřím si to u své banky.',
                        'right' => true,
                        'evaluation' => implode('<br><br>', [
                            'Výborně! Správně víte, že k přijetí peněz stačí jen číslo účtu. Chtít po vás číslo karty a třímístný CVC kód je jako žádat klíče od vašeho domácího trezoru.',
                            'Celé to divadlo o „panu Novákovi" a vaší bankovní poradkyni byla jen lež, která měla snížit vaši ostražitost. Banka by vaše kontakty cizímu člověku nikdy nedala.',
                            'Místo dohadování se s neznámým šmejdem jste zvolili jedinou správnou cestu – kontrolu u své skutečné banky přes její oficiální telefonní číslo. Skvělá práce.',
                        ]),
                    ],
                ],
            ],

            // ============ 4. herní kámen – falešný kupující ============
            [
                'button' => 4,
                'difficulty' => 1,
                'perex' => 'Falešný kupující',
                'description' => 'Na Facebooku prodáváte sekačku na trávu. Krátce po zveřejnění nabídky vám přijde zpráva od zájemce:<br><br>„Dobrý den, sekačku koupím. Bydlím daleko, proto objednám DPD kurýra, který sekačku vyzvedne přímo u vás doma, takže ji nemusíte nikam vozit. DPD vám pošle potvrzení dopravy a formulář k přijetí platby za prodané zboží. Stačí kliknout na odkaz, potvrdit objednávku kurýra a vyplnit číslo platební karty, datum platnosti a CVC kód."<br><br>Pod zprávou je odkaz.',
                'safety_card' => 'Na odkazy od neznámých lidí neklikejte a nic do nich nevyplňujte.',
                'image' => ['path' => 'images/situations/ae3a89d8-272f-4c3d-b59e-3c228ce0691f.webp', 'alt' => 'Zpráva od falešného kupujícího na bazaru'],
                'options' => [
                    [
                        'name' => 'Odkaz neotevřu a zprávu ignoruji.',
                        'right' => true,
                        'evaluation' => implode('<br><br>', [
                            'Skvěle, ignorovat tuhle zprávu byl ten nejlepší krok. Prokoukl jste tři velké bazarové lži.',
                            'Zneužití známé značky: podvodníci se schválně zaštiťují jménem jako DPD, Zásilkovna nebo PPL, protože těmto firmám věříte.',
                            'Obrácená logika placení: když vám chce někdo poslat peníze za zboží, stačí mu vaše číslo účtu. Nikdy k tomu nepotřebuje údaje z platební karty, a už vůbec ne CVC kód.',
                            'Falešné pohodlí: nabídka, že kupující všechno zařídí a pošle kurýra až k vašim dveřím, je jen trik, abyste bez přemýšlení klikli na odkaz.',
                            'Zaslaný odkaz by vás dovedl na falešnou stránku, která by vypadala jako web DPD; zadané údaje by podvodníci zneužili a ukradli peníze z účtu.',
                        ]),
                    ],
                    [
                        'name' => 'Na odkaz kliknu a případně vyplním, co bude potřeba.',
                        'right' => false,
                        'evaluation' => implode('<br><br>', [
                            'Poplach! Tohle je to nejnebezpečnější rozhodnutí, které vás v reálném životě může během jediné minuty připravit o veškeré úspory.',
                            'Číslo karty, platnost a hlavně CVC kód podvodník nepotřebuje k tomu, aby vám peníze poslal, ale aby je z vaší karty ukradl. K přijetí platby za sekačku stačí čisté číslo účtu.',
                            'Jakmile údaje zadáte, šmejdi okamžitě začnou nakupovat na internetu nebo peníze převedou do zahraničí. Než stihnete kartu zablokovat, účet může být prázdný.',
                            'Celá pohádka o tom, jak kupující vše zařídí a vy nemáte žádné starosti, byla jen návnada. Hlavní pravidlo: při prodeji dávejte zájemcům výhradně číslo svého bankovního účtu. Zkusme to znovu.',
                        ]),
                    ],
                    [
                        'name' => 'Odepíšu a zeptám se zájemce na více podrobností.',
                        'right' => false,
                        'evaluation' => implode('<br><br>', [
                            'Zadržte! Chtít se zeptat na podrobnosti je logický krok, ale v prostředí bazarů se psaním dalších zpráv nevědomky chytáte do pasti.',
                            'Vyškolení manipulátoři (nebo roboti): na druhé straně často nesedí skutečný nakupující, ale bot nebo vyškolený podvodník s připravenou odpovědí na každou pochybnost.',
                            'Falešné důkazy: klidně vám pošlou zfalšované obrázky, „oficiální" potvrzení od DPD nebo vymyšlené návody, že tento postup je bezpečný.',
                            'Útok na city: začnou tvrdit, že jsou maminky na mateřské, že už dopravu zaplatili a vy je chcete okrást, abyste ztratili ostražitost.',
                            'Hlavní pravidlo: jakmile zájemce poruší základní pravidlo bezpečného prodeje, okamžitě konverzaci ukončete. Zkusme to znovu a bezpečnější cestou.',
                        ]),
                    ],
                ],
            ],
            [
                'button' => 4,
                'difficulty' => 2,
                'perex' => 'Falešná kupující – odpočet',
                'description' => 'Na Facebooku prodáváte sekačku na trávu. Krátce po zveřejnění nabídky vám přijde zpráva od zájemkyně:<br><br>„Dobrý den, sekačku beru. Jsem teď v práci, proto jsem už zaplatila přes Zásilkovnu i dopravu a objednala kurýra k vám domů. Tady je odkaz na potvrzení objednávky a přijetí platby. Prosím potvrďte to do 5 minut, jinak se objednávka zruší, peníze se mi vrátí a budu to celé muset vyplňovat znovu."<br><br>Pod zprávou je odkaz. Běží odpočet času.',
                'safety_card' => 'Když vás někdo nutí spěchat, zastavte se.',
                'image' => ['path' => 'images/situations/bdbd55f7-bc05-4721-a569-b6ef3cfbaae0.webp', 'alt' => 'Falešná kupující s odpočtem času'],
                'options' => [
                    [
                        'name' => 'Odkaz neotevřu a zprávu ignoruji.',
                        'right' => true,
                        'evaluation' => implode('<br><br>', [
                            'Výborně! Nenechali jste se zahnat do kouta časovým nátlakem ani falešnou slušností. Prokoukli jste tři triky.',
                            'Odpočet času vás má vystresovat – v časové tísni lidé zmatkují a dělají chyby.',
                            'Věta, že to paní bude muset „vyplňovat znovu", je jen psychologické citové vydírání.',
                            'Šmejdi se schovávají za známou značku, aby získali důvěru. Zásilkovna ale takovým způsobem peníze nikdy neposílá.',
                            'Zaslaný odkaz by vás zavedl na věrnou kopii webu Zásilkovny, kde by po vás chtěli údaje z karty. Hlavní pravidlo: pro příjem platby dejte kupujícímu výhradně číslo svého bankovního účtu.',
                        ]),
                    ],
                    [
                        'name' => 'Na odkaz kliknu a vyplním, co bude potřeba.',
                        'right' => false,
                        'evaluation' => implode('<br><br>', [
                            'Poplach! Tohle je to nejnebezpečnější rozhodnutí, které vás v reálném životě může během jediné minuty připravit o veškeré úspory.',
                            'Jakmile do odkazu vyplníte číslo karty, platnost a CVC kód, předali jste podvodníkům kompletní přístup ke svým penězům. Tyto údaje kupující nepotřebuje k poslání peněz, slouží jen k jejich krádeži.',
                            'Podvodníci na nic nečekají – okamžitě začnou nakupovat nebo převedou peníze do zahraničí. Než stihnete kartu zablokovat, účet může být prázdný.',
                            'Celý pětiminutový odpočet a naříkání paní byl jen psychologický útok, který vás měl donutit jednat zbrkle. Hlavní pravidlo: nikdy nezadávejte údaje ze své karty do odkazů od cizích lidí. Zkusme to znovu.',
                        ]),
                    ],
                    [
                        'name' => 'Odepíšete zájemkyni a zeptáte se jí na podrobnosti.',
                        'right' => false,
                        'evaluation' => implode('<br><br>', [
                            'Zadržte! Chtít víc informací dává smysl, jenže v prostředí bazarů je psaní dalších zpráv přesně to, co podvodnice potřebuje.',
                            'Stroj na lži: na druhé straně nejspíš sedí bot nebo vyškolený manipulátor s okamžitou věrohodnou odpovědí; klidně pošle i zfalšovaný „oficiální návod" od Zásilkovny.',
                            'Stupňování nátlaku: když vyjádříte pochybnosti, podvodnice přitlačí: „Čas běží, zbývají 2 minuty, přijdeme o peníze!"',
                            'Potvrzení zájmu: tím, že odpovíte, dáváte najevo, že o obchod stojíte, a nátlak poroste.',
                            'Hlavní pravidlo: s lidmi, kteří na bazaru zkouší časový nátlak a posílají odkazy na vyplnění karty, se vůbec nediskutuje. Zkusme to znovu.',
                        ]),
                    ],
                ],
            ],
            [
                'button' => 4,
                'difficulty' => 3,
                'perex' => 'Falešná kupující',
                'description' => 'Na Facebooku prodáváte sekačku na trávu. Ozve se vám zájemkyně a působí velmi důvěryhodně:<br><br>„Dobrý den, je sekačka stále dostupná? Jak je stará? Je plně funkční? Máte k ní i koš? Prosím, poslala byste mi ještě detail motoru? Přesně takovou hledám pro rodiče na zahradu."<br><br>Po chvíli napíše: „Beru ji 👍 Jsem teď v práci, takže jsem už rovnou zaplatila přes Zásilkovnu i dopravu a objednala kurýra k vám domů, ať s tím nemáte starosti. Tady je odkaz na potvrzení přijetí platby. Stačí potvrdit objednávku, vyplnit číslo platební karty, datum platnosti a CVC kód, aby vám mohly být peníze připsány. Prosím udělejte to do 3 minut, jinak rezervace propadne a budu to celé muset vyplňovat znovu 😕"<br><br>Pod zprávou je odkaz.',
                'safety_card' => 'Na odkazy od neznámých lidí neklikejte a nic do nich nevyplňujte.',
                'image' => ['path' => 'images/situations/586f96e9-0004-42b1-bfae-b508beeaf404.webp', 'alt' => 'Falešná kupující předstírající poctivý zájem'],
                'options' => [
                    [
                        'name' => 'Odkaz neotevřu a konverzaci rovnou ukončím.',
                        'right' => true,
                        'evaluation' => implode('<br><br>', [
                            'Výborně! Víte, co všechno na vás podvodnice zkoušela? Otázky na stáří sekačky, funkčnost nebo fotku motoru měly vyvolat dojem, že jednáte s poctivým člověkem. Příběh o rodičích a zahradě útočí na vaše city.',
                            'Jakmile dojde na peníze, ze slušné paní je najednou diktátor. Limit 3 minuty vás má vystresovat, abyste ve spěchu zapomněli na bezpečnostní pravidla.',
                            'Zaslaný odkaz by vás zavedl na falešnou stránku Zásilkovny, kde by po vás chtěli CVC kód a číslo karty – místo příjmu by vám ale ukradli úspory.',
                            'Hlavní pravidlo: pro příjem peněz na bazaru stačí vždy jen číslo vašeho bankovního účtu. Jakmile někdo chce údaje z karty nebo vás honí časem, okamžitě konverzaci ukončete.',
                        ]),
                    ],
                    [
                        'name' => 'Kliknu na odkaz a vyplním, co je potřeba.',
                        'right' => false,
                        'evaluation' => implode('<br><br>', [
                            'Poplach! Tohle je to nejnebezpečnější rozhodnutí, které vás v reálném životě může během jediné minuty připravit o všechny úspory.',
                            'Maska poctivého kupujícího: otázky na funkčnost sekačky nebo detail motoru byly jen sehrané divadlo, abyste ztratil ostražitost.',
                            'Citové vydírání: příběh o rodičích na zahradě a stížnosti, jak to paní bude muset vyplňovat znovu, zaútočily na vaši ochotu pomoci.',
                            'Tím, že jste do falešného formuláře vyplnili číslo karty, platnost a CVC kód, jste podvodníkům otevřeli své bankovní konto.',
                            'Hlavní pravidlo: když na bazaru prodáváte, peníze vám mohou přijít výhradně na číslo bankovního účtu. Nikdy nezadávejte údaje z karty do odkazů od cizích lidí. Zkusme to znovu.',
                        ]),
                    ],
                    [
                        'name' => 'Napíšu zájemkyni, že mi odkaz nefunguje.',
                        'right' => false,
                        'evaluation' => implode('<br><br>', [
                            'Zadržte, kapitáne! Váš instinkt nezadávat údaje hned je správný, ale odpověď „odkaz nefunguje" drží komunikaci otevřenou.',
                            'Druhá strana vám pošle nový odkaz, začne vás navádět dál nebo zvýší tlak, abyste vše rychle dokončili. Podvod tak pokračuje.',
                            'Působí to důvěryhodně: nejprve běžné dotazy, pak pohodlí („všechno už jsem zařídila") a nakonec časový tlak („máte 3 minuty"). Kombinace důvěry, emocí a spěchu snižuje vaši pozornost.',
                            'Realita: pro přijetí peněz nepotřebujete zadávat číslo karty ani CVC kód – běžná platba přijde převodem na účet. Správný postup: na odkaz nereagovat, nic nevyplňovat a komunikaci ukončit.',
                        ]),
                    ],
                ],
            ],

            // ============ 5. herní kámen – falešné výhry a balíčky ============
            [
                'button' => 5,
                'difficulty' => 1,
                'perex' => 'SMS o výhře',
                // Pozn.: ve scénáři chyběly texty prvních dvou tlačítek i „Odpověď systému";
                // dorekonstruováno podle reakcí strážce a obdobných situací (5. kámen / obt. 2 a 3).
                'description' => 'Brzy ráno vás probudí pípnutí SMS na telefonu. Proberete se a podíváte se na ni:<br><br>„Gratulujeme! Vyhrál jste balíček zdravotních služeb zdarma. Pro vyzvednutí výhry klikněte zde: lsfdjgh17ONTkjxnf2gfj4"',
                'safety_card' => 'Na podezřelé odkazy neklikejte a nic do nich nevyplňujte.',
                'image' => ['path' => 'images/situations/c691ed70-d29c-4299-9c6a-164eba5ebd0f.webp', 'alt' => 'SMS o výhře zdravotního balíčku zdarma'],
                'options' => [
                    [
                        'name' => 'Odkaz neotevřu a zprávu ignoruji.',
                        'right' => true,
                        'evaluation' => implode('<br><br>', [
                            'Výborně! Tímto krokem jste prohlédli podvod, který sází na tyto věci.',
                            'Útok na rozespalost: podvodníci záměrně posílají tyto SMS brzy ráno nebo v noci. Spoléhají na to, že budete ještě rozespalí, vaše pozornost oslabená, a kliknete dřív, než se vám nastartuje kritické myšlení.',
                            'Zneužití tématu zdraví: vědí, že zdraví je pro starší lidi prioritou, a sliby „zdravotních služeb zdarma" používají jako nejlákavější návnadu.',
                            'Nesmyslný odkaz: oficiální instituce by vám nikdy neposlaly odkaz složený ze změti náhodných znaků.',
                            'Hlavní pravidlo: v online světě nikdy nevyhrajete v soutěži, do které jste se sami vědomě nepřihlásili.',
                        ]),
                    ],
                    [
                        'name' => 'Na odkaz kliknu a výhru si vyzvednu.',
                        'right' => false,
                        'evaluation' => implode('<br><br>', [
                            'Poplach! Tohle je velmi nebezpečný krok, který vás v reálném životě může okamžitě připravit o peníze nebo kontrolu nad telefonem.',
                            'Šmejdi vás schválně probudili brzy ráno – spoléhají, že rozespalý člověk je snazší oběť. Ta podivná změť písmen v odkazu je jasný varovný signál.',
                            'Stránka by po vás pod záminkou „doručení výhry" chtěla osobní údaje, rodné číslo, nebo malý poplatek za poštovné kartou. Už samotné kliknutí může do mobilu tajně stáhnout vir.',
                            'Hlavní pravidlo: nikdy nevyhrajete v soutěži, do které jste se sami nepřihlásili. Pokud přijde zpráva o výhře z ničeho nic, je to na 100 % podvod. Zkusme to znovu.',
                        ]),
                    ],
                    [
                        'name' => 'Na zprávu odpovím a požádám o víc informací.',
                        'right' => false,
                        'evaluation' => implode('<br><br>', [
                            'Zadržte! Chtít víc informací dává v běžném životě smysl, ale ve světě podezřelých SMS je odpověď nebezpečná.',
                            'Podvodníkům (nebo jejich automatickým systémům) ve vteřině potvrdíte: „Na tomto čísle žije reálný člověk, který zprávy čte a reaguje na ně."',
                            'Vaše číslo okamžitě získá nálepku „aktivní". Šmejdi si ho nasdílejí nebo prodají dál a začnou vás bombardovat dalšími SMS a falešnými telefonáty.',
                            'Tyto hromadné zprávy většinou rozesílají boti – skutečných podrobností se nedočkáte, systém vám akorát pošle ten samý nebezpečný odkaz znovu. Nejbezpečnější je neodpovídat a zprávu smazat. Zkusme to znovu.',
                        ]),
                    ],
                ],
            ],
            [
                'button' => 5,
                'difficulty' => 2,
                'perex' => 'SMS o zdravotním balíčku',
                'description' => 'Brzy ráno vás probudí SMS:<br><br>„Vážený pane Nováku, jako dlouhodobý klient jste získal nárok na balíček zdravotních služeb zdarma. Aktivujte si jej zde: www.zdravi-servis.cz/aktivace"',
                'safety_card' => 'Na podezřelé odkazy neklikejte a nic do nich nevyplňujte.',
                'image' => ['path' => 'images/situations/f6ef64c3-4dea-4b72-b90b-9fe8d5e51015.webp', 'alt' => 'SMS o zdravotním balíčku s odkazem'],
                'options' => [
                    [
                        'name' => 'Odkaz neotevřu a zprávu ignoruji.',
                        'right' => true,
                        'evaluation' => implode('<br><br>', [
                            'Výborně! Prokoukli jste tři velmi rafinované triky.',
                            'Oslovení „Vážený pane Nováku" má vyvolat pocit, že zpráva je oficiální a adresovaná vám. Podvodníci ale běžně nakupují uniklé databáze, kde jsou jména spárovaná s čísly.',
                            'Slib „balíčku za dlouhodobé klientství" útočí na emoce – každého potěší ocenění za věrnost; téma zdraví je navíc nejlákavější návnada.',
                            'Adresa www.zdravi-servis.cz nevypadá jako změť znaků, takže působí důvěryhodně. Je to ale jen maskovaná past, kterou si podvodníci zaregistrovali.',
                            'Tento odkaz by vás zavedl na falešný portál; pod záminkou „aktivace balíčku" by z vás vytáhli citlivé údaje nebo vás přiměli k přihlášení přes Bankovní identitu, čímž by získali přístup k vašemu účtu.',
                        ]),
                    ],
                    [
                        'name' => 'Na odkaz kliknu a balíček si aktivuji.',
                        'right' => false,
                        'evaluation' => implode('<br><br>', [
                            'Poplach! Tohle rozhodnutí vás v reálném životě může během několika vteřin připravit o přístup k celému bankovnímu účtu.',
                            'Past zvaná Bankovní identita: abyste si balíček „aktivovali", stránka po vás bude pod záminkou ověření totožnosti chtít přihlášení přes Bankovní identitu (nebo údaje z karty). Tím podvodníkům předáte kompletní přístup do bankovního účtu a mohou ukrást úspory nebo si na vaše jméno vzít úvěr.',
                            'To, že vás SMS oslovila jménem, nebyla náhoda – jména spárovaná s čísly se dají koupit z uniklých databází. Spolehli se na to, že když uvidíte své jméno, ztratíte ostražitost.',
                            'Skutečné pojišťovny nebo zdravotní instituce takto náhle bonusy přes SMS nerozdávají. Zkusme to znovu.',
                        ]),
                    ],
                    [
                        'name' => 'Na zprávu odpovím a požádám o více informací.',
                        'right' => false,
                        'evaluation' => implode('<br><br>', [
                            'Zadržte! Snaha zjistit víc informací zní logicky, ale u podvodných SMS je už samotná odpověď rizikem.',
                            'Podvodníkům svou odpovědí ve vteřině potvrdíte to nejdůležitější: že číslo funguje a majitel zprávy čte a reaguje.',
                            'Vaše číslo získá větší hodnotu, šmejdi si ho označí jako „aktivní" a často prodají dalším podvodníkům – množství útoků může vzrůstat.',
                            'Skutečné podrobnosti se nedozvíte: buď vám robot pošle ten samý nebezpečný odkaz znovu, nebo si vás převezme vyškolený manipulátor. Zkusme to znovu.',
                        ]),
                    ],
                ],
            ],
            [
                'button' => 5,
                'difficulty' => 3,
                'perex' => 'SMS o zdravotním programu',
                'settings' => ['time_limit' => 59],
                'description' => 'Brzy ráno vám přijde SMS:<br><br>„Vážený pane Nováku, byl Vám přidělen zdravotní balíček v rámci programu prevence. Pro více informací a aktivaci klikněte zde: www.zdravi-program.cz/overeni"',
                'safety_card' => 'Na podezřelé odkazy neklikejte a nic do nich nevyplňujte.',
                'image' => ['path' => 'images/situations/d138d317-487f-4042-9c97-808ba4e318f2.webp', 'alt' => 'SMS o zdravotním programu s odkazem a časomírou'],
                'options' => [
                    [
                        'name' => 'Odkaz neotevřu a zprávu ignoruji.',
                        'right' => true,
                        'evaluation' => implode('<br><br>', [
                            'Výborně! Prokoukli jste tři triky podvodníků.',
                            'Výrazy jako „program prevence" nebo „potvrzení" mají vyvolat dojem, že komunikujete se státem, ministerstvem nebo pojišťovnou. Téma zdraví zneužívají záměrně.',
                            'Slovní spojení „byl Vám přidělen" ve vás má vyvolat pocit, že na balíček máte automatické právo a byla by škoda o výhodu přijít.',
                            'Web www.zdravi-program.cz je jen rychlá zástěrka, kterou si podvodníci sami zaregistrovali. Skutečné instituce takto bonusy přes SMS nerozdávají.',
                            'Odkaz by vás zavedl na falešný web; pod záminkou „ověření a aktivace" by z vás vytáhli osobní údaje nebo vás přiměli k přihlášení přes Bankovní identitu. Pokud máte pochybnosti, ověřte si nabídku sami – třeba zavoláním na oficiální infolinku své pojišťovny.',
                        ]),
                    ],
                    [
                        'name' => 'Na odkaz kliknu a balíček si aktivuji.',
                        'right' => false,
                        'evaluation' => implode('<br><br>', [
                            'Poplach! Tohle rozhodnutí vás v reálném životě může během několika málo vteřin připravit o veškeré úspory na bankovním účtu.',
                            'Abyste si balíček „aktivovali" a „ověřili totožnost", stránka po vás bude chtít přihlášení přes Bankovní identitu. Jakmile údaje zadáte, předáte podvodníkům kompletní přístup do bankovnictví a mohou ukrást peníze nebo si na vaše jméno vzít úvěr.',
                            'Výrazy „program prevence" a „byl Vám přidělen" mají vyvolat dojem komunikace se státní institucí a pocit, že na balíček máte nárok.',
                            'Hlavní pravidlo: přihlašovací údaje do banky a Bankovní identitu používejte jen tehdy, když do bankovnictví vstupujete vy sami přes oficiální aplikaci nebo ručně zadanou adresu. Nikdy ne přes odkazy z SMS. Zkusme to znovu.',
                        ]),
                    ],
                    [
                        'name' => 'Na zprávu odpovím a požádám o více informací.',
                        'right' => false,
                        'evaluation' => implode('<br><br>', [
                            'Zadržte! Snaha zjistit víc informací zní logicky, ale ve světě podezřelých SMS je odpověď nebezpečná.',
                            'Automatickým systémům (botům) okamžitě potvrdíte: „Toto číslo funguje, jeho majitel zprávy čte a reaguje."',
                            'Vaše číslo v tu ránu získá velkou hodnotu – šmejdi si ho označí jako „aktivní" a často prodají dalším podvodníkům.',
                            'Žádné skutečné podrobnosti o „programu prevence" se nedozvíte: buď nikdo neodpoví, nebo se ozve podvodník, který bude zvyšovat nátlak. Nejbezpečnější je neodpovídat a zprávu smazat. Zkusme to znovu.',
                        ]),
                    ],
                ],
            ],
        ];
    }

    /**
     * Bonusová „bezpečná" otázka – nabídka práce od skutečného bývalého kolegy.
     * @return array<string, mixed>
     */
    private function bonusQuestion(): array
    {
        return [
            'difficulty' => 1,
            'perex' => 'Nabídka práce od kolegy',
            'description' => 'Na WhatsApp vám přijde zpráva z uloženého kontaktu „Petr – bývalý kolega":<br><br>„Ahoj, nechci tě otravovat 🙂 Vím, že jsi říkal, že by se ti hodil nějaký menší přivýdělek. U nás ve firmě teď hledáme někoho na výpomoc s administrativou – nic složitého, pár hodin týdně. Je to normálně na smlouvu, všechno oficiálně přes firmu. Když budeš chtít, můžu tě propojit přímo s kolegyní, pošle ti detaily."',
            'image' => ['path' => 'images/situations/6e929dc1-5434-482d-8a34-9de3218796f4.webp', 'alt' => 'Zpráva s nabídkou práce od bývalého kolegy'],
            'options' => [
                [
                    'name' => 'Kolegovi odpovím a poprosím o detaily.',
                    'right' => true,
                    'evaluation' => implode('<br><br>', [
                        'Skvěle! Správně jste rozpoznali, že svět na internetu není jen plný nástrah, ale že v něm stále probíhá i úplně běžný, bezpečný život.',
                        'Skutečný známý: píše vám kontakt, který máte prokazatelně uložený v telefonu pod jeho jménem. Logická návaznost: zpráva navazuje na vaši skutečnou minulost – o přivýdělku jste spolu kdysi opravdu mluvili.',
                        'Žádné pasti: v textu není podezřelý odkaz, nikdo po vás nechce údaje z karty ani přihlášení do banky a nikdo na vás netlačí časem.',
                        'Hlavní pravidlo: cílem opatrnosti je odhalovat šmejdy, ne ignorovat vlastní přátele a reálné příležitosti. Dokud po vás nikdo nechce citlivá data nebo peníze, komunikovat v klidu můžete.',
                    ]),
                ],
                [
                    'name' => 'Číslo radši zablokuji.',
                    'right' => false,
                    'evaluation' => implode('<br><br>', [
                        'Je naprosto v pořádku, že chcete mít jistotu. Opatrnost je dnes nutná, ale v tomto případě reagujete až příliš přísně.',
                        'Znáte se: píše vám bývalý kolega, kterého máte uloženého v telefonu. Není to cizí číslo. Chybí varovné znaky: žádný podezřelý odkaz, nikdo po vás nechce peníze, údaje z karty ani vás netlačí časem.',
                        'Zablokováním Petra byste zbytečně přišli o kontakt se známým člověkem i o reálnou nabídku přivýdělku. Cílem bezpečnosti je chránit se před podvodníky, ne se odříznout od přátel. Zkusme to znovu.',
                    ]),
                ],
                [
                    'name' => 'Petrovi zavolám.',
                    'right' => true,
                    'evaluation' => implode('<br><br>', [
                        'Výborně! Zavolat Petrovi je skvělá volba, protože tak máte okamžitou jistotu, že mluvíte opravdu s ním.',
                        'V této situaci je ale úplně stejně v pořádku i druhá možnost – normálně mu na zprávu odepsat. Ve zprávě není podezřelý odkaz, Petr po vás nechce peníze ani hesla a nikdo na vás netlačí časem, a píše vám uložený kontakt.',
                        'Zavolat i odepsat je tu naprosto v pořádku.',
                    ]),
                ],
            ],
        ];
    }
};
