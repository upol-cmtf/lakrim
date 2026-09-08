# Labyrinty kritického myšlení – programátorská dokumentace

| | |
|---|---|
| Software | Labyrinty kritického myšlení (Lakrim) |
| Verze dokumentu | 1.0, září 2026 |
| Odpovídá stavu kódu | větev `master`, stav ze srpna 2026 |
| Repozitář | https://github.com/upol-cmtf/lakrim (veřejný, licence MIT) |
| Související dokumenty | Analýza funkčních požadavků (`docs/analyza-funkcnich-pozadavku.md`), Technická dokumentace (`docs/technicka-dokumentace.md`) |

## 1. Účel dokumentu

Dokument slouží jako programátorská dokumentace softwaru pro potřeby vyhodnocení projektu
a doložení výsledku. Úplným zdrojovým kódem softwaru je veřejný git repozitář uvedený
v hlavičce; tento dokument je průvodcem po kódu a obsahuje jeho reprezentativní ukázky.
Popisuje, jak je kód členěn, jakými pravidly se řídí, jak se sestavuje, testuje a nasazuje,
a kde hledat implementaci jednotlivých funkcí. Architekturu, datový model a algoritmy
popisuje Technická dokumentace; zde jsou uvedeny v míře potřebné k orientaci v kódu.

Ukázky zdrojového kódu v kap. 5 jsou převzaty doslovně ze souborů v repozitáři ve stavu
uvedeném v hlavičce; u každé ukázky je uvedena cesta k souboru.

## 2. Rozsah a členění zdrojového kódu

### 2.1 Rozsah

| Část | Soubory | Řádky | Poznámka |
|---|---|---|---|
| Aplikační kód PHP (`app/`) | 70 | 3 759 | kontrolery, služby, modely, výčty, příkazy |
| Datové migrace (`database/migrations/`) | 56 | 6 492 | schéma databáze i kompletní herní obsah |
| Frontend (`resources/js/`) | 44 komponent Vue, 21 souborů JS | 7 310 | tři herní režimy, composables, Pinia stores, i18n |
| Blade šablony (`resources/views/`) | 15 | – | vstupní stránky, Livewire pexeso, administrace |
| Automatizované testy (`tests/`) | 19 | 2 210 | 99 testů PHPUnit |
| Git historie | 275 commitů, 16 tagů | – | 08/2024 – 08/2026, vývoj přes pull requesty |

### 2.2 Technologie

| Vrstva | Technologie |
|---|---|
| Backend | PHP 8.2+, Laravel 12, Livewire 3 (pexeso), MySQL 8 |
| Frontend | Vue 3 (Composition API), Pinia, vue-i18n, axios, Tailwind CSS 3, Sass, Vite 5 |
| Kvalita | PHPUnit 11, PHPStan/Larastan (úroveň `max`), PHP_CodeSniffer (PSR-2 + Slevomat), GitHub Actions |
| Provoz | Sentry (monitoring chyb), Laravel Sail (Docker pro vývoj) |

### 2.3 Členění repozitáře

```
app/
  Console/Commands/            stats:island-game, export výsledků
  Enums/                       Difficulty, QuestionType, Sex, Version (1 kvíz, 2 pexeso, 3 výprava)
  Http/Controllers/Admin/      přihlášení, dashboard, exporty pro výzkumný tým
  Http/Controllers/Web/Quiz/   JSON endpointy kvízu a společné endpointy respondenta
  Http/Controllers/Web/QuizGrid/  vstup do pexesa
  Http/Controllers/Web/IslandGame/ vstup do výpravy, výběr situace, uložení odpovědi
  Http/Resources/Web/          JSON transformace (QuestionResource, SituationResource, …)
  Livewire/Web/                odkrývaný obraz pexesa
  Models/                      Eloquent modely (Island, Situation, Question, QuestionOption,
                               Respondent, RespondentAnswer, RespondentSituation, EasterEgg, …)
  Observers/                   RespondentAnswerObserver – označení dokončeného kvízu
  Services/IslandGame/         SituationSelector (adaptivní výběr), GameStatistics
  Services/Quiz/               QuizQuestionService (výběr otázek lineárního kvízu)
  Services/Respondent/         Statistics, SituationsResolver (shrnutí pro respondenta)
database/migrations/           schéma + datové migrace s herním obsahem
resources/js/web/
  components/Quiz/             lineární kvíz (verze 1)
  components/QuizGrid/         pexeso (verze 2)
  components/IslandGame/       výprava (verze 3): IslandScene.vue, components/, composables/
  stores/                      Pinia stores (token respondenta, otázka, nastavení, …)
  services/QuizAPI.js          volání JSON endpointů kvízu a pexesa
  i18n/locales/cs.js           texty rozhraní výpravy
resources/views/               Blade šablony
routes/web.php, admin.php      veřejné a administrační routy
tests/Unit, Feature, Integration  PHPUnit testy
docs/                          projektová dokumentace, simulace adaptivity
.github/workflows/ci.yml       CI: phpstan, phpcs, testy, merge do staging
```

