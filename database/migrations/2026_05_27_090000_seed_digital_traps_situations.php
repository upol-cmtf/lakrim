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
 * Naplní Ostrov digitálních pastí – 5 herních kamenů (tlačítek),
 * každý se třemi situacemi o obtížnosti 1–3 (15 otázek celkem).
 * Otázky jsou verze 3, bez skupiny.
 */
return new class extends Migration {
    public function up(): void
    {
        $island = Island::query()->where('image', 'digital_traps.webp')->first();

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
        $island = Island::query()->where('image', 'digital_traps.webp')->first();

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
                'perex' => 'ČSSZ – mimořádný příspěvek',
                'description' => '[[image:situace]]<p>Na mobil vám přijde SMS z neznámého čísla <strong>+420 736 184 902</strong>:</p><p>„ČSSZ: Máte nárok na mimořádný příspěvek 4 800 Kč.<br>Pro vyplacení potvrďte žádost zde:<br>cssz-prispevek.cz/vyplata“</p><p>Zpráva působí jako oznámení od úřadu.</p>',
                'safety_card' => null,
                'image' => [
                    'key' => 'situace',
                    'path' => 'images/situations/ddb80889-e28c-47c4-9ecc-6c04332b24fe.webp',
                    'alt' => 'ČSSZ – mimořádný příspěvek',
                ],
                'options' => [
                    [
                        'name' => 'Kliknu na odkaz a vyplním potřebné údaje',
                        'right' => false,
                        'evaluation' => 'Zadržte!<br><br>Tohle je extrémně nebezpečná digitální past.<br><br>Tato zpráva útočí na vaše emoce – konkrétně na radost z nečekaného finančního zisku.<br><br>Podvodníci zneužívají jméno známého úřadu, aby získali vaši důvěru.<br><br>Pokud na odkaz kliknete a vyplníte údaje, neotevře se vám úřední formulář, ale falešná stránka, která z vás vyláká přístupy k vašemu bankovnímu účtu.<br><br>Během chvíle byste mohli přijít o všechny úspory.<br><br>Zkuste to znovu.',
                    ],
                    [
                        'name' => 'Odepíšu na zprávu a zeptám se na podrobnosti',
                        'right' => false,
                        'evaluation' => 'Pozor. Odepisovat na podezřelé zprávy je vždy rizikové a z technických důvodů na ně navíc často ani nelze přímo odpovědět.<br><br>I kdyby vám systém odepsat dovolil, jakákoliv reakce je pro podvodníky cenná informace – potvrdíte jim tím, že vaše telefonní číslo je aktivní, že zprávy čtete a že jste na ně ochotní reagovat. Tím se okamžitě dostanete na seznam „živých cílů“ a podvodníci vás začnou bombardovat dalšími falešnými zprávami a nevyžádanými hovory.<br><br>Že je zpráva od úřadu v pořádku a bezpečná, poznáte podle toho, že vám přijde do oficiální Datové schránky, nebo vás úřad vyzve, abyste se sami přihlásili přes zabezpečenou Identitu občana na jejich oficiální adrese.<br><br>Nikdy vám nepošlou přímý odkaz v SMSce.<br><br>Zkuste to znovu.',
                    ],
                    [
                        'name' => 'Zprávu rovnou smažu, úřady takhle nekomunikují',
                        'right' => true,
                        'evaluation' => 'Vynikající!<br><br>Správně jste vyhodnotili, že státní úřady takto s občany nekomunikují.<br><br>Odhalili jste klíčové varovné znaky: zpráva přišla z obyčejného mobilního čísla a odkaz je falešný.<br><br>Že je zpráva od úřadu v pořádku a bezpečná, poznáte podle toho, že vám přijde do oficiální Datové schránky, nebo vás úřad vyzve, abyste se sami přihlásili přes zabezpečenou Identitu občana na jejich oficiální adrese.<br><br>Skvělá práce.',
                    ],
                ],
            ],
            [
                'button' => 1,
                'difficulty' => 2,
                'perex' => 'ČSSZ – příspěvek na bydlení',
                'description' => '[[image:situace]]<p>Na mobil vám přijde SMS z neznámého čísla <strong>+420 736 184 902</strong>:</p><p>„ČSSZ: Byl vám předběžně schválen příspěvek na bydlení.<br>Pro dokončení žádosti ověřte svou totožnost přes bankovní identitu zde:<br>mpsv-podpora-bydleni.cz/overeni“</p><p>Zpráva se vydává za státní instituci a slibuje vyplacení příspěvku.</p>',
                'safety_card' => null,
                'image' => [
                    'key' => 'situace',
                    'path' => 'images/situations/a013c8fb-da6f-4b00-b38d-b8f04c3d2159.webp',
                    'alt' => 'ČSSZ – příspěvek na bydlení',
                ],
                'options' => [
                    [
                        'name' => 'Kliknu na odkaz a ověřím se přes svou bankovní identitu.',
                        'right' => false,
                        'evaluation' => 'Zadržte!<br><br>Tohle je ta nejnebezpečnější digitální past.<br><br>Text útočí na vaše emoce – na vidinu finanční pomoci s bydlením a úlevy od výdajů.<br><br>Podvodníci zneužívají jméno ministerstva a ČSSZ, aby zvýšili důvěryhodnost.<br><br>Pokud na odkaz kliknete a zadáte údaje ke své bankovní identitě, nepřihlásíte se na úřad, ale předáte podvodníkům plný přístup ke svému bankovnímu účtu.<br><br>Během několika minut byste mohli přijít o všechny celoživotní úspory.<br><br>Zkuste to znovu.',
                    ],
                    [
                        'name' => 'Odepíšu na zprávu a zeptám se na podrobnosti',
                        'right' => false,
                        'evaluation' => 'Pozor.  Odepisovat na podezřelé zprávy je vždy rizikové a z technických důvodů na ně navíc často ani nelze přímo odpovědět.<br><br>I kdyby vám systém odepsat dovolil, jakákoliv reakce je pro podvodníky cenná informace – potvrdíte jim tím, že vaše telefonní číslo je aktivní, že zprávy čtete a že jste na ně ochotní reagovat. Tím se okamžitě dostanete na seznam „živých cílů“ a podvodníci vás začnou bombardovat dalšími falešnými zprávami a nevyžádanými hovory.<br><br>Oficiální výzvy chodí výhradně do Datové schránky.<br><br>Pokud si chcete příspěvek ověřit, vždy sami ručně napište do prohlížeče oficiální adresu úřadu (mpsv.cz nebo cssz.cz).<br><br>Nikdy neklikejte na podobné odkazy v textových zprávách.<br><br>Zkuste to znovu.',
                    ],
                    [
                        'name' => 'Zprávu rovnou smažu, úřady takhle nekomunikují',
                        'right' => true,
                        'evaluation' => 'Vynikající!<br><br>Správně jste vyhodnotili, že státní úřady takto s občany nekomunikují.<br><br>Odhalili jste klíčové varovné znaky: zpráva přišla z běžného mobilního čísla a odkaz v textu byl falešný.<br><br>Že je zpráva od úřadu v pořádku a bezpečná, poznáte podle toho, že státní instituce nikdy neposílají odkazy na přihlášení k bankovní identitě v obyčejné SMSce.<br><br>Oficiální výzvy chodí výhradně do Datové schránky.<br><br>Pokud si chcete příspěvek ověřit, vždy sami ručně napište do prohlížeče oficiální adresu úřadu (mpsv.cz nebo cssz.cz)<br><br>Skvělá práce',
                    ],
                ],
            ],
            [
                'button' => 1,
                'difficulty' => 3,
                'perex' => 'ČSSZ – doplatek k důchodu',
                'description' => '[[image:situace]]<p>Na mobil vám přijde SMS z neznámého čísla <strong>+420 736 184 902</strong>:</p><p>„ČSSZ: Byla zjištěna možnost doplatku k vašemu důchodu.<br>Žádost je nutné potvrdit do 24 hodin, jinak bude nárok zrušen:<br>eportal-cssz-duchod.cz/prihlaseni“</p><p>Zpráva působí naléhavě a odkaz vypadá podobně jako ePortál ČSSZ.</p><p><strong>Časomíra:</strong> 1 minuta</p>',
                'safety_card' => null,
                'image' => [
                    'key' => 'situace',
                    'path' => 'images/situations/4590fa28-d10a-45ab-8647-3d496c6647d7.webp',
                    'alt' => 'ČSSZ – doplatek k důchodu',
                ],
                'options' => [
                    [
                        'name' => 'Kliknu na odkaz a vyplním údaje',
                        'right' => false,
                        'evaluation' => 'Zadržte!<br><br>Tohle je ta nejnebezpečnější digitální past.<br><br>Text útočí na vaše emoce – možnost získání doplatku k důchodu.<br><br>Podvodníci zneužívají jméno ministerstva a ČSSZ, aby zvýšili důvěryhodnost.<br><br>Pokud na odkaz kliknete a zadáte údaje ke své bankovní identitě, nepřihlásíte se na úřad, ale předáte podvodníkům plný přístup ke svému bankovnímu účtu.<br><br>Během několika minut byste mohli přijít o všechny celoživotní úspory.<br><br>Zkuste to znovu.',
                    ],
                    [
                        'name' => 'Odepíšu na zprávu a zeptám se na podrobnosti',
                        'right' => false,
                        'evaluation' => 'Pozor.  Odepisovat na podezřelé zprávy je vždy rizikové a z technických důvodů na ně navíc často ani nelze přímo odpovědět.<br><br>I kdyby vám systém odepsat dovolil, jakákoliv reakce je pro podvodníky cenná informace – potvrdíte jim tím, že vaše telefonní číslo je aktivní, že zprávy čtete a že jste na ně ochotní reagovat. Tím se okamžitě dostanete na seznam „živých cílů“ a podvodníci vás začnou bombardovat dalšími falešnými zprávami a nevyžádanými hovory.<br><br>Že je zpráva od úřadu v pořádku a bezpečná, poznáte podle toho, že státní instituce nikdy neposílají odkazy na přihlášení k bankovní identitě v obyčejné SMSce.<br><br>Oficiální výzvy chodí výhradně do Datové schránky.<br><br>Pokud si chcete příspěvek ověřit, vždy sami ručně napište do prohlížeče oficiální adresu úřadu (mpsv.cz nebo cssz.cz).<br><br>Nikdy neklikejte na podobné odkazy v textových zprávách.<br><br>Zkuste to znovu.',
                    ],
                    [
                        'name' => 'Zprávu rovnou smažu, úřady s lidmi takhle nekomunikují',
                        'right' => true,
                        'evaluation' => 'Vynikající!<br><br>Správně jste vyhodnotili, že státní úřady takto s občany nekomunikují.<br><br>Odhalili jste klíčové varovné znaky: zpráva přišla z běžného mobilního čísla a odkaz v textu byl falešný.<br><br>Že je zpráva od úřadu v pořádku a bezpečná, poznáte podle toho, že státní instituce nikdy neposílají odkazy na přihlášení k bankovní identitě v obyčejné SMSce.<br><br>Oficiální výzvy chodí výhradně do Datové schránky.<br><br>Pokud si chcete příspěvek ověřit, vždy sami ručně napište do prohlížeče oficiální adresu úřadu (mpsv.cz nebo cssz.cz)<br><br>Skvělá práce',
                    ],
                ],
            ],

            // ===== DRUHÝ herní kámen =====
            [
                'button' => 2,
                'difficulty' => 1,
                'perex' => 'Česká pošta – balíček',
                'description' => '[[image:situace]]<p>Na mobil vám přijde SMS z neznámého čísla <strong>+420 737 450 219</strong>:</p><p>„Česká pošta: Váš balíček čeká na doručení.<br>Pro dokončení doručení doplaťte poštovné 49 Kč zde:<br>posta-doruceni.cz/platba“</p>',
                'safety_card' => null,
                'image' => [
                    'key' => 'situace',
                    'path' => 'images/situations/90a9cae8-1b48-4929-a7d9-663105c35ca9.webp',
                    'alt' => 'Česká pošta – balíček',
                ],
                'options' => [
                    [
                        'name' => 'Kliknu na odkaz, ať můžu zaplatit poplatek',
                        'right' => false,
                        'evaluation' => 'Zadržte!<br><br>První a nejdůležitější otázka u takových zpráv vždy zní: Čekáte vůbec nějaký balíček? Pokud ne, je to jasné varování.<br><br>Tento text rafinovaně útočí na vaše emoce – na zvědavost, co by v zásilce mohlo být, a na touhu mít věc rychle vyřízenou.<br><br>Částka 49 Kč je schválně nízká, aby nevzbudila podezření.<br><br>Pokud však na odkaz kliknete a zadáte údaje ze své platební karty, nepošlete čtyřicet devět korun poště, ale odevzdáte kompletní přístup ke svému účtu podvodníkům.<br><br>Že jde o podvod, přitom poznáte snadno:<br><br>Česká pošta nikdy neposílá odkazy na platební brány v SMS zprávách z neznámých mobilních čísel. Skutečné upozornění o balíčku vždy obsahuje unikátní podací číslo zásilky. To, zda je zpráva realita, si můžete bezpečně ověřit tak, že toto číslo sami zadáte do oficiálního vyhledávače přímo na webu České pošty. Pokud žádné číslo nemáte nebo odkaz v SMS vypadá jako zkomolenina, je to jasný podvod. Tímto kliknutím byste mohli přijít o veškeré úspory.<br><br>Zkuste to znovu.',
                    ],
                    [
                        'name' => 'Odepíšu na zprávu a zeptám se, o jaký balíček jde',
                        'right' => false,
                        'evaluation' => 'Snaha získat podrobnosti je rozumná, ale odepisovat na takové zprávy bohužel nikam nevede.<br><br>Nejprve je dobré položit si tu nejjednodušší otázku: Čekáte vůbec nějaký balíček? Pokud ne, je to první jasný důkaz, že jde o podvod.<br><br>Odepisovat na podezřelé zprávy je vždy rizikové a z technických důvodů na ně navíc často ani nelze přímo odpovědět.<br><br>I kdyby vám systém odepsat dovolil, jakákoliv reakce je pro podvodníky cenná informace – potvrdíte jim tím, že vaše telefonní číslo je aktivní, že zprávy čtete a že jste na ně ochotní reagovat. Tím se okamžitě dostanete na seznam „živých cílů“ a podvodníci vás začnou bombardovat dalšími falešnými zprávami a nevyžádanými hovory.<br><br>Že je zpráva od doručovací služby realita, poznáte podle toho, že Česká pošta nikdy neposílá odkazy na platby v SMSkách. Skutečné upozornění vždy obsahuje unikátní podací číslo. To můžete sami ručně zadat do vyhledávače přímo na oficiálním webu České pošty.<br><br>Pokud žádné číslo nemáte, zprávu ignorujte.<br><br>Zkuste to znovu.',
                    ],
                    [
                        'name' => 'Zprávu rovnou smažu, Česká pošta takhle nekomunikuje',
                        'right' => true,
                        'evaluation' => 'Vynikající!<br><br>Položili jste si tu nejdůležitější otázku: zda vůbec nějaký balíček čekáte, a nenechali jste se zlákat zvědavostí ani podezřele nízkou částkou 49 Kč.<br><br>Správně jste vyhodnotili, že Česká pošta takovým způsobem doplatky nikdy nevyžaduje.<br><br>Odhalili jste klíčové varovné znaky: zpráva přišla z neznámého mobilního čísla a internetová adresa je falešná.<br><br>Skutečné upozornění o balíčku od pošty vždy obsahuje unikátní podací číslo zásilky, které si můžete sami bezpečně ověřit přímo na jejich oficiálním webu.<br><br>Tím, že jste zprávu smazali, jste udrželi své peníze i platební kartu v naprostém bezpečí. Skvělá práce',
                    ],
                ],
            ],
            [
                'button' => 2,
                'difficulty' => 2,
                'perex' => 'Česká pošta – pozastavená zásilka',
                'description' => '[[image:situace]]<p>Na mobil vám přijde SMS z neznámého čísla <strong>+420 732 804 611</strong>:</p><p>„Česká pošta: Pane Nováku, doručení vaší zásilky bylo pozastaveno z důvodu nedoplatku 49 Kč.<br>Pro pokračování doručení uhraďte poplatek zde:<br>ceskaposta-doplatek.cz/zasilka“</p>',
                'safety_card' => null,
                'image' => [
                    'key' => 'situace',
                    'path' => 'images/situations/b5ce2407-4b88-4d0e-a619-19bc9e2a0acc.webp',
                    'alt' => 'Česká pošta – pozastavená zásilka',
                ],
                'options' => [
                    [
                        'name' => 'Kliknu na odkaz, ať můžu zaplatit poplatek',
                        'right' => false,
                        'evaluation' => 'Zadržte!<br><br>První a nejdůležitější otázka u takových zpráv vždy zní: Čekáte vůbec nějaký balíček? Pokud ne, je to jasné varování.<br><br>Tento text rafinovaně útočí na vaše emoce – na zvědavost, co by v zásilce mohlo být, a na touhu mít věc rychle vyřízenou.<br><br>Částka 49 Kč je schválně nízká, aby nevzbudila podezření.<br><br>Pokud však na odkaz kliknete a zadáte údaje ze své platební karty, nepošlete čtyřicet devět korun poště, ale odevzdáte kompletní přístup ke svému účtu podvodníkům.<br><br>Že jde o podvod, přitom poznáte snadno:<br><br>Česká pošta nikdy neposílá odkazy na platební brány v SMS zprávách z neznámých mobilních čísel. Skutečné upozornění o balíčku vždy obsahuje unikátní podací číslo zásilky. To, zda je zpráva realita, si můžete bezpečně ověřit tak, že toto číslo sami zadáte do oficiálního vyhledávače přímo na webu České pošty. Pokud žádné číslo nemáte nebo odkaz v SMS vypadá jako zkomolenina, je to jasný podvod. Tímto kliknutím byste mohli přijít o veškeré úspory.<br><br>Zkuste to znovu.',
                    ],
                    [
                        'name' => 'Odepíšu na zprávu a zeptám se, o jaký balíček jde',
                        'right' => false,
                        'evaluation' => 'Snaha získat podrobnosti je rozumná, ale odepisovat na takové zprávy bohužel nikam nevede.<br><br>Nejprve je dobré položit si tu nejjednodušší otázku: Čekáte vůbec nějaký balíček? Pokud ne, je to první jasný důkaz, že jde o podvod.<br><br>Odepisovat na podezřelé zprávy je vždy rizikové a z technických důvodů na ně navíc často ani nelze přímo odpovědět.<br><br>I kdyby vám systém odepsat dovolil, jakákoliv reakce je pro podvodníky cenná informace – potvrdíte jim tím, že vaše telefonní číslo je aktivní, že zprávy čtete a že jste na ně ochotní reagovat. Tím se okamžitě dostanete na seznam „živých cílů“ a podvodníci vás začnou bombardovat dalšími falešnými zprávami a nevyžádanými hovory.<br><br>Že je zpráva od doručovací služby realita, poznáte podle toho, že Česká pošta nikdy neposílá odkazy na platby v SMSkách. Skutečné upozornění vždy obsahuje unikátní podací číslo. To můžete sami ručně zadat do vyhledávače přímo na oficiálním webu České pošty.<br><br>Pokud žádné číslo nemáte, zprávu ignorujte.<br><br>Zkuste to znovu.',
                    ],
                    [
                        'name' => 'Zprávu rovnou smažu, Česká pošta takhle nekomunikuje',
                        'right' => true,
                        'evaluation' => 'Vynikající!<br><br>Položili jste si tu nejdůležitější otázku: zda vůbec nějaký balíček čekáte, a nenechali jste se zlákat zvědavostí ani podezřele nízkou částkou 49 Kč.<br><br>Správně jste vyhodnotili, že Česká pošta takovým způsobem doplatky nikdy nevyžaduje.<br><br>Odhalili jste klíčové varovné znaky: zpráva přišla z neznámého mobilního čísla a internetová adresa je falešná.<br><br>Skutečné upozornění o balíčku od pošty vždy obsahuje unikátní podací číslo zásilky, které si můžete sami bezpečně ověřit přímo na jejich oficiálním webu.<br><br>Tím, že jste zprávu smazali, jste udrželi své peníze i platební kartu v naprostém bezpečí. Skvělá práce',
                    ],
                ],
            ],
            [
                'button' => 2,
                'difficulty' => 3,
                'perex' => 'Česká pošta – poslední upozornění',
                'description' => '[[image:situace]]<p>Na mobil vám přijde SMS z neznámého čísla <strong>+420 739 118 604</strong>:</p><p>„Česká pošta: Pane Nováku, poslední upozornění. Vaše zásilka bude dnes vrácena odesílateli.<br>Pro opakované doručení uhraďte poplatek 49 Kč zde:<br>posta-zasilka-online.cz/uhrada“</p><p><strong>Časomíra:</strong> 1 minuta</p>',
                'safety_card' => null,
                'image' => [
                    'key' => 'situace',
                    'path' => 'images/situations/c6c8ae29-dcda-42ff-940c-16e3b74c5ea6.webp',
                    'alt' => 'Česká pošta – poslední upozornění',
                ],
                'options' => [
                    [
                        'name' => 'Kliknu na odkaz, ať můžu zaplatit poplatek',
                        'right' => false,
                        'evaluation' => 'Zadržte!<br><br>První a nejdůležitější otázka u takových zpráv vždy zní: Čekáte vůbec nějaký balíček? Pokud ne, je to jasné varování.<br><br>Tento text rafinovaně útočí na vaše emoce – na zvědavost, co by v zásilce mohlo být, a na touhu mít věc rychle vyřízenou.<br><br>Částka 49 Kč je schválně nízká, aby nevzbudila podezření.<br><br>Pokud však na odkaz kliknete a zadáte údaje ze své platební karty, nepošlete čtyřicet devět korun poště, ale odevzdáte kompletní přístup ke svému účtu podvodníkům.<br><br>Že jde o podvod, přitom poznáte snadno:<br><br>Česká pošta nikdy neposílá odkazy na platební brány v SMS zprávách z neznámých mobilních čísel. Skutečné upozornění o balíčku vždy obsahuje unikátní podací číslo zásilky. To, zda je zpráva realita, si můžete bezpečně ověřit tak, že toto číslo sami zadáte do oficiálního vyhledávače přímo na webu České pošty. Pokud žádné číslo nemáte nebo odkaz v SMS vypadá jako zkomolenina, je to jasný podvod. Tímto kliknutím byste mohli přijít o veškeré úspory.<br><br>Zkuste to znovu.',
                    ],
                    [
                        'name' => 'Odepíšu na zprávu a zeptám se, o jaký balíček jde',
                        'right' => false,
                        'evaluation' => 'Snaha získat podrobnosti je rozumná, ale odepisovat na takové zprávy bohužel nikam nevede.<br><br>Nejprve je dobré položit si tu nejjednodušší otázku: Čekáte vůbec nějaký balíček? Pokud ne, je to první jasný důkaz, že jde o podvod.<br><br>I když chcete situaci jen prověřit, odepisováním pouze potvrzujete podvodníkům, že je vaše telefonní číslo aktivní a zprávy čtete. Na druhé straně navíc nesedí žádný skutečný poštovní doručovatel, ale naprogramovaný automat, který vám neodpoví a nebo ani nebude technicky možné odpovědět.<br><br>Že je zpráva od doručovací služby realita, poznáte podle toho, že Česká pošta nikdy neposílá odkazy na platby v SMSkách. Skutečné upozornění vždy obsahuje unikátní podací číslo. To můžete sami ručně zadat do vyhledávače přímo na oficiálním webu České pošty.<br><br>Pokud žádné číslo nemáte, zprávu ignorujte.<br><br>Zkuste to znovu.',
                    ],
                    [
                        'name' => 'Zprávu rovnou smažu, Česká pošta takhle nekomunikuje',
                        'right' => true,
                        'evaluation' => 'Vynikající!<br><br>Položili jste si tu nejdůležitější otázku: zda vůbec nějaký balíček čekáte, a nenechali jste se zlákat zvědavostí ani podezřele nízkou částkou 49 Kč.<br><br>Správně jste vyhodnotili, že Česká pošta takovým způsobem doplatky nikdy nevyžaduje.<br><br>Odhalili jste klíčové varovné znaky: zpráva přišla z neznámého mobilního čísla a internetová adresa je falešná.<br><br>Skutečné upozornění o balíčku od pošty vždy obsahuje unikátní podací číslo zásilky, které si můžete sami bezpečně ověřit přímo na jejich oficiálním webu.<br><br>Tím, že jste zprávu smazali, jste udrželi své peníze i platební kartu v naprostém bezpečí. Skvělá práce',
                    ],
                ],
            ],

            // ===== TŘETÍ herní kámen =====
            [
                'button' => 3,
                'difficulty' => 1,
                'perex' => 'Falešné varování o viru',
                'description' => '[[image:situace]]<p>Při prohlížení internetu se vám na obrazovce zobrazí vyskakovací okno:</p><p>„VAROVÁNÍ! Vaše zařízení může být zavirované.“</p><p>Pod zprávou je tlačítko:</p><p>„Stáhnout antivir zdarma“</p>',
                'safety_card' => null,
                'image' => [
                    'key' => 'situace',
                    'path' => 'images/situations/f9a865d7-cb56-466b-8e70-e603fd7d3c6e.webp',
                    'alt' => 'Falešné varování o viru',
                ],
                'options' => [
                    [
                        'name' => 'Kliknu na odkaz a antivir si stáhnu',
                        'right' => false,
                        'evaluation' => 'Zadržte!<br><br>Text útočí na vaše emoce – na strach o bezpečí vašeho počítače nebo mobilu a na obavu ze ztráty dat.<br><br>Slovo ‚VAROVÁNÍ‘ vás má vystresovat, abyste jednali bez přemýšlení.<br><br>Pokud na odkaz kliknete a program stáhnete, uděláte přesný opak toho, co chcete: do zařízení si sami stáhnete skutečný virus nebo škodlivý program, který vám může ukrást hesla.<br><br>Že jde o podvod, přitom poznáte podle jednoho základního pravidla: běžná internetová stránka nikdy nedokáže na dálku zjistit, zda máte v počítači virus.<br><br>To umí pouze váš vlastní antivirus, který máte v zařízení sami nainstalovaný.<br><br>Zkuste to znovu',
                    ],
                    [
                        'name' => 'Kliknu na odkaz, chci zjistit o jaký vir jde',
                        'right' => false,
                        'evaluation' => 'Zvědavost a snaha zjistit, co se děje, je přirozená, ale v tomto případě vás vede přímo do pasti.<br><br>Útočníci s vaší zvědavostí počítají. Vědí, že když vás vyděsí slovem ‚zavirované‘, budete chtít vědět víc.<br><br>Kliknutím na odkaz se však nedozvíte žádné podrobnosti, protože žádný skutečný virus ve vašem zařízení nenašli. Celé toto okno je jen falešná reklama na internetu.<br><br>Skutečné varování před virem poznáte tak, že vám ho neukáže internetový prohlížeč uprostřed nějaké stránky, ale vyskočí vám upozornění přímo od vašeho vlastního antivirového programu (pokud nějaký máte).<br><br>Klikáním na toto falešné okno riskujete zavirování zařízení.<br><br>Zkuste to znovu.',
                    ],
                    [
                        'name' => 'Okno zavřu, je to podvod',
                        'right' => true,
                        'evaluation' => 'Vynikající!<br><br>Prohlédli jste typický trik, který zneužívá strach a umělý pocit ohrožení.<br><br>Správně jste vyhodnotili, že žádný internetový web nemá technickou schopnost na dálku skenovat vaše zařízení a zjišťovat, zda v něm máte virus.<br><br>Celé toto okno je jen obyčejná, lživá reklama.<br><br>Skutečné varování před virem poznáte tak, že vám ho neukáže internetový prohlížeč uprostřed nějaké stránky, ale vyskočí vám upozornění přímo od vašeho vlastního antivirového programu, který máte v počítači nebo mobilu sami nainstalovaný (pokud  nějaký máte).<br><br>Tím, že jste okno jednoduše zavřeli, jste podvodníkům nedali žádnou šanci.<br><br>Skvělá práce!',
                    ],
                ],
            ],
            [
                'button' => 3,
                'difficulty' => 2,
                'perex' => 'Falešné varování o hrozbách',
                'description' => '[[image:situace]]<p>Při prohlížení internetu se vám na obrazovce zobrazí vyskakovací okno:</p><p>„Ve vašem zařízení byly nalezeny 3 hrozby.<br>Pokud je neodstraníte, můžete přijít o fotografie a hesla.“</p><p>Pod zprávou je tlačítko:</p><p>„Nainstalovat doporučený antivir“</p><p>Okno působí jako bezpečnostní upozornění.</p>',
                'safety_card' => null,
                'image' => [
                    'key' => 'situace',
                    'path' => 'images/situations/62f181c1-8a7a-4bb3-a18b-9de6f98e8706.webp',
                    'alt' => 'Falešné varování o hrozbách',
                ],
                'options' => [
                    [
                        'name' => 'Kliknu na odkaz antivir si nainstaluji',
                        'right' => false,
                        'evaluation' => 'Zadržte!<br><br>Text útočí na vaše emoce – na strach o bezpečí vašeho počítače nebo mobilu a na obavu ze ztráty dat.<br><br>Varování, že byly “nalezeny 3 hrozby” vás má vystresovat, abyste jednali bez přemýšlení.<br><br>Pokud na odkaz kliknete a program stáhnete, uděláte přesný opak toho, co chcete: do zařízení si sami stáhnete skutečný virus nebo škodlivý program, který vám může ukrást hesla.<br><br>Že jde o podvod, přitom poznáte podle jednoho základního pravidla: běžná internetová stránka nikdy nedokáže na dálku zjistit, zda máte v počítači virus.<br><br>To umí pouze váš vlastní antivirus, který máte v zařízení sami nainstalovaný.<br><br>Zkuste to znovu',
                    ],
                    [
                        'name' => 'Kliknu na odkaz, chci vědět více o virech v mém zařízení',
                        'right' => false,
                        'evaluation' => 'Zvědavost a snaha zjistit, co se děje, je přirozená, ale v tomto případě vás vede přímo do pasti.<br><br>Útočníci s vaší zvědavostí počítají. Vědí, že když vás vyděsí tvrzením o nalezených hrozbách a ztrátě fotografií, budete chtít vědět víc a situaci zachránit.<br><br>Kliknutím na odkaz se však nedozvíte žádné podrobnosti, protože žádný skutečný virus ve vašem zařízení nenašli.<br><br>Celé toto okno je jen falešná a lživá reklama na internetu.<br><br>Skutečné varování před virem poznáte tak, že vám ho neukáže internetový prohlížeč uprostřed nějaké stránky, ale vyskočí vám upozornění přímo od vašeho vlastního antivirového programu (pokud nějaký máte).<br><br>Klikáním na toto falešné okno riskujete zavirování zařízení. Zkuste to znovu.',
                    ],
                    [
                        'name' => 'Okno zavřu, je to podvod',
                        'right' => true,
                        'evaluation' => 'Vynikající!<br><br>Prohlédli jste typický trik, který zneužívá strach a umělý pocit ohrožení.<br><br>Správně jste vyhodnotili, že žádný internetový web nemá technickou schopnost na dálku skenovat vaše zařízení a zjišťovat, zda v něm máte virus.<br><br>Celé toto okno je jen obyčejná, lživá reklama.<br><br>Skutečné varování před virem poznáte tak, že vám ho neukáže internetový prohlížeč uprostřed nějaké stránky, ale vyskočí vám upozornění přímo od vašeho vlastního antivirového programu, který máte v počítači nebo mobilu sami nainstalovaný (pokud  nějaký máte).<br><br>Tím, že jste okno jednoduše zavřeli, jste podvodníkům nedali žádnou šanci.<br><br>Skvělá práce!',
                    ],
                ],
            ],
            [
                'button' => 3,
                'difficulty' => 3,
                'perex' => 'Falešné varování s časomírou',
                'description' => '[[image:situace]]<p>Při prohlížení internetu se vám na obrazovce zobrazí vyskakovací okno:</p><p>„Bezpečnostní upozornění: Na vašem zařízení byly zjištěny škodlivé soubory.<br>Bankovní údaje jsou ohroženy. Zařízení je nutné zabezpečit do minuty”.</p><p>Pod zprávou je uvedeno:</p><p>„Doporučená ochrana: SafeProtect“</p><p>Dole je tlačítko:</p><p>„Okamžitě zabezpečit zařízení“</p><p>Okno působí oficiálně a používá časový tlak.</p><p><strong>Časomíra:</strong> 1 minuta</p>',
                'safety_card' => null,
                'image' => [
                    'key' => 'situace',
                    'path' => 'images/situations/a1bce634-57de-4072-ba56-d33e67a762db.webp',
                    'alt' => 'Falešné varování s časomírou',
                ],
                'options' => [
                    [
                        'name' => 'Kliknu na odkaz antivir si nainstaluji',
                        'right' => false,
                        'evaluation' => 'Zadržte!<br><br>Text útočí na vaše emoce – na strach o bezpečí vašeho počítače nebo mobilu a na obavu ze ztráty dat.<br><br>Informace, že “bankovní údaje jsou ohroženy” vás má vystresovat, abyste jednali bez přemýšlení.<br><br>Pokud na odkaz kliknete a program stáhnete, uděláte přesný opak toho, co chcete: do zařízení si sami stáhnete skutečný virus nebo škodlivý program, který vám může ukrást hesla.<br><br>Že jde o podvod, přitom poznáte podle jednoho základního pravidla: běžná internetová stránka nikdy nedokáže na dálku zjistit, zda máte v počítači virus.<br><br>To umí pouze váš vlastní antivirus, který máte v zařízení sami nainstalovaný.<br><br>Zkuste to znovu',
                    ],
                    [
                        'name' => 'Kliknu na odkaz, chci vědět více o virech v mém',
                        'right' => false,
                        'evaluation' => 'Zvědavost a snaha zjistit, co se děje, je přirozená, ale vede vás vede přímo do pasti.<br><br>Útočníci s vaší zvědavostí a strachem kalkulují. Vědí, že když vás vyděsí tvrzením o ohrožení bankovních údajů, budete chtít vědět víc a situaci rychle zachránit.<br><br>Kliknutím na odkaz se však nedozvíte žádné podrobnosti, protože žádné škodlivé soubory ve vašem telefonu nenašli.<br><br>Celé toto okno je jen falešná a lživá reklama na internetu, která zneužívá umělý spěch.<br><br>Skutečné varování před virem poznáte tak, že vám ho neukáže internetový prohlížeč uprostřed nějaké stránky, ale vyskočí vám upozornění přímo od vašeho vlastního antivirového programu (pokud nějaký máte).<br><br>Klikáním na toto falešné okno riskujete zavirování zařízení.<br><br>Zkuste to znovu',
                    ],
                    [
                        'name' => 'Okno zavřu, je to podvod',
                        'right' => true,
                        'evaluation' => 'Vynikající!<br><br>Prohlédli jste typický trik, který zneužívá strach a umělý pocit ohrožení.<br><br>Správně jste vyhodnotili, že žádný internetový web nemá technickou schopnost na dálku skenovat vaše zařízení a zjišťovat, zda v něm máte virus.<br><br>Celé toto okno je jen obyčejná, lživá reklama.<br><br>Skutečné varování před virem poznáte tak, že vám ho neukáže internetový prohlížeč uprostřed nějaké stránky, ale vyskočí vám upozornění přímo od vašeho vlastního antivirového programu, který máte v počítači nebo mobilu sami nainstalovaný (pokud  nějaký máte).<br><br>Tím, že jste okno jednoduše zavřeli, jste podvodníkům nedali žádnou šanci.<br><br>Skvělá práce!',
                    ],
                ],
            ],

            // ===== ČTVRTÝ herní kámen =====
            [
                'button' => 4,
                'difficulty' => 1,
                'perex' => 'Banka – pozastavený účet',
                'description' => '[[image:situace]]<p>Na mobil vám přijde SMS z neznámého čísla <strong>+420 735 219 884</strong>:</p><p>„BANKA: Váš bankovní účet byl z bezpečnostních důvodů dočasně pozastaven.<br>Pro opětovnou aktivaci účtu se přihlaste zde:<br>moje-banka-bezpecnost.cz/aktivace“</p><p>Zpráva působí jako bezpečnostní upozornění od banky.</p>',
                'safety_card' => null,
                'image' => [
                    'key' => 'situace',
                    'path' => 'images/situations/ff90c544-5724-4523-92e2-faec73120ce7.webp',
                    'alt' => 'Banka – pozastavený účet',
                ],
                'options' => [
                    [
                        'name' => 'Kliknu na odkaz a přihlásím se, ať mi účet odblokují',
                        'right' => false,
                        'evaluation' => 'Zadržte!<br><br>Toto je ta nejnebezpečnější digitální past, která míří přímo na vaše úspory.<br><br>Text útočí na vaše emoce – na strach z finančního odříznutí a paniku, že jste přišli o přístup ke svým penězům.<br><br>Pokud na odkaz kliknete a vyplníte přihlašovací údaje, neotevřete své bankovnictví, ale předáte hesla a kódy přímo útočníkům, kteří vám mohou okamžitě vybrat celý účet.<br><br>Pamatujte si jedno zlaté pravidlo: banka tohle nikdy nedělá. Nikdy vám nepošle SMSku s tím, že vám zablokovala účet a vy se musíte přihlásit přes přiložený odkaz.<br><br>Zkuste to znovu.',
                    ],
                    [
                        'name' => 'Odepíšu na zprávu a zeptám se, co se s mým účtem děje',
                        'right' => false,
                        'evaluation' => 'Snaha zjistit, co se s vašimi penězi děje, je pochopitelná, ale odepisování na toto číslo je velká chyba.<br><br>I když chcete situaci jen prověřit, odepisováním pouze potvrzujete podvodníkům, že je vaše telefonní číslo aktivní, že zprávy čtete a že vás text vystrašil.<br><br>Na druhé straně navíc neodpovídá žádný skutečný bankovní poradce, ale naprogramovaný robot a nebo ani nemusí být technicky možné na zprávu odpovědět. .<br><br>Pamatujte si jedno zlaté pravidlo: banka tohle nikdy nedělá. Nikdy vám nepošle SMSku s odkazem, abyste přes něj řešili zablokovaný účet.<br><br>Pokud máte jakékoliv pochybnosti, zprávu zavřete, otočte svou platební kartu a zavolejte na oficiální telefonní číslo na její zadní straně, případně se sami přihlaste do své oficiální bankovní aplikace.<br><br>Tam hned uvidíte, že jsou vaše peníze v naprostém pořádku.<br><br>Zkuste to znovu.',
                    ],
                    [
                        'name' => 'Zprávu smažu, banka přes odkazy v SMS nekomunikuje',
                        'right' => true,
                        'evaluation' => 'Vynikající!<br><br>Zachovali jste si chladnou hlavu a nenechali se paralyzovat strachem o své peníze.<br><br>Správně víte, že banka tohle nikdy nedělá – nikdy neposílá odkazy na přihlášení do internetového bankovnictví v obyčejné textové zprávě.<br><br>Tím, že jste zprávu smazali a v případě pochybností byste raději sami zavolali na číslo na zadní straně své platební karty, jste své celoživotní úspory udrželi v naprostém bezpečí.<br><br>Skvělá práce!',
                    ],
                ],
            ],
            [
                'button' => 4,
                'difficulty' => 2,
                'perex' => 'Exekutor – evidence dluhu',
                'description' => '[[image:situace]]<p>Na mobil vám přijde SMS z neznámého čísla <strong>+420 734 662 901</strong>:</p><p>„EXEKUTOR: Evidence dluhu 1 500 Kč.<br>Zaplaťte do 24 hodin, jinak může dojít k zabavení majetku.<br>Platbu proveďte zde:<br>exekuce-platba.cz/uhrada“</p>',
                'safety_card' => null,
                'image' => [
                    'key' => 'situace',
                    'path' => 'images/situations/54d39536-2d87-4112-8557-c622d840184d.webp',
                    'alt' => 'Exekutor – evidence dluhu',
                ],
                'options' => [
                    [
                        'name' => 'Kliknu na odkaz a dluh raději zaplatím',
                        'right' => false,
                        'evaluation' => 'Zadržte!<br><br>Toto je ta nejnebezpečnější digitální past, která míří přímo na vaše úspory.<br><br>Text útočí na vaše emoce – na obrovský strach ze ztráty střechy nad hlavou a paniku, že přijdete o svůj majetek.<br><br>Pokud na odkaz kliknete a vyplníte platební údaje, nepošlete patnáct set korun exekutorovi, ale předáte hesla ke své kartě nebo účtu přímo útočníkům, kteří vám mohou okamžitě vybrat celé konto.<br><br>Pamatujte si jedno zlaté pravidlo: skutečný exekutor tohle nikdy nedělá.<br><br>Nikdy vám nepošle obyčejnou SMSku s ultimátem na 24 hodin a odkazem na placení.<br><br>Zkuste to znovu',
                    ],
                    [
                        'name' => 'Odepíšu na zprávu, že je to omyl a žádný dluh nemám',
                        'right' => false,
                        'evaluation' => 'To, že si stojíte za svým a víte, že žádný dluh nemáte, je naprosto správný postoj.<br><br>Odepisovat na toto číslo je ale bohužel velká chyba. I když chcete situaci uvést na pravou míru, odepisováním pouze potvrzujete podvodníkům, že je vaše telefonní číslo aktivní, že zprávy čtete a že ve vás hrozba exekuce vyvolala reakci. Na druhé straně navíc neodpovídá žádný skutečný úředník, ale naprogramovaný robot a nebo ani nemusí být technicky možné na zprávu odpovědět.<br><br>Pamatujte si jedno zlaté pravidlo: skutečný exekutor tohle nikdy nedělá. Nikdy vám nepošle SMSku s odkazem, abyste přes něj platili nějaký dluh. Oficiální výzvy a exekuce se posílají výhradně doporučeným dopisem s modrým pruhem do vlastních rukou nebo do oficiální Datové schránky.<br><br>Pokud byste měli jakékoli pochybnosti, zprávu zavřete a raději se sami podívejte do zabezpečeného Centrálního registru exekucí.<br><br>Zkuste to znovu.',
                    ],
                    [
                        'name' => 'Zprávu rovnou smažu, exekutoři takhle přes SMS nekomunikují',
                        'right' => true,
                        'evaluation' => 'Vynikající!<br><br>Zachovali jste si chladnou hlavu a nenechali se paralyzovat strachem ze zabavení majetku.<br><br>Správně víte, že skutečný exekutor tohle nikdy nedělá – nikdy neposílá ultimáta na 24 hodin a odkazy na placení v obyčejné textové zprávě.<br><br>Odhalili jste klíčové varovné znaky: zpráva přišla z obyčejného mobilního čísla, snaží se vás vyděsit umělým spěchem a internetová adresa je zřejmý podvrh.<br><br>Skutečné úřední záležitosti se řeší přes doporučené dopisy s modrým pruhem nebo oficiální Datovou schránku.<br><br>Tím, že jste zprávu smazali, jste podvodníkům nedali žádnou šanci a své úspory udrželi v naprostém bezpečí.<br><br>Skvělá práce',
                    ],
                ],
            ],
            [
                'button' => 4,
                'difficulty' => 3,
                'perex' => 'Falešný hovor od „policie"',
                'description' => '[[image:situace]]<p>Přehraje se vám nahrávka telefonátu z neznámého čísla <strong>+420 739 504 118</strong>.</p><p><strong>Muž č. 1:<br></strong>„Dobrý den, pane Nováku. Tady kapitán Novák z hospodářské kriminálky. Volám kvůli vyšetřování organizované skupiny. U jednoho ze zadržených jsme našli padělaný občanský průkaz s vašimi údaji. Máme podezření, že se někdo ve spolupráci s pracovníkem vaší banky pokouší převést vaše peníze na zahraniční účet.</p><p>Teď mě prosím poslouchejte. O tomto hovoru s nikým nemluvte, ani s rodinou, abyste nezmařil vyšetřování. Pro ochranu vašich úspor vás přepojím na pracovníka bezpečnostního oddělení banky.“</p><p><strong>Muž č. 2:<br></strong>„Dobrý den, tady Marek Dvořák z bezpečnostního oddělení banky. Právě jsme dostali hlášení od policie. Situace je vážná a musíme jednat rychle. Otevřete si prosím bankovní aplikaci. Budu vás krok za krokem navádět k převodu peněz na rezervní bezpečnostní účet. Jakmile bude případ vyřešen, peníze se vám vrátí. Hlavně zůstaňte na lince.“</p>',
                'safety_card' => null,
                'image' => [
                    'key' => 'situace',
                    'path' => 'images/situations/1265754c-a348-4c85-b693-5808387d46bd.webp',
                    'alt' => 'Falešný hovor od „policie"',
                ],
                'options' => [
                    [
                        'name' => 'Udělám vše podle jejich pokynů a převedu peníze na bezpečný účet',
                        'right' => false,
                        'evaluation' => 'Zadržte!<br><br>Toto je ta nejpromyšlenější a nejvíc drtivá digitální past, která vás může připravit o úplně všechny celoživotní úspory.<br><br>Tento telefonát útočí na vaše emoce – na obrovský šok, strach, že přicházíte o peníze, a respekt k policii.<br><br>Útočníci jsou na podvody vyškoleni.  Záměrně vás nutí držet tajnosti před rodinou, abyste neměli šanci se s nikým poradit. Pokud peníze převedete, nepošlete je na žádný ‚rezervní‘ účet, ale přímo do kapes podvodníků a své peníze už nikdy neuvidíte.<br><br>Pamatujte si jedno zlaté a neprůstřelné pravidlo:<br><br><strong>policie ani banka tohle nikdy nedělají.</strong><br><br>Nikdy vás nebudou nutit převádět peníze na jiný účet, ani vás žádat, abyste cokoli tajili před rodinou.<br><br>Pokud takový hovor zažijete, okamžitě zavěste.<br><br>Sami pak zavolejte na oficiální linku své banky nebo na linku 158 a situaci si ověřte.<br><br>Zkuste to znovu',
                    ],
                    [
                        'name' => 'Zůstanu na lince, ale budu se s nimi hádat a chtít důkazy.',
                        'right' => false,
                        'evaluation' => 'Snaha nenechat se opít rohlíkem a odhalit pravdu je odvážná, ale pouštět se do debaty s těmito lidmi je obrovské riziko.<br><br>Útočníci na telefonu jsou profesionální manipulátoři, kteří mají na každou vaši otázku nebo pochybnost připravenou promyšlenou lež.<br><br>V hádce vás budou dál citově vydírat, strašit vás vězením za maření vyšetřování, nebo vám do zprávy pošlou falešný průkaz policisty, aby vás přesvědčili. Čím déle s nimi mluvíte, tím větší šanci mají vás v té panice nakonec přemluvit.<br><br>Pamatujte si zlaté pravidlo: policie ani banka tohle nikdy nedělají. Nikdy vás nebudou držet na lince a nutit vás tajit věci před rodinou.<br><br>Nejlepší obranou není hádka, ale okamžité položení telefonu. Hovor jednoduše ukončete, zavěste a pak sami zavolejte na skutečnou policii (158) nebo do své banky.<br><br>Zkuste to znovu.',
                    ],
                    [
                        'name' => 'Hovor okamžitě zavěsím, policie ani banka tohle nikdy nežádají',
                        'right' => true,
                        'evaluation' => 'Vynikající!<br><br>Dokázali jste to nejtěžší – zachovali jste klid a hovor ukončili.<br><br>Správně víte, že policie ani banka tohle nikdy nedělají.<br><br>Nikdy po vás nebudou chtít, abyste převáděli peníze na nějaké cizí ‚bezpečné‘ účty, a nikdy vás nebudou nutit držet tajemství před vlastní rodinou.<br><br>To dělají pouze podvodníci, kteří vás chtějí izolovat, abyste se neměli s kým poradit.<br><br>Tím, že jste jim bez debat položili telefon, jste okamžitě zničili celou jejich hru a zachránili si své celoživotní úspory.<br><br>Pokud byste přece jen měli malou pochybnost, můžete situaci ověřit sami – zavoláním na skutečnou policii (158) nebo na oficiální číslo své banky, které máte na zadní straně platební karty.<br><br>Skvělá práce',
                    ],
                ],
            ],

            // ===== PÁTÝ herní kámen =====
            [
                'button' => 5,
                'difficulty' => 1,
                'perex' => 'QR kód u parkovacího automatu',
                'description' => '[[image:situace]]<p>U parkovacího automatu je přes část původní cedule nalepený papírek s QR kódem:</p><p>„Zaplaťte parkování zde.“</p><p>Pod QR kódem je uvedeno:</p><p>parkovani-platba.cz</p><p>Na automatu je zároveň vidět běžný návod k platbě kartou a SMS, ale papírek tvrdí:</p><p>„Platba pouze přes QR kód.“</p>',
                'safety_card' => null,
                'image' => [
                    'key' => 'situace',
                    'path' => 'images/situations/29afe5e7-0574-4a68-83e8-7b7b8f27dbac.webp',
                    'alt' => 'QR kód u parkovacího automatu',
                ],
                'options' => [
                    [
                        'name' => 'Naskenuji QR kód a parkovné přes něj rovnou zaplatím',
                        'right' => false,
                        'evaluation' => 'Zadržte!<br><br>Toto je moderní digitální past, která se maskuje ve fyzickém světě a míří přímo na vaše celoživotní úspory.<br><br>V této situaci jste bohužel přehlédli varovné znaky: tento kód není pevnou součástí originálního stojanu, ale je to narychlo vytištěný papírek amatérsky nalepený přes původní pokyny.<br><br>Tím, že tam zadáte citlivé údaje ze své platební karty (dlouhé číslo, platnost a CVV kód ze zadní strany), předáte údaje karty rovnou podvodníkům.<br><br>Skutečné a bezpečné parkovací systémy tyto údaje v mobilu vůbec nepotřebují – vystačí si s vaší registrační značkou (SPZ) a bezpečnou, rychlou platbou přes Apple/Google Pay, která vaši kartu chrání.<br><br>Tady byste o peníze zaručeně přišli.<br><br>Zkuste to znovu.',
                    ],
                    [
                        'name' => 'Naskenuji kód jen na zkoušku, abych viděl, kam mě to přesměruje',
                        'right' => true,
                        'evaluation' => '',
                    ],
                    [
                        'name' => 'Nálepku s kódem ignoruji a zaplatím běžným způsobem přímo v automatu',
                        'right' => true,
                        'evaluation' => '',
                    ],
                ],
            ],
            [
                'button' => 5,
                'difficulty' => 2,
                'perex' => 'QR kód přelepený přes ceník',
                'description' => '[[image:situace]]<p>U parkovacího automatu je QR kód s textem:</p><p>„Nově můžete parkování zaplatit online.“</p><p>Pod QR kódem je uvedeno:</p><p>parkovani-praha-platba.com</p><p>QR kód je nalepený přes starší informační štítek. Po okrajích se už odlepuje a je zjevně nalepená přes oficiální ceník.</p><p><br>Vedle QR kódu je napsáno:</p><p>„Zóna P7, parkování 1 hodina: 40 Kč.“</p><p>Po naskenování se na telefonu zobrazí:</p><p>„Příjemce platby: PARK PAY s.r.o.“</p>',
                'safety_card' => null,
                'image' => [
                    'key' => 'situace',
                    'path' => 'images/situations/7f912fcc-16e5-4169-ba7a-bdafcffd3207.webp',
                    'alt' => 'QR kód přelepený přes ceník',
                ],
                'options' => [
                    [
                        'name' => 'Kód naskenuji a klidně zadám údaje z karty',
                        'right' => false,
                        'evaluation' => 'Zadržte!<br><br>Toto je moderní digitální past, která se maskuje ve fyzickém světě a míří přímo na vaše celoživotní úspory.<br><br>V této situaci jste bohužel přehlédli varovné znaky: tento kód není pevnou součástí originálního stojanu, ale je to narychlo vytištěný papírek amatérsky nalepený přes původní pokyny.<br><br>Tím, že tam zadáte citlivé údaje ze své platební karty (dlouhé číslo, platnost a CVV kód ze zadní strany), předáte údaje karty rovnou podvodníkům.<br><br>Skutečné a bezpečné parkovací systémy tyto údaje v mobilu vůbec nepotřebují – vystačí si s vaší registrační značkou (SPZ) a bezpečnou, rychlou platbou přes Apple/Google Pay, která vaši kartu chrání.<br><br>Tady byste o peníze zaručeně přišli.<br><br>Zkuste to znovu.',
                    ],
                    [
                        'name' => 'Zkusím kód naskenovat, abych viděl, na jakou stránku mě to přesměruje',
                        'right' => true,
                        'evaluation' => '',
                    ],
                    [
                        'name' => 'Papírek ignoruji, samolepky na automatech přes původní ceník jsou podezřelé. Zaplatím mincemi nebo přes oficiální aplikaci',
                        'right' => true,
                        'evaluation' => '',
                    ],
                ],
            ],
            [
                'button' => 5,
                'difficulty' => 3,
                'perex' => 'QR kód – platba kartou',
                'description' => '[[image:situace]]<p>U parkovacího automatu je nalepený QR kód s textem:</p><p>„Online platba parkovného.“</p><p>Vedle kódu stojí:</p><p>„Zóna P7, parkování 1 hodina: 40 Kč.“</p><p>Pod QR kódem je uvedeno:</p><p>parkuj-praha.cz</p><p>Po naskenování se na telefonu zobrazí:</p><p>„Parkování Praha 7 — 40 Kč“<br>„Příjemce platby: City Parking Online“<br>„Zadejte číslo karty pro dokončení platby.“</p><p>QR kód vypadá jako součást cedule, ale jsou přelepeny přes oficiální ceník. Adresa ani příjemce platby neodpovídají oficiální službě města.</p>',
                'safety_card' => null,
                'image' => [
                    'key' => 'situace',
                    'path' => 'images/situations/faf15269-48e1-482c-9bdc-8442caac949e.webp',
                    'alt' => 'QR kód – platba kartou',
                ],
                'options' => [
                    [
                        'name' => 'Vyplním číslo karty a platbu rovnou potvrdím',
                        'right' => false,
                        'evaluation' => 'Zadržte! Toto je moderní digitální past, která se maskuje ve fyzickém světě a míří přímo na vaše celoživotní úspory.<br><br>V této situaci jste bohužel přehlédli varovné znaky: tento kód není pevnou součástí originálního stojanu, ale je to narychlo vytištěný papírek amatérsky nalepený přes původní pokyny a oficiální ceník. Navíc adresa webu ani příjemce platby neodpovídají oficiální službě města.<br><br>Tím, že na této stránce zadáte citlivé údaje ze své platební karty (dlouhé číslo, platnost a CVV kód ze zadní strany), předáte údaje karty rovnou podvodníkům.<br><br>Skutečné a bezpečné parkovací systémy tyto údaje v mobilu vůbec nepotřebují – vystačí si s vaší registrační značkou (SPZ) a bezpečnou, rychlou platbou přes Apple/Google Pay, která vaši kartu chrání. Tady byste o peníze zaručeně přišli. Zkuste to znovu.',
                    ],
                    [
                        'name' => 'Zkusím na stránce kliknout na kontakt nebo detaily platby, abych zjistil, o koho jde',
                        'right' => true,
                        'evaluation' => 'Snaha dopátrat se pravdy a kliknout na kontakty je logická, ale v tomto případě vás vede do pasti.<br><br>Útočníci s vaší zvědavostí a opatrností počítají. Vytvořili stránku, která sice vypadá oficiálně, ale tlačítka pro kontakty nebo detaily budou buď nefunkční, nebo vás dovedou ke smyšleným údajům, které mají jen posílit vaši důvěru.<br><br>Po naskenování jste sice ještě o peníze nepřišli, ale přehlédli jste varovné znaky přímo na místě: tento kód není pevnou součástí stojanu, ale je to papírek amatérsky nalepený přes oficiální ceník. Navíc adresa webu ani příjemce neodpovídají skutečné službě města.<br><br>Pokud byste zkoumali dál, stránka vás stejně nakonec dovede k tomu, abyste tam zadali citlivé údaje ze své platební karty (dlouhé číslo, platnost a CVV kód ze zadní strany), čímž byste je předali rovnou podvodníkům.<br><br>Skutečné a bezpečné parkovací systémy tyto údaje v mobilu vůbec nepotřebují – vystačí si s vaší registrační značkou (SPZ) a bezpečnou, rychlou platbou přes Apple/Google Pay, která vaši kartu chrání.<br><br>Zkoumáním falešného webu riskujete ztrátu úspor.<br><br>Zkuste to znovu.',
                    ],
                    [
                        'name' => 'Stránku v mobilu okamžitě zavřu a zaplatím standardně mincemi nebo kartou v automatu',
                        'right' => true,
                        'evaluation' => 'Vynikající!<br><br>Zachovali jste si chladnou hlavu, projevili skvělý postřeh a nenechali se oklamat. QR kódy na parkovacích stojanech jsou dnes běžným a skvělým pomocníkem, ale vy jste správně odhalili, že teď jde o podvod.<br><br>Všimli jste si všech klíčových varovných znaků: kód nebyl pevnou součástí stojanu, ale byl amatérsky nalepený přes oficiální ceník.<br><br>Internetová adresa ani příjemce neodpovídali službám města a stránka po vás chtěla citlivé údaje z platební karty (dlouhé číslo, platnost a CVV kód ze zadní strany).<br><br>Skutečné a bezpečné parkovací systémy přitom tyto údaje v mobilu vůbec nepotřebují – vystačí si s vaší registrační značkou (SPZ) a bezpečnou, rychlou platbou přes Apple/Google Pay, která vaši kartu chrání.<br><br>Tím, že jste podvodnou stránku okamžitě zavřeli a zaplatili přímo v automatu, jste podvodníky stoprocentně obehráli a své úspory udrželi v naprostém bezpečí.<br><br>Skvělá práce!',
                    ],
                ],
            ],
        ];
    }
};
