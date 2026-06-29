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
 * Naplní Ostrov digitálních pastí z dodaného scénáře (docx).
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
 * Herní časomíra (question.settings.time_limit = 59 s) je u kamenů 1, 2 a 3 /
 * obtížnosti 3 („Časomíra: 59 sekund"). Bonusová otázka se ukládá jako Question
 * s bonus=true, BEZ vazby na situations (difficulty_id je povinné, proto 1).
 *
 * Pozn.: u 5. kamene / obtížnosti 3 měla prostřední možnost ve scénáři omylem
 * „Plné světlo" (přitom reakce strážce je varovná a u obtížností 1 a 2 je
 * obdobná možnost špatně) – opraveno na right=false.
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
        $island = Island::query()->where('image', 'digital_traps.webp')->first();

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
        $cardUrad = 'Státní úřady vám nikdy nepošlou SMS zprávu s odkazem, abyste si vyzvedli peníze.';
        $cardPosta = 'Doručovací společnosti po vás nikdy nebudou chtít doplatit drobné částky přes odkaz v SMS zprávě.';
        $cardVirus = 'Vyskočilo na vás varování o viru? Je to trik, který vás má vyděsit. Na nic neklikejte a nic nestahujte.';
        $cardQr = 'Podezřelé QR kódy neskenujte ani ze zvědavosti.';

        // opakované reakce strážce (verbatim shodné napříč obtížnostmi daného kamene)
        $uradReply = implode('<br><br>', [
            'Pozor. Odepisovat na podezřelé zprávy je vždy rizikové a z technických důvodů na ně navíc často ani nelze přímo odpovědět.',
            'I kdyby vám systém odepsat dovolil, jakákoliv reakce je pro podvodníky cenná informace – potvrdíte jim tím, že vaše telefonní číslo je aktivní, že zprávy čtete a že jste na ně ochotní reagovat. Tím se okamžitě dostanete na seznam „živých cílů" a podvodníci vás začnou bombardovat dalšími falešnými zprávami a nevyžádanými hovory.',
            'Že je zpráva od úřadu bezpečná, poznáte podle toho, že vám přijde do oficiální Datové schránky, nebo vás úřad vyzve, abyste se sami přihlásili přes zabezpečenou Identitu občana na jejich oficiální adrese. Nikdy vám nepošlou přímý odkaz v SMS.',
        ]);
        $postaClick = implode('<br><br>', [
            'Zadržte! První a nejdůležitější otázka u takových zpráv vždy zní: čekáte vůbec nějaký balíček? Pokud ne, je to jasné varování.',
            'Tento text rafinovaně útočí na vaše emoce – na zvědavost, co by v zásilce mohlo být, a na touhu mít věc rychle vyřízenou. Částka 49 Kč je schválně nízká, aby nevzbudila podezření. Pokud na odkaz kliknete a zadáte údaje ze své platební karty, nepošlete čtyřicet devět korun poště, ale odevzdáte kompletní přístup ke svému účtu podvodníkům.',
            'Česká pošta nikdy neposílá odkazy na platební brány v SMS z neznámých čísel. Skutečné upozornění o balíčku vždy obsahuje unikátní podací číslo zásilky, které si můžete sami ověřit přímo na oficiálním webu pošty. Pokud žádné číslo nemáte nebo odkaz vypadá jako zkomolenina, je to jasný podvod.',
        ]);
        $postaDelete = 'Vynikající! Položili jste si tu nejdůležitější otázku: zda vůbec nějaký balíček čekáte, a nenechali jste se zlákat zvědavostí ani podezřele nízkou částkou 49 Kč. Správně jste vyhodnotili, že Česká pošta takovým způsobem doplatky nikdy nevyžaduje. Odhalili jste klíčové varovné znaky: zpráva přišla z neznámého mobilního čísla a internetová adresa je falešná. Skutečné upozornění o balíčku vždy obsahuje unikátní podací číslo zásilky, které si můžete sami ověřit přímo na oficiálním webu. Tím, že jste zprávu smazali, jste udrželi své peníze i platební kartu v naprostém bezpečí. Skvělá práce.';
        $virusClose = 'Vynikající! Prohlédli jste typický trik, který zneužívá strach a umělý pocit ohrožení. Správně jste vyhodnotili, že žádný internetový web nemá technickou schopnost na dálku skenovat vaše zařízení a zjišťovat, zda v něm máte virus. Celé toto okno je jen obyčejná, lživá reklama. Skutečné varování před virem nepřijde uprostřed nějaké stránky v prohlížeči, ale vyskočí přímo od vašeho vlastního antivirového programu, který máte v zařízení sami nainstalovaný (pokud nějaký máte). Tím, že jste okno jednoduše zavřeli, jste podvodníkům nedali žádnou šanci. Skvělá práce!';

        return [
            // ============ 1. herní kámen – falešné SMS od úřadu ============
            [
                'button' => 1, 'difficulty' => 1, 'perex' => 'SMS o mimořádném příspěvku',
                'description' => 'Na mobil vám přijde SMS z neznámého čísla +420 736 184 902:<br><br>„MPS: Máte nárok na mimořádný příspěvek 4 800 Kč. Pro vyplacení potvrďte žádost zde: mps-prispevek-info.cz/vyplata"<br><br>Zpráva působí jako oznámení od úřadu.',
                'safety_card' => $cardUrad,
                'image' => ['path' => 'images/situations/2c3ccad6-83c0-4afe-ac7c-c54423a173c0.webp', 'alt' => 'Falešná SMS o mimořádném příspěvku od „úřadu"'],
                'options' => [
                    ['name' => 'Kliknu na odkaz a vyplním potřebné údaje.', 'right' => false, 'evaluation' => 'Zadržte! Tohle je extrémně nebezpečná digitální past. Tato zpráva útočí na vaše emoce – konkrétně na radost z nečekaného finančního zisku. Podvodníci zneužívají jméno známého úřadu, aby získali vaši důvěru. Pokud na odkaz kliknete a vyplníte údaje, neotevře se vám úřední formulář, ale falešná stránka, která z vás vyláká přístupy k vašemu bankovnímu účtu. Během chvíle byste mohli přijít o všechny úspory.'],
                    ['name' => 'Odepíšu na zprávu a zeptám se na podrobnosti.', 'right' => false, 'evaluation' => $uradReply],
                    ['name' => 'Zprávu rovnou smažu, úřady takhle nekomunikují.', 'right' => true, 'evaluation' => 'Vynikající! Správně jste vyhodnotili, že státní úřady takto s občany nekomunikují. Odhalili jste klíčové varovné znaky: zpráva přišla z obyčejného mobilního čísla a odkaz je falešný. Že je zpráva od úřadu bezpečná, poznáte podle toho, že vám přijde do oficiální Datové schránky, nebo vás úřad vyzve, abyste se sami přihlásili přes zabezpečenou Identitu občana na jejich oficiální adrese. Skvělá práce.'],
                ],
            ],
            [
                'button' => 1, 'difficulty' => 2, 'perex' => 'SMS o příspěvku na bydlení',
                'description' => 'Na mobil vám přijde SMS z neznámého čísla +420 736 184 902:<br><br>„MPS: Byl vám předběžně schválen příspěvek na bydlení. Pro dokončení žádosti ověřte svou totožnost přes bankovní identitu zde: mps-prispevek-info.cz/vyplata"<br><br>Zpráva se vydává za státní instituci a slibuje vyplacení příspěvku.',
                'safety_card' => $cardUrad,
                'image' => ['path' => 'images/situations/c388d600-34ef-4e99-abd8-4467763c3b76.webp', 'alt' => 'Falešná SMS o příspěvku na bydlení'],
                'options' => [
                    ['name' => 'Kliknu na odkaz a ověřím se přes svou bankovní identitu.', 'right' => false, 'evaluation' => 'Zadržte! Tohle je ta nejnebezpečnější digitální past. Text útočí na vaše emoce – na vidinu finanční pomoci s bydlením. Podvodníci zneužívají jméno ministerstva a ČSSZ, aby zvýšili důvěryhodnost. Pokud na odkaz kliknete a zadáte údaje ke své bankovní identitě, nepřihlásíte se na úřad, ale předáte podvodníkům plný přístup ke svému bankovnímu účtu. Během několika minut byste mohli přijít o všechny celoživotní úspory.'],
                    ['name' => 'Odepíšu na zprávu a zeptám se na podrobnosti.', 'right' => false, 'evaluation' => $uradReply],
                    ['name' => 'Zprávu rovnou smažu, úřady takhle nekomunikují.', 'right' => true, 'evaluation' => 'Vynikající! Správně jste vyhodnotili, že státní úřady takto s občany nekomunikují. Odhalili jste klíčové varovné znaky: zpráva přišla z běžného mobilního čísla a odkaz byl falešný. Státní instituce nikdy neposílají odkazy na přihlášení k bankovní identitě v obyčejné SMS – oficiální výzvy chodí výhradně do Datové schránky. Pokud si chcete příspěvek ověřit, vždy sami ručně napište do prohlížeče oficiální adresu úřadu (mpsv.cz nebo cssz.cz). Skvělá práce.'],
                ],
            ],
            [
                'button' => 1, 'difficulty' => 3, 'perex' => 'SMS o doplatku k důchodu',
                'settings' => ['time_limit' => 59],
                'description' => 'Na mobil vám přijde SMS z neznámého čísla +420 736 184 902:<br><br>„MPS: Byla zjištěna možnost doplatku k vašemu důchodu. Žádost je nutné potvrdit do 24 hodin, jinak bude nárok zrušen: mps-prispevek-info.cz/prihlaseni"<br><br>Zpráva působí naléhavě a odkaz vypadá podobně jako ePortál ČSSZ.',
                'safety_card' => $cardUrad,
                'image' => ['path' => 'images/situations/7d3d41c1-5c67-4f55-b1b2-93d04259894e.webp', 'alt' => 'Falešná SMS o doplatku k důchodu s časovým tlakem'],
                'options' => [
                    ['name' => 'Kliknu na odkaz a vyplním údaje.', 'right' => false, 'evaluation' => 'Zadržte! Tohle je ta nejnebezpečnější digitální past. Text útočí na vaše emoce – možnost získání doplatku k důchodu. Podvodníci zneužívají jméno ministerstva a ČSSZ, aby zvýšili důvěryhodnost. Pokud na odkaz kliknete a zadáte údaje ke své bankovní identitě, nepřihlásíte se na úřad, ale předáte podvodníkům plný přístup ke svému bankovnímu účtu. Během několika minut byste mohli přijít o všechny celoživotní úspory.'],
                    ['name' => 'Odepíšu na zprávu a zeptám se na podrobnosti.', 'right' => false, 'evaluation' => $uradReply],
                    ['name' => 'Zprávu rovnou smažu, úřady s lidmi takhle nekomunikují.', 'right' => true, 'evaluation' => 'Vynikající! Správně jste vyhodnotili, že státní úřady takto s občany nekomunikují. Odhalili jste klíčové varovné znaky: zpráva přišla z běžného mobilního čísla a odkaz byl falešný. Státní instituce nikdy neposílají odkazy na přihlášení k bankovní identitě v obyčejné SMS – oficiální výzvy chodí výhradně do Datové schránky. Pokud si chcete příspěvek ověřit, vždy sami ručně napište do prohlížeče oficiální adresu úřadu (mpsv.cz nebo cssz.cz). Skvělá práce.'],
                ],
            ],

            // ============ 2. herní kámen – falešné doručení balíčku ============
            [
                'button' => 2, 'difficulty' => 1, 'perex' => 'SMS o doručení balíčku',
                'description' => 'Na mobil vám přijde SMS z neznámého čísla +420 737 450 219:<br><br>„Městská pošta: Váš balíček čeká na doručení. Pro dokončení doručení doplaťte poštovné 49 Kč zde: mestska-posta-doruceni.cz/platba"',
                'safety_card' => $cardPosta,
                'image' => ['path' => 'images/situations/4eaabf21-e2eb-443c-bcf0-ba8a84d3148e.webp', 'alt' => 'Falešná SMS o doručení balíčku'],
                'options' => [
                    ['name' => 'Kliknu na odkaz, ať můžu zaplatit poplatek.', 'right' => false, 'evaluation' => $postaClick],
                    ['name' => 'Odepíšu na zprávu a zeptám se, o jaký balíček jde.', 'right' => false, 'evaluation' => 'Snaha získat podrobnosti je rozumná, ale odepisovat na takové zprávy bohužel nikam nevede. Nejprve si položte tu nejjednodušší otázku: čekáte vůbec nějaký balíček? Pokud ne, je to první jasný důkaz, že jde o podvod.<br><br>Odepisovat na podezřelé zprávy je vždy rizikové a často na ně ani nelze přímo odpovědět. Jakákoliv reakce navíc podvodníkům potvrdí, že je vaše číslo aktivní, a dostanete se na seznam „živých cílů".<br><br>Česká pošta nikdy neposílá odkazy na platby v SMS. Skutečné upozornění vždy obsahuje unikátní podací číslo, které si sami ověříte na oficiálním webu pošty. Pokud žádné nemáte, zprávu ignorujte.'],
                    ['name' => 'Zprávu rovnou smažu, Česká pošta takhle nekomunikuje.', 'right' => true, 'evaluation' => $postaDelete],
                ],
            ],
            [
                'button' => 2, 'difficulty' => 2, 'perex' => 'SMS o nedoplatku za zásilku',
                'description' => 'Na mobil vám přijde SMS z neznámého čísla +420 732 804 611:<br><br>„Městská pošta: Pane Nováku, doručení vaší zásilky bylo pozastaveno z důvodu nedoplatku 49 Kč. Pro pokračování doručení uhraďte poplatek zde: mestska-posta-doplatek.cz/zasilka"',
                'safety_card' => $cardPosta,
                'image' => ['path' => 'images/situations/e04878db-4824-4180-adda-84b4db5bf5e3.webp', 'alt' => 'Falešná SMS o nedoplatku za zásilku'],
                'options' => [
                    ['name' => 'Kliknu na odkaz, ať můžu zaplatit poplatek.', 'right' => false, 'evaluation' => $postaClick],
                    ['name' => 'Odepíšu na zprávu a zeptám se, o jaký balíček jde.', 'right' => false, 'evaluation' => 'Snaha získat podrobnosti je rozumná, ale odepisovat na takové zprávy bohužel nikam nevede. Nejprve si položte tu nejjednodušší otázku: čekáte vůbec nějaký balíček? Pokud ne, je to první jasný důkaz, že jde o podvod.<br><br>Odepisovat na podezřelé zprávy je vždy rizikové a často na ně ani nelze přímo odpovědět. Jakákoliv reakce navíc podvodníkům potvrdí, že je vaše číslo aktivní, a dostanete se na seznam „živých cílů".<br><br>Česká pošta nikdy neposílá odkazy na platby v SMS. Skutečné upozornění vždy obsahuje unikátní podací číslo, které si sami ověříte na oficiálním webu pošty. Pokud žádné nemáte, zprávu ignorujte.'],
                    ['name' => 'Zprávu rovnou smažu, Česká pošta takhle nekomunikuje.', 'right' => true, 'evaluation' => $postaDelete],
                ],
            ],
            [
                'button' => 2, 'difficulty' => 3, 'perex' => 'SMS o vrácení zásilky',
                'settings' => ['time_limit' => 59],
                'description' => 'Na mobil vám přijde SMS z neznámého čísla +420 739 118 604:<br><br>„Pošta Online: Pane Nováku, poslední upozornění. Vaše zásilka bude dnes vrácena odesílateli. Pro opakované doručení uhraďte poplatek 49 Kč zde: posta-online-doprava.cz/uhrada"',
                'safety_card' => $cardPosta,
                'image' => ['path' => 'images/situations/5517f9c5-c16d-4d62-80d4-16a32fb57649.webp', 'alt' => 'Falešná SMS o vrácení zásilky s časovým tlakem'],
                'options' => [
                    ['name' => 'Kliknu na odkaz, ať můžu zaplatit poplatek.', 'right' => false, 'evaluation' => $postaClick],
                    ['name' => 'Odepíšu na zprávu a zeptám se, o jaký balíček jde.', 'right' => false, 'evaluation' => 'Snaha získat podrobnosti je rozumná, ale odepisovat na takové zprávy bohužel nikam nevede. Nejprve si položte tu nejjednodušší otázku: čekáte vůbec nějaký balíček? Pokud ne, je to první jasný důkaz, že jde o podvod.<br><br>I když chcete situaci jen prověřit, odepisováním pouze potvrzujete podvodníkům, že je vaše číslo aktivní a zprávy čtete. Na druhé straně navíc nesedí žádný skutečný doručovatel, ale naprogramovaný automat.<br><br>Česká pošta nikdy neposílá odkazy na platby v SMS. Skutečné upozornění vždy obsahuje unikátní podací číslo, které si sami ověříte na oficiálním webu pošty. Pokud žádné nemáte, zprávu ignorujte.'],
                    ['name' => 'Zprávu rovnou smažu, Česká pošta takhle nekomunikuje.', 'right' => true, 'evaluation' => $postaDelete],
                ],
            ],

            // ============ 3. herní kámen – falešné varování o viru ============
            [
                'button' => 3, 'difficulty' => 1, 'perex' => 'Vyskakovací okno o viru',
                'description' => 'Při prohlížení internetu se vám na obrazovce zobrazí vyskakovací okno:<br><br>„VAROVÁNÍ! Vaše zařízení může být zavirované."<br><br>Pod zprávou je tlačítko „Stáhnout antivir zdarma".',
                'safety_card' => $cardVirus,
                'image' => ['path' => 'images/situations/739ebbcc-913f-4369-beee-8c10cd99fbf8.webp', 'alt' => 'Falešné vyskakovací okno varující před virem'],
                'options' => [
                    ['name' => 'Kliknu na odkaz a antivir si stáhnu.', 'right' => false, 'evaluation' => 'Zadržte! Text útočí na vaše emoce – na strach o bezpečí vašeho zařízení a obavu ze ztráty dat. Slovo „VAROVÁNÍ" vás má vystresovat, abyste jednali bez přemýšlení. Pokud na odkaz kliknete a program stáhnete, uděláte přesný opak toho, co chcete: do zařízení si sami stáhnete skutečný virus nebo škodlivý program, který vám může ukrást hesla. Že jde o podvod, poznáte podle jednoho pravidla: běžná internetová stránka nikdy nedokáže na dálku zjistit, zda máte v počítači virus. To umí pouze váš vlastní antivirus, který máte v zařízení sami nainstalovaný.'],
                    ['name' => 'Kliknu na odkaz, chci zjistit, o jaký vir jde.', 'right' => false, 'evaluation' => 'Zvědavost a snaha zjistit, co se děje, je přirozená, ale v tomto případě vás vede přímo do pasti. Útočníci s vaší zvědavostí počítají – vědí, že když vás vyděsí slovem „zavirované", budete chtít vědět víc. Kliknutím se ale žádné podrobnosti nedozvíte, protože žádný skutečný virus ve vašem zařízení nenašli. Celé toto okno je jen falešná reklama. Skutečné varování před virem nepřijde uprostřed stránky v prohlížeči, ale vyskočí přímo od vašeho antivirového programu (pokud nějaký máte). Klikáním na falešné okno riskujete zavirování zařízení.'],
                    ['name' => 'Okno zavřu, je to podvod.', 'right' => true, 'evaluation' => $virusClose],
                ],
            ],
            [
                'button' => 3, 'difficulty' => 2, 'perex' => 'Falešné bezpečnostní upozornění',
                'description' => 'Při prohlížení internetu se vám na obrazovce zobrazí vyskakovací okno:<br><br>„Ve vašem zařízení byly nalezeny 3 hrozby. Pokud je neodstraníte, můžete přijít o fotografie a hesla."<br><br>Pod zprávou je tlačítko „Nainstalovat doporučený antivir". Okno působí jako bezpečnostní upozornění.',
                'safety_card' => $cardVirus,
                'image' => ['path' => 'images/situations/3b7f48c3-fb18-45cf-acef-789cc6e39b71.webp', 'alt' => 'Falešné bezpečnostní upozornění o hrozbách'],
                'options' => [
                    ['name' => 'Kliknu na odkaz a antivir si nainstaluji.', 'right' => false, 'evaluation' => 'Zadržte! Text útočí na vaše emoce – na strach o bezpečí zařízení a obavu ze ztráty dat. Varování, že byly „nalezeny 3 hrozby", vás má vystresovat, abyste jednali bez přemýšlení. Pokud program stáhnete, uděláte přesný opak toho, co chcete: do zařízení si sami stáhnete skutečný virus, který vám může ukrást hesla. Že jde o podvod, poznáte podle jednoho pravidla: běžná internetová stránka nikdy nedokáže na dálku zjistit, zda máte v počítači virus. To umí pouze váš vlastní antivirus.'],
                    ['name' => 'Kliknu na odkaz, chci vědět víc o virech v mém zařízení.', 'right' => false, 'evaluation' => 'Zvědavost a snaha zjistit, co se děje, je přirozená, ale v tomto případě vás vede přímo do pasti. Útočníci vědí, že když vás vyděsí tvrzením o nalezených hrozbách a ztrátě fotografií, budete chtít situaci zachránit. Kliknutím se ale žádné podrobnosti nedozvíte, protože žádný skutečný virus ve vašem zařízení nenašli. Celé toto okno je jen falešná a lživá reklama. Skutečné varování vyskočí přímo od vašeho antivirového programu (pokud nějaký máte). Klikáním na falešné okno riskujete zavirování zařízení.'],
                    ['name' => 'Okno zavřu, je to podvod.', 'right' => true, 'evaluation' => $virusClose],
                ],
            ],
            [
                'button' => 3, 'difficulty' => 3, 'perex' => 'Falešné upozornění s časovým tlakem',
                'settings' => ['time_limit' => 59],
                'description' => 'Při prohlížení internetu se vám na obrazovce zobrazí vyskakovací okno:<br><br>„Bezpečnostní upozornění: Ve vašem zařízení byly zjištěny škodlivé soubory. Bankovní údaje jsou ohroženy. Zařízení je nutné zabezpečit do minuty."<br><br>Pod zprávou je uvedeno „Doporučená ochrana: SafeProtect" a tlačítko „Okamžitě zabezpečit zařízení". Okno působí oficiálně a používá časový tlak.',
                'safety_card' => $cardVirus,
                'image' => ['path' => 'images/situations/22ab8002-bf8a-486f-a9d6-5b4c45d72ceb.webp', 'alt' => 'Falešné upozornění o ohrožení s časovým tlakem'],
                'options' => [
                    ['name' => 'Kliknu na odkaz a antivir si nainstaluji.', 'right' => false, 'evaluation' => 'Zadržte! Text útočí na vaše emoce – na strach o bezpečí zařízení a obavu ze ztráty dat. Informace, že „bankovní údaje jsou ohroženy", vás má vystresovat, abyste jednali bez přemýšlení. Pokud program stáhnete, uděláte přesný opak toho, co chcete: do zařízení si sami stáhnete skutečný virus, který vám může ukrást hesla. Že jde o podvod, poznáte podle jednoho pravidla: běžná internetová stránka nikdy nedokáže na dálku zjistit, zda máte v počítači virus. To umí pouze váš vlastní antivirus.'],
                    ['name' => 'Kliknu na odkaz, chci vědět víc o virech v mém zařízení.', 'right' => false, 'evaluation' => 'Zvědavost a snaha zjistit, co se děje, je přirozená, ale vede vás přímo do pasti. Útočníci s vaší zvědavostí a strachem kalkulují – vědí, že když vás vyděsí tvrzením o ohrožení bankovních údajů, budete chtít situaci rychle zachránit. Kliknutím se ale žádné podrobnosti nedozvíte, protože žádné škodlivé soubory ve vašem telefonu nenašli. Celé toto okno je jen falešná reklama, která zneužívá umělý spěch. Skutečné varování vyskočí přímo od vašeho antivirového programu (pokud nějaký máte). Klikáním na falešné okno riskujete zavirování zařízení.'],
                    ['name' => 'Okno zavřu, je to podvod.', 'right' => true, 'evaluation' => $virusClose],
                ],
            ],

            // ============ 4. herní kámen – falešná banka, exekutor, „policie" ============
            [
                'button' => 4, 'difficulty' => 1, 'perex' => 'SMS o zablokování účtu',
                'description' => 'Na mobil vám přijde SMS z neznámého čísla +420 735 219 884:<br><br>„BANKA: Váš bankovní účet byl z bezpečnostních důvodů dočasně zablokován. Pro opětovnou aktivaci účtu se přihlaste zde: moje-banka-bezpecnost.cz/aktivace"<br><br>Zpráva působí jako bezpečnostní upozornění od banky.',
                'safety_card' => 'Zablokovaný účet se přes odkaz v SMS nikdy neřeší. Nenechte se vystrašit. Pokud máte pochybnosti, zavolejte na oficiální číslo banky.',
                'image' => ['path' => 'images/situations/ef24ae7d-1f3c-42b3-a8da-caa66a8ab077.webp', 'alt' => 'Falešná SMS o zablokování bankovního účtu'],
                'options' => [
                    ['name' => 'Kliknu na odkaz a přihlásím se, ať mi účet odblokují.', 'right' => false, 'evaluation' => 'Zadržte! Toto je ta nejnebezpečnější digitální past, která míří přímo na vaše úspory. Text útočí na vaše emoce – na strach z finančního odříznutí a paniku, že jste přišli o přístup ke svým penězům. Pokud na odkaz kliknete a vyplníte přihlašovací údaje, neotevřete své bankovnictví, ale předáte hesla a kódy přímo útočníkům, kteří vám mohou okamžitě vybrat celý účet. Pamatujte si jedno zlaté pravidlo: banka tohle nikdy nedělá. Nikdy vám nepošle SMS s tím, že vám zablokovala účet a vy se musíte přihlásit přes přiložený odkaz.'],
                    ['name' => 'Odepíšu na zprávu a zeptám se, co se s mým účtem děje.', 'right' => false, 'evaluation' => 'Snaha zjistit, co se s vašimi penězi děje, je pochopitelná, ale odepisování na toto číslo je velká chyba. I když chcete situaci jen prověřit, odepisováním pouze potvrzujete podvodníkům, že je vaše číslo aktivní a že vás text vystrašil. Na druhé straně navíc neodpovídá žádný skutečný poradce, ale naprogramovaný robot. Pamatujte: banka tohle nikdy nedělá. Pokud máte pochybnosti, zprávu zavřete, otočte svou platební kartu a zavolejte na oficiální telefonní číslo na její zadní straně, případně se sami přihlaste do své oficiální bankovní aplikace. Tam hned uvidíte, že jsou vaše peníze v pořádku.'],
                    ['name' => 'Zprávu smažu, banka přes odkazy v SMS nekomunikuje.', 'right' => true, 'evaluation' => 'Vynikající! Zachovali jste si chladnou hlavu a nenechali se paralyzovat strachem o své peníze. Správně víte, že banka tohle nikdy nedělá – nikdy neposílá odkazy na přihlášení do internetového bankovnictví v obyčejné textové zprávě. Tím, že jste zprávu smazali a v případě pochybností byste raději sami zavolali na číslo na zadní straně své platební karty, jste své celoživotní úspory udrželi v naprostém bezpečí. Skvělá práce!'],
                ],
            ],
            [
                'button' => 4, 'difficulty' => 2, 'perex' => 'SMS od „exekutora"',
                'description' => 'Na mobil vám přijde SMS z neznámého čísla +420 734 662 901:<br><br>„EXEKUTOR: Evidence dluhu 1 500 Kč. Zaplaťte do 24 hodin, jinak může dojít k zabavení majetku. Platbu proveďte zde: exekuce-platba.cz/uhrada"',
                'safety_card' => 'Přišla vám SMS od exekutora s ultimátem na 24 hodin? Je to stoprocentní podvod. Skutečné dluhy se přes odkazy v mobilu neplatí.',
                'image' => ['path' => 'images/situations/ee0fe0f4-9f83-422c-9005-bbdf8a4dfc3f.webp', 'alt' => 'Falešná SMS od „exekutora" s ultimátem'],
                'options' => [
                    ['name' => 'Kliknu na odkaz a dluh raději zaplatím.', 'right' => false, 'evaluation' => 'Zadržte! Toto je ta nejnebezpečnější digitální past, která míří přímo na vaše úspory. Text útočí na vaše emoce – na strach ze ztráty střechy nad hlavou a paniku, že přijdete o majetek. Pokud na odkaz kliknete a vyplníte platební údaje, nepošlete patnáct set korun exekutorovi, ale předáte hesla ke své kartě nebo účtu přímo útočníkům, kteří vám mohou okamžitě vybrat celé konto. Pamatujte si zlaté pravidlo: skutečný exekutor tohle nikdy nedělá. Nikdy vám nepošle obyčejnou SMS s ultimátem na 24 hodin a odkazem na placení.'],
                    ['name' => 'Odepíšu na zprávu, že je to omyl a žádný dluh nemám.', 'right' => false, 'evaluation' => 'To, že si stojíte za svým a víte, že žádný dluh nemáte, je naprosto správný postoj. Odepisovat na toto číslo je ale velká chyba. Odepisováním pouze potvrzujete podvodníkům, že je vaše číslo aktivní a že ve vás hrozba exekuce vyvolala reakci. Na druhé straně navíc neodpovídá žádný skutečný úředník, ale naprogramovaný robot. Skutečný exekutor tohle nikdy nedělá – oficiální výzvy a exekuce se posílají výhradně doporučeným dopisem s modrým pruhem do vlastních rukou nebo do oficiální Datové schránky. Pokud byste měli pochybnosti, raději se sami podívejte do zabezpečeného Centrálního registru exekucí.'],
                    ['name' => 'Zprávu rovnou smažu, exekutoři takhle přes SMS nekomunikují.', 'right' => true, 'evaluation' => 'Vynikající! Zachovali jste si chladnou hlavu a nenechali se paralyzovat strachem ze zabavení majetku. Správně víte, že skutečný exekutor tohle nikdy nedělá – nikdy neposílá ultimáta na 24 hodin a odkazy na placení v obyčejné SMS. Odhalili jste klíčové varovné znaky: zpráva přišla z obyčejného mobilního čísla, snaží se vás vyděsit umělým spěchem a internetová adresa je zřejmý podvrh. Skutečné úřední záležitosti se řeší přes doporučené dopisy s modrým pruhem nebo oficiální Datovou schránku. Skvělá práce.'],
                ],
            ],
            [
                'button' => 4, 'difficulty' => 3, 'perex' => 'Telefonát od „policie"',
                'description' => 'Přehraje se vám nahrávka telefonátu z neznámého čísla +420 739 504 118.<br><br>Muž č. 1: „Dobrý den, pane Nováku. Tady kapitán Novák z hospodářské kriminálky. Volám kvůli vyšetřování organizované skupiny. U jednoho ze zadržených jsme našli padělaný občanský průkaz s vašimi údaji. Máme podezření, že se někdo ve spolupráci s pracovníkem vaší banky pokouší převést vaše peníze na zahraniční účet. O tomto hovoru s nikým nemluvte, ani s rodinou, abyste nezmařil vyšetřování. Pro ochranu vašich úspor vás přepojím na pracovníka bezpečnostního oddělení banky."<br><br>Muž č. 2: „Dobrý den, tady Marek Dvořák z bezpečnostního oddělení banky. Situace je vážná a musíme jednat rychle. Otevřete si prosím bankovní aplikaci. Budu vás krok za krokem navádět k převodu peněz na rezervní bezpečnostní účet. Jakmile bude případ vyřešen, peníze se vám vrátí. Hlavně zůstaňte na lince."',
                'safety_card' => 'Policie ani banka vás nikdy nebudou po telefonu nutit převádět peníze. Takzvaný „bezpečný účet" neexistuje, je to past zlodějů. Hovor okamžitě položte.',
                'image' => ['path' => 'images/situations/1ce67ca9-0b7e-4788-bd4e-c1ccbb3ef325.webp', 'alt' => 'Telefonát od falešné „policie" a „banky"'],
                'options' => [
                    ['name' => 'Udělám vše podle jejich pokynů a převedu peníze na bezpečný účet.', 'right' => false, 'evaluation' => 'Zadržte! Toto je ta nejpromyšlenější a nejdrtivější digitální past, která vás může připravit o úplně všechny celoživotní úspory. Tento telefonát útočí na vaše emoce – na obrovský šok, strach a respekt k policii. Útočníci jsou na podvody vyškoleni. Záměrně vás nutí držet tajnosti před rodinou, abyste neměli šanci se s nikým poradit. Pokud peníze převedete, nepošlete je na žádný „rezervní" účet, ale přímo do kapes podvodníků a své peníze už nikdy neuvidíte. Pamatujte si neprůstřelné pravidlo: policie ani banka tohle nikdy nedělají. Pokud takový hovor zažijete, okamžitě zavěste a sami zavolejte na oficiální linku své banky nebo na linku 158.'],
                    ['name' => 'Zůstanu na lince, ale budu se s nimi hádat a chtít důkazy.', 'right' => false, 'evaluation' => 'Snaha nenechat se opít rohlíkem je odvážná, ale pouštět se do debaty s těmito lidmi je obrovské riziko. Útočníci na telefonu jsou profesionální manipulátoři, kteří mají na každou vaši otázku připravenou promyšlenou lež. V hádce vás budou dál citově vydírat, strašit vězením za maření vyšetřování, nebo vám pošlou falešný průkaz policisty. Čím déle s nimi mluvíte, tím větší šanci mají vás v panice nakonec přemluvit. Nejlepší obranou není hádka, ale okamžité položení telefonu. Zavěste a pak sami zavolejte na skutečnou policii (158) nebo do své banky.'],
                    ['name' => 'Hovor okamžitě zavěsím, policie ani banka tohle nikdy nežádají.', 'right' => true, 'evaluation' => 'Vynikající! Dokázali jste to nejtěžší – zachovali jste klid a hovor ukončili. Správně víte, že policie ani banka tohle nikdy nedělají. Nikdy po vás nebudou chtít, abyste převáděli peníze na cizí „bezpečné" účty, a nikdy vás nebudou nutit držet tajemství před vlastní rodinou. To dělají pouze podvodníci, kteří vás chtějí izolovat. Tím, že jste jim bez debat položili telefon, jste zničili celou jejich hru. Pokud byste přece jen měli pochybnost, ověřte si to zavoláním na skutečnou policii (158) nebo na oficiální číslo své banky. Skvělá práce.'],
                ],
            ],

            // ============ 5. herní kámen – falešné QR kódy u parkovacího automatu ============
            [
                'button' => 5, 'difficulty' => 1, 'perex' => 'QR kód u parkovacího automatu',
                'description' => 'U parkovacího automatu je přes část původní cedule nalepený papírek s QR kódem: „Zaplaťte parkování zde."<br><br>Pod QR kódem je uvedeno www.platba-parkovani-online.com. Na automatu je zároveň vidět běžný návod k platbě kartou a SMS, ale papírek tvrdí „Platba pouze přes QR kód."',
                'safety_card' => $cardQr,
                'image' => ['path' => 'images/situations/d4b70889-af89-4d56-90b9-a9908fc0d693.webp', 'alt' => 'Nalepený QR kód přes parkovací automat'],
                'options' => [
                    ['name' => 'Naskenuji QR kód a parkovné přes něj rovnou zaplatím.', 'right' => false, 'evaluation' => 'Zadržte! Toto je moderní digitální past, která se maskuje ve fyzickém světě a míří přímo na vaše úspory. Přehlédli jste varovné znaky: tento kód není pevnou součástí originálního stojanu, ale je to narychlo vytištěný papírek amatérsky nalepený přes původní pokyny. Tím, že tam zadáte citlivé údaje ze své platební karty (dlouhé číslo, platnost a CVV kód ze zadní strany), předáte údaje karty rovnou podvodníkům. Skutečné a bezpečné parkovací systémy tyto údaje v mobilu vůbec nepotřebují – vystačí si s vaší registrační značkou (SPZ) a bezpečnou platbou přes Apple/Google Pay.'],
                    ['name' => 'Naskenuji kód jen na zkoušku, abych viděl, kam mě to přesměruje.', 'right' => false, 'evaluation' => 'Zadržte, kapitáne. Chápu vaši zvědavost, ale tady se vydáváte na velmi tenký led. Samotným naskenováním kódu jste sice ještě peníze neodeslali, ale už to je riziko. Ocitli byste se na falešné stránce podvodníků, která může na pozadí zkusit stáhnout do mobilu vir, nebo vás její dokonalý vzhled nakonec přesvědčí údaje z karty opravdu zadat. Podezřelé nálepky na ulici je nejlepší vůbec neskenovat.'],
                    ['name' => 'Nálepku s kódem ignoruji a zaplatím běžným způsobem přímo v automatu.', 'right' => true, 'evaluation' => 'Vynikající! Skvělý postřeh. Zachovali jste si chladnou hlavu a všimli jste si toho nejdůležitějšího varovného znaku: QR kód nebyl pevnou součástí automatu, ale šlo jen o amatérsky nalepený papírek přes původní informace. Přesně takhle vypadá moderní digitální past. Tím, že jste papírek ignorovali a použili oficiální systém automatu, jste své úspory ochránili.'],
                ],
            ],
            [
                'button' => 5, 'difficulty' => 2, 'perex' => 'Falešný QR kód parkování',
                'description' => 'U parkovacího automatu je QR kód s textem „Nově můžete parkování zaplatit online."<br><br>Pod QR kódem je uvedeno parkovani-praha-platba.com. QR kód je nalepený přes starší informační štítek, po okrajích se už odlepuje a je zjevně nalepený přes oficiální ceník. Vedle je napsáno „Zóna P7, parkování 1 hodina: 40 Kč." Po naskenování se na telefonu zobrazí „Příjemce platby: PARK PAY s.r.o."',
                'safety_card' => $cardQr,
                'image' => ['path' => 'images/situations/bb1f8072-cae1-4476-aee0-acc31ef46db5.webp', 'alt' => 'Odlepující se falešný QR kód na automatu'],
                'options' => [
                    ['name' => 'Kód naskenuji a klidně zadám údaje z karty.', 'right' => false, 'evaluation' => 'Zadržte! Toto je moderní digitální past, která se maskuje ve fyzickém světě a míří přímo na vaše úspory. Přehlédli jste varovné znaky: tento kód není pevnou součástí stojanu, ale je to narychlo vytištěný papírek amatérsky nalepený přes původní pokyny. Tím, že tam zadáte citlivé údaje ze své platební karty (dlouhé číslo, platnost a CVV kód ze zadní strany), předáte údaje karty rovnou podvodníkům. Skutečné a bezpečné parkovací systémy tyto údaje v mobilu vůbec nepotřebují – vystačí si s vaší registrační značkou (SPZ) a bezpečnou platbou přes Apple/Google Pay.'],
                    ['name' => 'Zkusím kód naskenovat, abych viděl, na jakou stránku mě to přesměruje.', 'right' => false, 'evaluation' => 'Zadržte, kapitáne! Rozumím, že si chcete situaci jen prověřit, ale tohle je riskantní krok. Útočníci počítají s vaší zvědavostí. Jakmile falešný QR kód naskenujete, dostanete se na podvodnou stránku, která se už rovnou může snažit do vašeho mobilu dostat vir. Navíc stránka s falešnou adresou parkovani-praha-platba.com bude vypadat velmi důvěryhodně a mohla by vás snadno zmanipulovat k zadání údajů z karty. Nejlepší prověření je nenechat se do jejich pasti vůbec vtáhnout.'],
                    ['name' => 'Papírek ignoruji, samolepky přes původní ceník jsou podezřelé. Zaplatím mincemi nebo přes oficiální aplikaci.', 'right' => true, 'evaluation' => 'Vynikající práce! Přesně takhle se pozná zkušený kormidelník. Odhalili jste hned několik varovných signálů: QR kód se na okrajích odlepoval a navíc překrýval oficiální ceník automatu. Kdybyste kód naskenovali a zadali údaje z platební karty, peníze by nešly městu, ale skončily by rovnou na účtu podvodníků, kteří se schovávali za neznámou firmu PARK PAY s.r.o. Tím, že jste zpozorněli a použili oficiální automat nebo ověřenou aplikaci, jste své úspory dokonale ochránili.'],
                ],
            ],
            [
                'button' => 5, 'difficulty' => 3, 'perex' => 'Falešný QR kód parkování',
                // Pozn.: prostřední možnost měla ve scénáři omylem „Plné světlo" – reakce je
                // varovná („vede do pasti") a u obt. 1 a 2 je obdobná možnost špatně, proto right=false.
                'description' => 'U parkovacího automatu je nalepený QR kód s textem „Online platba parkovného." Vedle kódu stojí „Zóna P7, parkování 1 hodina: 40 Kč."<br><br>Pod QR kódem je uvedeno www.platba-parkovani-online.com. Po naskenování se na telefonu zobrazí: „Parkování Praha 7 — 40 Kč", „Příjemce platby: Městská platba online", „Zadejte číslo karty pro dokončení platby." QR kód vypadá jako součást cedule, ale je přelepený přes oficiální ceník. Adresa ani příjemce platby neodpovídají oficiální službě města.',
                'safety_card' => $cardQr,
                'image' => ['path' => 'images/situations/2a4c29e5-9ad9-4d28-b1c5-68a5540c07d7.webp', 'alt' => 'Falešný QR kód parkování žádající údaje z karty'],
                'options' => [
                    ['name' => 'Vyplním číslo karty a platbu rovnou potvrdím.', 'right' => false, 'evaluation' => 'Zadržte! Toto je moderní digitální past, která se maskuje ve fyzickém světě a míří přímo na vaše úspory. Přehlédli jste varovné znaky: tento kód není pevnou součástí stojanu, ale je to papírek amatérsky nalepený přes původní pokyny a oficiální ceník. Navíc adresa webu ani příjemce platby neodpovídají oficiální službě města. Tím, že zadáte citlivé údaje ze své platební karty (dlouhé číslo, platnost a CVV kód ze zadní strany), předáte je rovnou podvodníkům. Skutečné parkovací systémy tyto údaje v mobilu vůbec nepotřebují – vystačí si s vaší SPZ a bezpečnou platbou přes Apple/Google Pay.'],
                    ['name' => 'Zkusím na stránce kliknout na kontakt nebo detaily platby, abych zjistil, o koho jde.', 'right' => false, 'evaluation' => 'Snaha dopátrat se pravdy je logická, ale v tomto případě vás vede do pasti. Útočníci s vaší opatrností počítají – vytvořili stránku, která sice vypadá oficiálně, ale tlačítka pro kontakty budou buď nefunkční, nebo vás dovedou ke smyšleným údajům, které mají jen posílit vaši důvěru. Po naskenování jste sice ještě o peníze nepřišli, ale přehlédli jste varovné znaky přímo na místě: kód není pevnou součástí stojanu a adresa ani příjemce neodpovídají skutečné službě města. Pokud byste zkoumali dál, stránka vás stejně dovede k zadání údajů z karty. Zkoumáním falešného webu riskujete ztrátu úspor.'],
                    ['name' => 'Stránku v mobilu okamžitě zavřu a zaplatím standardně mincemi nebo kartou v automatu.', 'right' => true, 'evaluation' => 'Vynikající! Zachovali jste si chladnou hlavu, projevili skvělý postřeh a nenechali se oklamat. QR kódy na parkovacích stojanech jsou dnes běžným pomocníkem, ale vy jste správně odhalili, že teď jde o podvod. Všimli jste si klíčových znaků: kód nebyl pevnou součástí stojanu, ale byl nalepený přes oficiální ceník, adresa ani příjemce neodpovídali službám města a stránka po vás chtěla citlivé údaje z platební karty. Skutečné systémy tyto údaje v mobilu nepotřebují – vystačí si s SPZ a platbou přes Apple/Google Pay. Tím, že jste podvodnou stránku zavřeli a zaplatili přímo v automatu, jste své úspory udrželi v bezpečí. Skvělá práce!'],
                ],
            ],
        ];
    }

    /**
     * Bonusová „bezpečná" otázka – legitimní potvrzení vlastní platby v bankovní aplikaci.
     * @return array<string, mixed>
     */
    private function bonusQuestion(): array
    {
        return [
            'difficulty' => 1,
            'perex' => 'Potvrzení platby v aplikaci',
            'description' => 'Právě jste si na internetu koupili rychlovarnou konvici za 699 Kč. Na mobilu se vám otevře bankovní aplikace a zobrazí se potvrzení platby:<br><br>Potvrzení platby kartou. Částka: 699 Kč. Obchodník: Elektro Market. Karta: Visa •••• 2481. Čas: dnes, 14:32.<br><br>Dole jsou dvě tlačítka: Potvrdit / Odmítnout. Nikdo vám nevolá, nikdo vás nenavádí a údaje odpovídají nákupu, který jste právě sami provedli.',
            'image' => ['path' => 'images/situations/8b276f31-69b8-4058-912d-f7c87fa0507c.webp', 'alt' => 'Potvrzení platby kartou v bankovní aplikaci'],
            'options' => [
                [
                    'name' => 'Potvrdím platbu. Částka i obchodník odpovídají mému nákupu.',
                    'right' => true,
                    'evaluation' => implode('<br><br>', [
                        'Výborně, kapitáne! Toto je bezpečná situace. Potvrzení přišlo přímo v bankovní aplikaci a údaje odpovídají nákupu, který jste právě sami provedli – sedí částka, obchodník i čas.',
                        'Nikdo vás nenutí jednat pod tlakem, nikdo po vás nechce převod na jiný účet a nikdo vás po telefonu nenavádí, co máte dělat.',
                        'Správně jste rozpoznali běžné a bezpečné potvrzení vlastní platby.',
                    ]),
                ],
                [
                    'name' => 'Platbu odmítnu, protože každé potvrzení v bankovní aplikaci může být nebezpečné.',
                    'right' => false,
                    'evaluation' => 'Pozor, kapitáne! Opatrnost je důležitá, ale tady jste byli až příliš přísní. Potvrzení se zobrazilo přímo v bankovní aplikaci a týká se nákupu, který jste právě sami provedli. Částka i obchodník odpovídají. V této situaci nejde o podvod, ale o běžné potvrzení platby kartou na internetu.',
                ],
                [
                    'name' => 'Zavolám do banky, jestli je bezpečné platbu potvrdit.',
                    'right' => false,
                    'evaluation' => 'Pozor, kapitáne! To je sice velmi opatrný postup, ale v této situaci to není nutné. Platbu jste právě zadali vy sami a potvrzení přišlo přímo v bankovní aplikaci. Částka, obchodník i čas odpovídají vašemu nákupu. Kdyby vám ale někdy volal údajný bankéř a naváděl vás k potvrzení nebo převodu, to už by byl varovný signál.',
                ],
            ],
        ];
    }
};