## 3. Konvence a pravidla psaní kódu

- **Jazyk komentářů a dokumentace je čeština.** Identifikátory (třídy, metody, proměnné,
  sloupce) jsou anglické podle konvencí Laravelu. Uživatelské texty výpravy jsou
  v `resources/js/web/i18n/locales/cs.js`, herní obsah v datových migracích.
- **Statická analýza na nejvyšší úrovni.** PHPStan s rozšířením Larastan běží na úrovni
  `max` nad `app/` i `tests/` (`phpstan.neon`). Každá metoda má typové deklarace
  parametrů i návratové hodnoty; pole jsou popsána generickými anotacemi (`int[]`,
  `Collection<int, Situation>`, `list<array{…}>`).
- **Kódovací standard.** PHP_CodeSniffer se standardem PSR-2 doplněným o pravidla Slevomat
  (`phpcs-standard.xml`): zákaz přiřazení v podmínce, zákaz yoda porovnání, zákaz volných
  operátorů rovnosti (`==`), povinná koncová čárka ve víceřádkových voláních a deklaracích,
  abecedně řazené a nepoužité `use`, nepoužité parametry.
- **Kontrolery jsou `final`,** mají jednu veřejnou akci a validují vstup přes
  `$this->validate()`. Logika, která přesahuje validaci a uložení, je v `app/Services`.
- **Herní obsah je kód.** Ostrovy, situace, otázky, karty bezpečí, bonusové úkoly i události
  kurzů jsou datové migrace s metodou `down()`. Čistá instalace po `php artisan migrate`
  obsahuje kompletní hru; změna obsahu prochází pull requestem a CI stejně jako změna kódu.
- **Testy jsou scénářové.** Feature testy volají skutečné HTTP endpointy nad MySQL databází
  a ověřují chování (např. „po pěti správných a jedné špatné odpovědi přijde stále těžká
  situace“), nikoli vnitřní strukturu tříd.
- **Verzování.** Vydání jsou označena tagy `1.x` (kvíz) a `2.x` (pexeso); změny jdou přes
  pull requesty do `master`, CI po úspěchu slučuje feature větve do `staging`.

## 4. Průvodce kódem: jeden tah v Dobrodružné výpravě

Nejrychlejší způsob, jak kód pochopit, je sledovat jeden tah hráče od kliknutí na kámen po
zobrazení karty bezpečí. Soubory jsou uvedeny v pořadí, v jakém se uplatní.

| Krok | Co se děje | Soubor |
|---|---|---|
| 1 | Hráč vstoupí na `/ostrov`. Server najde nebo založí anonymního respondenta s UUID tokenem uloženým v session, aby se šlo k rozehrané hře vrátit. | `app/Http/Controllers/Web/IslandGame/HomepageController.php` |
| 2 | Blade předá token, ostrovy a počet bonusových úkolů Vue aplikaci. | `resources/views/web/island-game/index.blade.php`, `resources/js/web/app.js` |
| 3 | Vue scéna drží stav ostrovů a kamenů (barvy, pokusy, uložené situace). | `resources/js/web/components/IslandGame/IslandScene.vue`, `composables/useIslands.js` |
| 4 | Kliknutí na kámen pošle `POST /ostrov/situace` s tokenem, ostrovem a číslem kamene. | `composables/useQuestionFlow.js` |
| 5 | Kontroler zvaliduje vstup a požádá službu o situaci. | `app/Http/Controllers/Web/IslandGame/SituationController.php` |
| 6 | Služba spočítá skóre znalostí ze všech prvních pokusů, určí cílovou obtížnost, případně nabídne bonus, a vybere situaci. | `app/Services/IslandGame/SituationSelector.php` |
| 7 | Situace se serializuje do JSON (otázka, možnosti, obrázky, časový limit). | `app/Http/Resources/Web/SituationResource.php`, `QuestionResource.php` |
| 8 | Hráč odpoví; frontend pošle `POST /ostrov/odpoved` s pokusem, časem a zvolenými možnostmi. | `composables/useQuestionFlow.js` |
| 9 | Kontroler uloží každou zvolenou možnost jako řádek odpovědi, vyhodnotí správnost, označí situaci za splněnou a vrátí vysvětlení a kartu bezpečí. | `app/Http/Controllers/Web/IslandGame/AnswerController.php` |
| 10 | Frontend obarví kámen (zelený, oranžový pro druhý pokus, červený), zobrazí průvodce a kartu. | `composables/useGuide.js`, `useSafetyCard.js`, `components/GuideBubble.vue`, `SafetyCardToast.vue` |

Lineární kvíz a pexeso používají stejné modely a tabulku odpovědí; liší se kontrolery
v `app/Http/Controllers/Web/Quiz` a `QuizGrid` a frontendem v `components/Quiz`
a `components/QuizGrid`.

## 5. Ukázky zdrojového kódu

### 5.1 Adaptivní výběr situace

Soubor `app/Services/IslandGame/SituationSelector.php` (výběr metod; celý soubor má
234 řádků). Třída je jádrem adaptivity: jedno globální skóre ze všech prvních pokusů,
dolní mez nula, prahy pro obtížnost 2 a 3, preference nižší obtížnosti při nedostupnosti
cílové a bonus za sérii správných odpovědí.

```php
/**
 * Vybírá situaci (baterii otázek) pro konkrétní tlačítko ostrova.
 *
 * Adaptivní obtížnost: drží se jedno skóre znalostí počítané ze všech odpovědí
 * respondenta napříč všemi ostrovy. Správná odpověď ho zvýší, špatná sníží,
 * takže čím lépe si respondent celkově vede, tím těžší otázky se mu nabízejí.
 *
 * Odměna za sérii: když respondent odpovídá pořád správně, jednou za čas (max
 * 2× za celou hru) se mu místo klasické otázky nabídne bonusová otázka.
 */
class SituationSelector
{
    /** Skóre znalostí potřebné pro obtížnost 2, resp. 3. */
    private const SCORE_FOR_MEDIUM = 2;
    private const SCORE_FOR_HARD = 4;

    /** Série správných odpovědí v řadě, která odemkne 1., resp. 2. bonus. */
    private const STREAK_FOR_FIRST_BONUS = 3;
    private const STREAK_FOR_SECOND_BONUS = 6;

    /** Maximální počet bonusových otázek za celou hru. */
    private const MAX_BONUSES = 2;

    /**
     * Vrátí situaci pro dané tlačítko, na kterou respondent ještě neodpověděl,
     * s obtížností co nejblíž jeho aktuální úrovni. Když žádná nezbývá, vrátí null.
     */
    public function select(Respondent $respondent, Island $island, int $button): ?Situation
    {
        $answeredQuestionIds = $respondent->getAnsweredQuestionIds();

        /** @var Collection<int, Situation> $candidates */
        $candidates = Situation::query()
            ->where('island_id', $island->id)
            ->where('position', $button)
            // bonusové otázky se na kameny ostrova nenabízejí
            ->whereHas('question', fn(Builder $query) => $query->where('bonus', false))
            ->when(
                $answeredQuestionIds !== [],
                fn(Builder $query) => $query->whereNotIn('question_id', $answeredQuestionIds),
            )
            ->with(['question.difficulty', 'question.questionGroup', 'question.options'])
            ->get();

        if ($candidates->isEmpty()) {
            return null;
        }

        // odměna za sérii správných odpovědí – místo klasické otázky občas bonus
        $bonus = $this->maybeBonusSituation($respondent, $island, $button, $answeredQuestionIds);
        if ($bonus !== null) {
            return $bonus;
        }

        return $this->pickByDifficulty($candidates, $this->targetDifficulty($respondent));
    }

    /**
     * Cílová obtížnost (1–3) odvozená ze skóre znalostí respondenta.
     */
    private function targetDifficulty(Respondent $respondent): int
    {
        $score = $this->knowledgeScore($respondent);

        return match (true) {
            $score >= self::SCORE_FOR_HARD => 3,
            $score >= self::SCORE_FOR_MEDIUM => 2,
            default => 1,
        };
    }

    /**
     * Adaptivní skóre znalostí počítané ze všech odpovědí respondenta napříč
     * všemi ostrovy. Správná odpověď na první pokus skóre zvýší o 1, špatná
     * sníží o 1; skóre nikdy neklesne pod 0. Díky tomu se obtížnost přizpůsobuje
     * oběma směry a jedna chyba neshodí respondenta rovnou na nejlehčí úroveň.
     */
    private function knowledgeScore(Respondent $respondent): int
    {
        /** @var Collection<int, RespondentAnswer> $answers */
        $answers = $respondent->answers()
            ->where('attempt', 1)
            ->with('option')
            ->get();

        $score = 0;
        foreach ($answers as $answer) {
            $score += $answer->isRightAnswer() ? 1 : -1;
            $score = max(0, $score);
        }

        return $score;
    }

    /**
     * Vybere situaci s cílovou obtížností; když na ní žádná není, sáhne po
     * nejbližší dostupné (nejdřív nižší, pak vyšší). Při více kandidátech náhodně.
     *
     * @param Collection<int, Situation> $candidates
     */
    private function pickByDifficulty(Collection $candidates, int $target): Situation
    {
        $preference = collect([$target])
            ->merge(range($target - 1, 1))
            ->merge(range($target + 1, 3))
            ->unique()
            ->filter(fn(int $difficulty): bool => $difficulty >= 1 && $difficulty <= 3);

        foreach ($preference as $difficulty) {
            $group = $candidates->filter(
                fn(Situation $situation): bool => $situation->question->difficulty->id === $difficulty,
            );

            if ($group->isNotEmpty()) {
                return $group->random();
            }
        }

        return $candidates->random();
    }

    /**
     * Kolik bonusů smí respondent dostat při dané sérii správných odpovědí.
     */
    private function allowedBonuses(int $streak): int
    {
        return match (true) {
            $streak >= self::STREAK_FOR_SECOND_BONUS => 2,
            $streak >= self::STREAK_FOR_FIRST_BONUS => 1,
            default => 0,
        };
    }
}
```

### 5.2 Uložení odpovědi a dvoupokusové vyhodnocení

Soubor `app/Http/Controllers/Web/IslandGame/AnswerController.php` (zkráceno). Každá
zvolená možnost se ukládá jako samostatný řádek s časem, pokusem, kontextem ostrova
a kamene; správnost je definována jako „zvoleny pouze správné možnosti“, takže situace
s více přijatelnými reakcemi nevyžaduje výběr všech.

```php
final class AnswerController extends ApiController
{
    // bonusové otázky nemají vlastní situaci, a tedy ani kartu bezpečí – po jejich
    // správném vyřešení dáme tuto univerzální kartu (rozpoznání bezpečné situace)
    private const BONUS_SAFETY_CARD = 'Ne každá zpráva je podvod. '
    . 'Bezpečné situace umíte rozpoznat – a to je stejně důležité jako odhalit past.';

    public function store(): ArrayResource
    {
        $questionId = $this->request->get('question_id', -1);
        assert(is_numeric($questionId));

        $this->validate($this->request, [
            'respondent_token' => 'required|string|exists:respondents,token',
            'question_id' => 'required|integer|exists:questions,id',
            'option_ids' => 'required|array',
            'option_ids.*' => 'integer|exists:questions_options,id,question_id,' . $questionId,
            'seconds' => 'required|integer',
            'attempt' => 'required|integer',
            'island_id' => 'required|integer|exists:islands,id',
            'button' => 'required|integer|min:1|max:5',
        ]);

        // … načtení otázky a respondenta podle tokenu …

        foreach ($optionIds as $optionId) {
            $option = $question->options()->where('id', $optionId)->first();
            assert($option instanceof QuestionOption);

            $respondent->answers()->create([
                'respondent_id' => $respondent->id,
                'question_option_id' => $option->id,
                'seconds' => $this->request->input('seconds'),
                'weight' => $option->weight,
                'attempt' => $attempt,
                'island_id' => $islandId,
                'button' => $button,
            ]);
        }

        $correct = $this->isCorrect($question, $optionIds);
        // … dohledání situace pro ostrov, kámen a otázku …

        // správná odpověď → situace je splněná, hráč získává kartu bezpečí
        if ($correct && $situation instanceof Situation) {
            RespondentSituation::updateOrCreate(
                ['respondent_id' => $respondent->id, 'situation_id' => $situation->id],
                ['completed_at' => now()],
            );
            $safetyCard = $situation->safety_card;
        }

        // bonusová otázka nemá situaci ani vlastní kartu – po správné odpovědi dáme univerzální kartu
        if ($correct && $question->bonus && $safetyCard === null) {
            $safetyCard = self::BONUS_SAFETY_CARD;
        }

        return new ArrayResource([
            'correct' => $correct,
            'safetyCard' => $safetyCard,
            'evaluations' => $this->evaluations($question, $optionIds),
            'correctAnswerEvaluation' => match ($attempt) {
                1 => $question->first_wrong_answer_evaluation,
                2 => $question->second_wrong_answer_evaluation,
                default => null,
            },
        ]);
    }

    /**
     * Odpověď je správná, když hráč zvolil pouze správné možnosti. Situace může
     * mít víc přijatelných odpovědí – stačí vybrat některou z nich.
     *
     * @param int[] $optionIds
     */
    private function isCorrect(Question $question, array $optionIds): bool
    {
        if ($optionIds === []) {
            return false;
        }

        /** @var int[] $rightOptionIds */
        $rightOptionIds = $question->options()->where('right', true)->pluck('id')->all();

        return array_diff($optionIds, $rightOptionIds) === [];
    }
}
```

### 5.3 Anonymní respondent a obnovení rozehrané hry

Soubor `app/Http/Controllers/Web/IslandGame/HomepageController.php`. Hráč nemá účet;
identitou je UUID token uložený v session a předaný frontendu. Návrat na úvodní stránku
a zpět tak pokračuje v rozehrané hře; „Začít znovu“ token ze session odstraní.

```php
final class HomepageController extends Controller
{
    /** Klíč v session, pod kterým držíme token rozehraného respondenta. */
    public const SESSION_TOKEN_KEY = 'island_game_respondent_token';

    public function index(?QuizEvent $quizEvent = null): View
    {
        // Znovu použijeme respondenta rozehraného v této session (stejná akce),
        // ať se hráč po odchodu na úvodní stránku a návratu zpět vrátí ke svému
        // postupu místo startu od začátku. Nového založíme jen když žádný není
        // (nebo přišel na jinou akci).
        $token = session()->get(self::SESSION_TOKEN_KEY);
        $respondent = $token ? Respondent::where('token', $token)->first() : null;

        if ($respondent && $respondent->quiz_event_id !== $quizEvent?->id) {
            $respondent = null;
        }

        if (!$respondent) {
            $respondent = Respondent::create([
                'session_id' => session()->get('_token'),
                'quiz_event_id' => $quizEvent?->id,
                'token' => Uuid::uuid4()->toString(),
                'ip' => request()->ip(),
                'version' => Version::Three,
            ]);
            session()->put(self::SESSION_TOKEN_KEY, $respondent->token);
        }

        return view('web.island-game.index', [
            'respondentToken' => $respondent->token,
            'islands' => Island::query()->get(['id', 'name', 'image', 'guide', 'intro']),
            // počet easter eggů = počet rybek, které se mají ve scéně vygenerovat
            'easterEggsCount' => EasterEgg::query()->count(),
        ]);
    }

    public function restart(): RedirectResponse
    {
        session()->forget(self::SESSION_TOKEN_KEY);

        return redirect()->route('web.island-game.homepage');
    }
}
```

### 5.4 Frontend: stav rozehrané situace

Soubor `resources/js/web/components/IslandGame/composables/useQuestionFlow.js` (úvod).
Frontend výpravy je rozdělen do composables podle odpovědnosti: `useIslands` (stav
ostrovů a kamenů), `useQuestionFlow` (průběh jedné situace), `useGuide` (průvodce),
`useSafetyCard` (karty), `useEasterEggs` a `useSeaFish` (bonusové úkoly), `useLighthouseModals`
(maják). Komponenta `IslandScene.vue` je jen skládá.

```js
// Rozehraná situace v modálu: načtení otázky pro tlačítko, odpočet času,
// vyhodnocení odpovědi (zelená / oranžová / červená), karta bezpečí, prohlížení
// už vyřešených kamenů a lightbox obrázků ze zadání.
export function useQuestionFlow(props, {
    selectedIsland, guideMessage, guideTone, guideAction,
    showSafetyCard, hideSafetyCard, collectedSafetyCards, close,
}) {
    const { t } = useI18n();

    const activeQuestion = ref(null);
    // stav rozehrané situace v modálu: 'live' = čeká na odpověď,
    // 'retry' = po 1. špatném pokusu, 'finished' = vyhodnoceno (správně nebo 2. špatně)
    const questionStatus = ref('live');
    // prohlížení už vyřešeného kamene – jen čtení, nejde znovu odpovídat
    const reviewMode = ref(false);
    // historie zvolených možností v právě otevřené situaci – zvýrazňuje se v modalu
    const wrongOptionIds = ref([]);
    const correctOptionId = ref(null);

    const isOptionDisabled = (option) => (
        reviewMode.value
        || answerSubmitting.value
        || questionStatus.value === 'finished'
        || wrongOptionIds.value.includes(option.id)
    );

    // --- časový limit na odpověď (volitelný, settings.time_limit = počet sekund) ---
    const remainingSeconds = ref(0);

    // počet sekund z nastavení otázky; null = situace bez limitu
    const parseTimeLimit = (settings) => {
        const seconds = Number(settings?.time_limit);
        return Number.isFinite(seconds) && seconds > 0 ? Math.floor(seconds) : null;
    };

    const timeIsLow = computed(() => questionTimeLimit.value !== null && remainingSeconds.value <= 10);
    // …
}
```

### 5.5 Herní obsah jako datová migrace

Soubor `database/migrations/2026_06_22_150000_seed_digital_traps_questions.php` (úvod).
Každý ostrov má vlastní migraci, která ze scénáře odborného týmu vytvoří 15 situací
(5 kamenů × 3 obtížnosti) a bonusovou otázku. Hlavičkový komentář dokumentuje mapování
sloupců scénáře na sloupce databáze i odchylky od scénáře.

```php
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
 * obtížnosti 3 („Časomíra: 59 sekund").
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
    // down() odstraní situace, otázky, možnosti a obrázky tohoto ostrova
};
```

### 5.6 Scénářový test adaptivity

Soubor `tests/Feature/Web/IslandGame/SituationTest.php` (jeden ze 14 scénářů). Test
sestaví historii odpovědí, připraví situace v různých obtížnostech a ověří přes skutečný
HTTP endpoint, kterou situaci server vrátí.

```php
public function testSingleWrongAnswerDoesNotDropDifficultyToEasy(): void
{
    $respondent = $this->respondent();

    // pět správných a jedna špatná → skóre 4 → stále obtížnost 3
    for ($i = 0; $i < 5; $i++) {
        $this->answer($respondent, Question::factory()->create(), right: true);
    }
    $this->answer($respondent, Question::factory()->create(), right: false);

    $this->situation(button: 1, difficulty: 1);
    $hard = $this->situation(button: 1, difficulty: 3);

    $this->postJson(route(self::ROUTE_NAME), [
        'respondent_token' => $respondent->token,
        'island_id' => 1,
        'button' => 1,
    ])
        ->assertOk()
        ->assertJsonPath('data.id', $hard->id);
}

public function testRepeatedMistakesGraduallyLowerDifficulty(): void
{
    $respondent = $this->respondent();

    // čtyři správné a dvě špatné → skóre 2 → obtížnost 2 (ne rovnou nejlehčí)
    for ($i = 0; $i < 4; $i++) {
        $this->answer($respondent, Question::factory()->create(), right: true);
    }
    $this->answer($respondent, Question::factory()->create(), right: false);
    $this->answer($respondent, Question::factory()->create(), right: false);

    $this->situation(button: 1, difficulty: 1);
    $medium = $this->situation(button: 1, difficulty: 2);
    $this->situation(button: 1, difficulty: 3);
    // … volání endpointu a assertJsonPath('data.id', $medium->id)
}
```

## 6. Rozhraní

Veřejné stránky a JSON endpointy jsou definovány v `routes/web.php`, administrace
v `routes/admin.php`. Podrobný popis vstupů a výstupů je v Technické dokumentaci, kap. 6.

| Metoda a cesta | Kontroler | Účel |
|---|---|---|
| `GET /` | closure | úvodní stránka s volbou režimu, nabídka pokračovat v rozehrané výpravě |
| `GET /ostrov/{hash?}` | `IslandGame\HomepageController@index` | vstup do výpravy, založení nebo obnovení respondenta |
| `GET /ostrov-znovu` | `IslandGame\HomepageController@restart` | zahození rozehrané hry |
| `POST /ostrov/situace` | `IslandGame\SituationController@show` | adaptivní výběr situace pro kámen |
| `POST /ostrov/odpoved` | `IslandGame\AnswerController@store` | uložení odpovědi, vyhodnocení, karta bezpečí |
| `GET /kviz/{hash?}` | `Quiz\HomepageController@index` | vstup do lineárního kvízu |
| `POST /kviz/question`, `/kviz/answer` | `Quiz\QuestionController`, `Quiz\AnswerController` | otázka a odpověď kvízu i pexesa |
| `POST /kviz/respondent/…` | `Quiz\Respondent*Controller` | identifikace, shrnutí, situace, bonusové úkoly respondenta |
| `GET /pexeso/{hash?}` | `QuizGrid\HomepageController@index` | vstup do pexesa (Livewire) |
| `GET /admin/…` | `Admin\*` | přihlášení, dashboard, exporty výsledků (CSV, XLSX) |

Nepovinný `{hash}` je identifikátor události (kurzu nebo přednášky) z tabulky `quiz_events`;
respondenti z jedné akce se podle něj seskupují ve výstupech.

## 7. Sestavení, spuštění a testy

Požadavky: PHP 8.2+, Composer, Node.js 18+, MySQL 8 (nebo Docker s Laravel Sail).

```bash
git clone git@github.com:upol-cmtf/lakrim.git
cd lakrim
composer install
npm install
cp .env.example .env
php artisan key:generate
# v .env nastavte DB_* a APP_URL, VITE_QUIZ_API_URL="${APP_URL}/kviz"
php artisan migrate        # schéma i kompletní herní obsah
npm run build              # nebo `npm run dev` pro vývoj s HMR
php artisan serve
```

Kontrola kvality a testy:

```bash
composer phpstan            # statická analýza, úroveň max
composer phpcs              # kódovací standard
composer check-all          # obojí
vendor/bin/phpunit          # 99 testů; vyžaduje MySQL databázi `testing` (phpunit.xml)
```

Nástroje pro výzkumný tým:

```bash
php artisan stats:island-game                 # statistiky výpravy: úspěšnost po obtížnostech, odpadávání
php artisan stats:island-game --event=HASH    # jen jedna akce (kurz)
php artisan stats:island-game --json          # strojově čitelný výstup
python3 docs/simulace/adaptivita.py           # simulace variant adaptivního algoritmu
```

Exporty odpovědí (CSV, XLSX) jsou v administraci (`/admin`), přihlášení uživatelem
z tabulky `users`.

## 8. Kontinuální integrace

Soubor `.github/workflows/ci.yml` spouští nad každým pull requestem a každým pushem
do `master` tři nezávislé úlohy: PHPStan (PHP 8.2), PHP_CodeSniffer (PHP 8.3) a PHPUnit
nad MySQL 8 s sestavením frontendu (`npm run build`). Po úspěchu všech tří se větve
`feature/*` a `bugfix/*` automaticky slučují do větve `staging`, která odpovídá
testovacímu prostředí. Sloučení do `master` provádí vývojář ručně přes pull request.

## 9. Kde hledat implementaci jednotlivých funkcí

| Funkce | Backend | Frontend | Testy |
|---|---|---|---|
| Adaptivní výběr obtížnosti | `Services/IslandGame/SituationSelector.php` | – | `IslandGame/SituationTest.php` |
| Bonus za sérii správných odpovědí | `SituationSelector::maybeBonusSituation` | `useQuestionFlow.js` | `SituationTest` (3 scénáře) |
| Dvoupokusové vyhodnocení, karta bezpečí | `IslandGame/AnswerController.php` | `useQuestionFlow.js`, `useGuide.js`, `useSafetyCard.js` | `IslandGame/AnswerTest.php` |
| Obnovení rozehrané hry | `IslandGame/HomepageController.php`, `Quiz/RespondentSituationsController.php` | `useIslands.js` (localStorage) | `Quiz/RespondentSituationsTest.php` |
| Bonusové úkoly (easter eggy), rybky | `Quiz/EasterEggController.php`, `RespondentEasterEggController.php` | `useEasterEggs.js`, `useSeaFish.js`, `EasterEggModal.vue` | `EasterEggTest.php`, `RespondentEasterEggTest.php` |
| Časový limit situace | `questions.settings.time_limit` (migrace obsahu) | `useQuestionFlow.js` | – |
| Obrázky v textu otázek | `Models/QuestionImage.php`, `QuestionResource.php` | `ImageZoom.vue` | `QuestionImageTest.php` |
| Lineární kvíz | `Quiz/QuestionController.php`, `Services/Quiz/QuizQuestionService.php` | `components/Quiz/` | `Quiz/QuestionTest.php`, `AnswerTest.php`, `RunTest.php` |
| Pexeso | `QuizGrid/HomepageController.php`, `Livewire/Web/RevealingImage*.php` | `components/QuizGrid/` | – (Livewire, ověřováno funkčně) |
| Identifikace studijním číslem, shrnutí | `Quiz/RespondentIdentificationController.php`, `RespondentSummaryController.php` | `StudentIdForm.vue`, `RespondentIdentification.vue` | `RespondentIdentificationTest.php`, `RespondentSummaryTest.php` |
| Statistiky a exporty | `Console/Commands/IslandGameStatsCommand.php`, `Services/IslandGame/GameStatistics.php`, `Admin/Export*Controller.php` | – | `Console/IslandGameStatsCommandTest.php` |
| Herní obsah | `database/migrations/2026_05_13_*` až `2026_07_02_*` | `i18n/locales/cs.js` | – |

## 10. Historie vývoje v repozitáři

Repozitář obsahuje úplnou historii vývoje od srpna 2024 (275 commitů ve větvi `master`).
Klíčové milníky, které lze v historii dohledat:

| Období | Doklad | Milník |
|---|---|---|
| 01/2025 | tag `1.0` | první vydání lineárního kvízu |
| 01/2025 | git historie | identifikace studijním číslem pro kurzy AU3V |
| 08/2025 | git historie | druhý pokus a dvojí vysvětlení chybné odpovědi |
| 11/2025 | tag `2.0` | první vydání pexesa |
| 01/2026 | git historie | mapa moře s ostrovy, začátek výpravy |
| 05/2026 | historie `SituationSelector` | první verze výběru situace (prototypová varianta podle série) |
| 05/2026 | git historie | dvoupokusový průběh, průvodce, karty bezpečí |
| 06/2026 | historie `SituationSelector` | finální adaptivní algoritmus (kumulativní skóre) |
| 06/2026 | historie `SituationSelector`, datové migrace | bonus za sérii, reimport obsahu z finálních scénářů |
| 06/2026 | pull request `feature/v3-final` | sloučení výpravy do `master` |
| 07/2026 | datová migrace | události kurzů AU3V pro nasazení |

Srovnání prototypové a finální varianty adaptivního algoritmu, včetně simulace, je
v Technické dokumentaci, kap. 12.2, a ve skriptu `docs/simulace/adaptivita.py`.
