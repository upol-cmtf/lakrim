# Labyrinty kritického myšlení – technická dokumentace

| | |
|---|---|
| Software | Labyrinty kritického myšlení (Lakrim) |
| Verze dokumentu | 1.1, září 2026 (přepracována kap. 12: vývojové varianty, přínos, srovnání s existujícími řešeními) |
| Odpovídá stavu kódu | větev `master`, commit `4d77d2e` (13. 8. 2026) |
| Repozitář | https://github.com/upol-cmtf/lakrim |
| Řešitel | Cyrilometodějská teologická fakulta UP v Olomouci, ve spolupráci s AU3V ČR, podpora TA ČR |

## 1. Účel dokumentu

Dokument popisuje, jak byl software navržen, jaké principy byly uplatněny, jaké má datové
struktury, architekturu a rozhraní. Je určen hodnotitelům výsledku typu R (software),
správcům aplikace a vývojářům, kteří ji budou dále rozvíjet. Doplňují ho *Analýza funkčních
požadavků* (`docs/analyza-funkcnich-pozadavku.md`), uživatelská příručka a popis ověření
funkčnosti (samostatné dokumenty).

## 2. Přehled systému

Lakrim je webová vzdělávací hra (serious game) pro seniory zaměřená na rozpoznávání
podvodných, manipulačních a dezinformačních praktik v digitálním prostředí. Hráč řeší
modelové situace, volí reakci a dostává okamžitou, situaci šitou zpětnou vazbu. Aplikace
zároveň slouží jako výzkumný nástroj: každou odpověď ukládá s časem, pořadím pokusu
a kontextem, ve kterém padla, pro následné výzkumné vyhodnocení.

### 2.1 Aktéři

| Aktér | Popis |
|---|---|
| Hráč (respondent) | Anonymní návštěvník, typicky senior. Nemá účet, identifikuje ho náhodný token. |
| Lektor / organizátor akce | Rozdává hráčům odkaz s identifikátorem akce (`hash`), aby šly výsledky skupiny seskupit. |
| Výzkumník | Vyhodnocuje uložená data o odpovědích respondentů (mimo veřejnou část aplikace). |
| Správce obsahu (vývojář) | Přidává a upravuje herní obsah formou datových migrací. |

### 2.2 Tři herní režimy nad jedním jádrem

Aplikace obsahuje tři režimy, které sdílejí datový model otázek, model respondenta,
ukládání odpovědí. Režim je dán hodnotou výčtu `App\Enums\Version`:

| Verze | Režim | Vstupní URL | Frontend | Princip |
|---|---|---|---|---|
| 1 | Znalostní kvíz | `/kviz/{hash?}` | Vue `QuizForm` | Lineární průchod pevně danou sadou otázek typu `select` (jedna volba). |
| 2 | Pexeso | `/pexeso/{hash?}` | Vue `QuestionsTiles` + Livewire `RevealingImage` | 16 políček, za správnou odpověď se odkryje část obrazu. Otázky typu `select` i `multiselect`, až dva pokusy. |
| 3 | Dobrodružná výprava | `/ostrov/{hash?}` | Vue `IslandScene` | Nelineární hra na mapě se 4 ostrovy a 20 kameny; adaptivní obtížnost, karty bezpečí, bonusové otázky, easter eggy, obnovení rozehrané hry. |

## 3. Architektura

### 3.1 Vrstvy

```mermaid
flowchart LR
    subgraph Browser["Prohlížeč hráče"]
        Blade["Blade stránka<br/>(server-rendered obal)"]
        Vue["Vue 3 aplikace<br/>Pinia stores, composables, vue-i18n"]
        LS["localStorage<br/>(postup výpravy, ulovené rybky)"]
    end
    subgraph Server["Laravel 12 (PHP 8.2+)"]
        Routes["routes/web.php"]
        Ctl["HTTP controllers<br/>(validace, orchestrace)"]
        Svc["Služby<br/>SituationSelector, QuizQuestionService,<br/>SituationsResolver, Statistics"]
        Models["Eloquent modely + observer"]
        Res["JSON Resources"]
        LW["Livewire komponenty<br/>(pexeso – odkrývání obrazu)"]
    end
    DB[("MySQL 8")]
    Sentry["Sentry"]

    Blade --> Vue
    Vue <-->|JSON přes axios| Routes
    Vue <--> LS
    Routes --> Ctl --> Svc --> Models --> DB
    Ctl --> Res --> Vue
    Blade <--> LW --> Models
    Server -.chyby.-> Sentry
```

Architektura je třívrstvá s tenkými kontrolery a doménovou logikou ve službách:

- **Prezentační vrstva.** Blade šablony vykreslí stránku a předají Vue komponentě
  startovní data (token respondenta, seznam ostrovů, URL endpointů). Vue komponenta pak
  řídí celý průběh hry a s serverem komunikuje JSON požadavky. Pexeso navíc používá
  Livewire pro server-rendered mřížku odkrývaného obrazu.
- **Aplikační vrstva.** Kontrolery validují vstup (Laravel validator), načtou respondenta
  podle tokenu a delegují na služby. Odpovědi se serializují přes `JsonResource`.
- **Doménová vrstva.** Služby v `app/Services` nesou herní pravidla: adaptivní výběr situace,
  výběr otázky lineárního kvízu, přehled postupu a statistiky. Modely nesou vztahy,
  přetypování a pomocné dotazy; `RespondentAnswerObserver` označí dotazník za dokončený.
- **Datová vrstva.** MySQL 8, schéma i herní obsah spravují migrace.

### 3.2 Průběh jednoho tahu ve výpravě

```mermaid
sequenceDiagram
    participant H as Hráč (Vue IslandScene)
    participant S as SituationController
    participant Sel as SituationSelector
    participant A as AnswerController
    participant DB as MySQL

    H->>S: POST /ostrov/situace {respondent_token, island_id, button}
    S->>Sel: select(respondent, island, button)
    Sel->>DB: nezodpovězené situace kamene + odpovědi respondenta
    Sel-->>S: situace s otázkou v cílové obtížnosti (nebo bonus / null)
    S-->>H: SituationResource {question, options, settings}
    H->>H: volba odpovědi, měření času, případný odpočet
    H->>A: POST /ostrov/odpoved {question_id, option_ids, seconds, attempt, island_id, button}
    A->>DB: uloží RespondentAnswer pro každou zvolenou možnost
    A->>DB: správně → RespondentSituation.completed_at = now()
    A-->>H: {correct, safetyCard, evaluations, correctAnswerEvaluation}
    H->>H: zelená / oranžová (2. pokus) / červená, karta bezpečí, průvodce
```

### 3.3 Technologický stack

| Oblast | Technologie | Verze |
|---|---|---|
| Jazyk / framework | PHP, Laravel | 8.2+ / 12.x |
| Reaktivní server-side UI | Livewire | 3.x |
| Databáze | MySQL | 8.0 |
| Frontend | Vue 3, Pinia, vue-i18n, mitt (event bus), axios | 3.x / 2.x / 11.x |
| Styly | Tailwind CSS 3, Sass | |
| Build | Vite 5, laravel-vite-plugin | |
| Monitoring | Sentry Laravel SDK | 4.x |
| Kvalita | PHPUnit 11, PHPStan/Larastan (level max), PHP_CodeSniffer + Slevomat | |
| CI | GitHub Actions (phpstan, phpcs, testy, merge do `staging`) | |

### 3.4 Struktura repozitáře

```
app/
  Enums/                       Version, Difficulty, QuestionType, Sex
  Exceptions/                  MaximumQuestionsExceeded, QuestionNotFound
  Http/Controllers/Web/Quiz    endpointy kvízu + společné endpointy respondenta
  Http/Controllers/Web/QuizGrid, Web/IslandGame
  Http/Resources/Web           JSON serializace (Question, Situation, EasterEgg, …)
  Livewire/Web                 RevealingImage, RevealingImageItem (pexeso)
  Models/                      Eloquent modely
  Observers/                   RespondentAnswerObserver
  Services/                    doménová logika
database/migrations/           schéma + datové migrace s herním obsahem
resources/js/web/              Vue aplikace (components, composables, stores, i18n, services)
resources/views/               Blade (layouts, web, livewire)
resources/lang/cs/             serverové překlady
public/images, public/videos   grafika ostrovů, situací, easter eggů; videa
tests/Unit, Feature, Integration
```

## 4. Datový model

### 4.1 ER diagram

```mermaid
erDiagram
    ISLANDS ||--o{ SITUATIONS : "má kameny/situace"
    ISLANDS }o--o{ QUESTIONS : "island_question"
    SITUATIONS }o--|| QUESTIONS : "obaluje"
    QUESTIONS }o--|| DIFFICULTY : "má obtížnost"
    QUESTIONS }o--o| QUESTIONS_GROUPS : "téma"
    QUESTIONS ||--o{ QUESTIONS_OPTIONS : "možnosti"
    QUESTIONS ||--o{ QUESTIONS_IMAGES : "[[image:key]]"
    RESPONDENTS }o--o| QUIZ_EVENTS : "akce"
    RESPONDENTS }o--o| AGES : "věková skupina"
    RESPONDENTS ||--o{ RESPONDENTS_ANSWERS : "odpovědi"
    RESPONDENTS_ANSWERS }o--|| QUESTIONS_OPTIONS : "zvolená možnost"
    RESPONDENTS_ANSWERS }o--o| ISLANDS : "kontext (výprava)"
    RESPONDENTS ||--o{ RESPONDENT_SITUATIONS : "splněné situace"
    RESPONDENT_SITUATIONS }o--|| SITUATIONS : ""
    RESPONDENTS ||--o{ RESPONDENT_EASTER_EGGS : "splněné bonusy"
    RESPONDENT_EASTER_EGGS }o--|| EASTER_EGGS : ""
    EASTER_EGGS ||--o{ EASTER_EGG_IMAGES : "[[image:key]]"

    ISLANDS { int id  string name  string image  string guide  text intro  json settings }
    SITUATIONS { int id  int island_id  int question_id  smallint position  string title  text safety_card }
    QUESTIONS { int id  int question_group_id  int difficulty_id  smallint version  bool bonus  string type  text perex  text description  json settings  text first_wrong_answer_evaluation  text second_wrong_answer_evaluation }
    QUESTIONS_OPTIONS { int id  int question_id  string name  text description  float weight  bool right  string evaluation_title  text evaluation  text summary }
    QUESTIONS_IMAGES { int id  int question_id  string key  string path  string alt  int position }
    DIFFICULTY { int id  string name  int min_questions  int max_questions  bool shuffle_questions  bool shuffle_options  bool show_evaluations_for_other_options  json settings }
    QUESTIONS_GROUPS { int id  string name }
    RESPONDENTS { int id  string token  string session_id  string ip  int quiz_event_id  int age_id  char sex  string student_id  bool finished  smallint version }
    RESPONDENTS_ANSWERS { int id  int respondent_id  int question_option_id  int seconds  float weight  int attempt  int island_id  int button }
    RESPONDENT_SITUATIONS { int id  int respondent_id  int situation_id  datetime completed_at }
    QUIZ_EVENTS { int id  string name  string hash }
    AGES { int id  string name }
    EASTER_EGGS { int id  text description  text evaluation }
    EASTER_EGG_IMAGES { int id  int easter_egg_id  string key  string path  string alt  int position }
    RESPONDENT_EASTER_EGGS { int id  int respondent_id  int easter_egg_id  int seconds  datetime completed_at }
```

### 4.2 Popis entit

**Obsahové entity**

| Tabulka | Význam |
|---|---|
| `islands` | Čtyři tematické ostrovy výpravy. `image` a `guide` jsou názvy WebP souborů (ostrov, postava průvodce), `intro` je HTML úvodní řeč průvodce. |
| `situations` | Herní kámen ostrova. `position` 1–5 je index kamene, na jeden kámen připadá více situací (baterie otázek různé obtížnosti). `safety_card` je text karty bezpečí udělené za správné vyřešení. Bonusové otázky situaci nemají; obalují se dočasnou, neuloženou instancí. |
| `questions` | Otázka / modelová situace. `version` určuje režim, `difficulty_id` obtížnost 1–3, `bonus` označuje odměnové otázky výpravy, `type` je `select` nebo `multiselect`. `description` může obsahovat zástupné značky `[[image:key]]`. `first_/second_wrong_answer_evaluation` je vysvětlení po prvním, resp. druhém neúspěšném pokusu. `settings` nese volitelné parametry (viz 7.2). |
| `questions_options` | Možnost odpovědi s příznakem `right`, váhou `weight`, individuálním vyhodnocením (`evaluation_title`, `evaluation`) a větou do závěrečného shrnutí (`summary`). Otázka může mít více správných možností. |
| `questions_images` | Obrázky vkládané do textu otázky přes `[[image:key]]`. |
| `questions_groups` | Tematické okruhy (např. „E-mail od banky“, „Vnuk v nesnázích“, „Hoax“). Ve výpravě jsou otázky bez skupiny, téma nese ostrov. |
| `difficulty` | Tři řádky (1 lehké, 2 střední, 3 těžké). Tabulka má dvojí roli: u otázek udává obtížnost, u režimů 1 a 2 řádek se stejným `id` jako `version` nese nastavení běhu (`max_questions`, míchání otázek a možností, zobrazení vyhodnocení ostatních možností). |
| `easter_eggs`, `easter_egg_images` | Oddechové bonusové úkoly (hledání rozdílů apod.) reprezentované rybkami v moři. Zadání i vyhodnocení jsou HTML s `[[image:key]]`. |
| `quiz_events` | Akce (přednáška, kurz). `hash` je veřejný identifikátor v URL. |
| `ages` | Věkové skupiny pro dobrovolnou sebeidentifikaci. |

**Entity respondenta a sběru dat**

| Tabulka | Význam |
|---|---|
| `respondents` | Jeden herní běh. `token` (UUID v4) je jediný identifikátor, kterým frontend respondenta prokazuje. `version` říká, ve kterém režimu běh vznikl. `finished` nastaví observer po zodpovězení maximálního počtu otázek. `sex`, `age_id`, `student_id` jsou dobrovolné. |
| `respondents_answers` | Jedna zvolená možnost. `seconds` je čas od zobrazení otázky, `attempt` pořadí pokusu (1 nebo 2), `island_id` a `button` kontext ve výpravě. U `multiselect` vzniká řádek pro každou zvolenou možnost. |
| `respondent_situations` | Kámen splněný respondentem (`completed_at`). Základ pro obnovení postupu a pro počty karet bezpečí. |
| `respondent_easter_eggs` | Splněný bonusový úkol včetně času stráveného v úkolu. |

### 4.3 Zásady návrhu datového modelu

- **Oddělení obsahu a běhu.** Obsahové tabulky se mění jen migracemi, tabulky respondenta
  jen za běhu. Vyhodnocení je proto reprodukovatelné a obsah verzovaný spolu s kódem.
- **Jedna banka otázek pro tři režimy.** Režimy sdílejí `questions` a `questions_options`,
  liší se hodnotou `version`. Výsledky ze všech režimů jsou v jedné tabulce odpovědí
  a vyhodnocují se jednotně.
- **Odpověď jako událost, ne stav.** Ukládá se každá volba včetně neúspěšných pokusů,
  což umožňuje analyzovat chování (čas, druhý pokus), ne jen výsledek.
- **Situace jako vazba otázky na herní prostor.** Otázka je znovupoužitelný obsah, situace
  ji umisťuje na konkrétní kámen ostrova a přidává kartu bezpečí.

## 5. Herní principy a algoritmy

### 5.1 Anonymní respondent a token

Při vstupu do režimu vytvoří kontroler nového respondenta s náhodným UUID tokenem, uloží
identifikátor akce z URL a předá token frontendové komponentě. Každý další požadavek
token nese v těle a server jím respondenta dohledá (validace `exists:respondents,token`).
Nepoužívají se účty, e-maily ani cookies s osobními údaji; ukládá se IP adresa
a identifikátor session. Ve výpravě se token navíc drží v serverové session pod klíčem
`island_game_respondent_token`, aby se hráč po návratu na úvodní stránku vrátil ke svému
postupu (viz 5.6).

### 5.2 Adaptivní výběr situace (výprava)

Implementace: `app/Services/IslandGame/SituationSelector.php`, testy
`tests/Feature/Web/IslandGame/SituationTest.php`.

Cílem je nabízet hráči situace na hranici jeho schopností: silnému hráči těžší, slabšímu
lehčí, a to plynule napříč ostrovy, bez skoků po jedné chybě.

**Vstup:** respondent, ostrov, index kamene `button` (1–5).
**Výstup:** situace s otázkou, kterou hráč ještě neřešil, nebo `null`, když na kameni nic nezbývá.

1. **Kandidáti.** Vyberou se situace daného ostrova a kamene, jejichž otázka není bonusová
   a kterou respondent ještě nezodpověděl (množina zodpovězených otázek se odvozuje
   z uložených odpovědí). Prázdná množina → `null`.
2. **Odměna za sérii.** Spočítá se *série* `s` = počet po sobě jdoucích správných odpovědí
   na první pokus, počítáno od poslední odpovědi zpět (chyba sérii nuluje). Povolený počet
   bonusů `b(s)` je 0 pro `s < 3`, 1 pro `3 ≤ s < 6`, 2 pro `s ≥ 6`, nejvýše 2 za hru.
   Pokud respondent dosud dostal méně bonusů než `b(s)`, vrátí se náhodná dosud
   nezodpovězená bonusová otázka verze 3, obalená dočasnou situací pro daný kámen.
   Bonus se tedy neváže na obsah kamene a rozprostře se do hry (nejdříve po 3., pak po 6.
   správné odpovědi v řadě).
3. **Skóre znalostí.** Přes všechny odpovědi respondenta na první pokus (napříč ostrovy,
   v pořadí vzniku) se počítá `score`: správná +1, špatná −1, po každém kroku
   `score = max(0, score)`. Cílová obtížnost `t` je 1 pro `score < 2`, 2 pro `2 ≤ score < 4`,
   3 pro `score ≥ 4`.
4. **Výběr podle obtížnosti.** Prohledá se preference `[t, t−1, …, 1, t+1, …, 3]`; v první
   neprázdné skupině kandidátů se vybere náhodně. Preferuje se tedy nejbližší nižší
   obtížnost před vyšší, aby hráč nebyl přetížen.

Vlastnosti ověřené testy: po dostatečném počtu správných odpovědí přichází těžší otázka;
jedna chyba nesrazí hráče na lehkou úroveň; opakované chyby úroveň postupně snižují;
skóre neklesne pod nulu; započítávají se odpovědi ze všech ostrovů; při nedostupnosti
cílové obtížnosti se sáhne po nejbližší nižší; bonus přijde po sérii a nejvýše dvakrát.

Parametry (prahy skóre 2 a 4, prahy série 3 a 6, maximum 2 bonusy) jsou konstanty třídy
a lze je měnit bez zásahu do zbytku systému. Složitost je lineární v počtu odpovědí
respondenta (desítky řádků), dotazy jsou omezené na jednoho respondenta a jeden kámen.
Původ hodnot parametrů a varianta algoritmu, která této podobě předcházela, jsou popsány
v kap. 12.2 a v Analýze funkčních požadavků, kap. 1.5 a 1.6.

### 5.3 Dvoupokusové vyhodnocení, karty bezpečí a průvodce

Implementace: `app/Http/Controllers/Web/IslandGame/AnswerController.php`,
`resources/js/web/components/IslandGame/composables/useQuestionFlow.js`.

- Odpověď je **správná**, když všechny zvolené možnosti jsou označené jako správné.
  Situace může mít víc přijatelných reakcí; stačí zvolit některou z nich.
- Po **prvním špatném pokusu** dostane hráč vysvětlení k zvolené možnosti, text
  `first_wrong_answer_evaluation`, kámen zežloutne (oranžová) a hráč může zkusit jinou
  možnost; špatně zvolená možnost se zablokuje.
- Po **druhém špatném pokusu** se zobrazí `second_wrong_answer_evaluation`, kámen
  zčervená a situace se uzavře; další kámen se odemkne, aby hráč nezůstal zablokovaný.
- Po **správné odpovědi** se situace označí jako splněná, hráč získá **kartu bezpečí**
  (krátká zapamatovatelná zásada, například jak reagovat na výzvu k platbě), která
  vyskočí jako toast a přidá se na nástěnku v majáku. Bonusová otázka bez vlastní karty
  uděluje univerzální kartu o rozpoznání bezpečné situace.
- Volitelný **časový limit** (`settings.time_limit` v sekundách) se zobrazuje jako odpočet;
  po vypršení se odpověď vyhodnotí jako neúspěšný pokus.
- **Průvodce** ostrova komentuje průběh v bublině (úvod, úspěch, výzva k druhému pokusu,
  neúspěch). Texty jsou v `resources/js/web/i18n/locales/cs.js`.

Barevný stav kamenů (`locked`, `default`, `green`, `orange`, `red`) je jediným zdrojem
pravdy pro odemykání: kameny se odemykají postupně a odemčení nezávisí na správnosti.

### 5.4 Lineární kvíz (verze 1)

Implementace: `app/Services/Quiz/QuizQuestionService.php`, `RespondentAnswerObserver`.

Nastavení běhu (počet otázek, míchání) se čte z řádku tabulky `difficulty` odpovídajícího
verzi. Služba vrací první (nebo náhodnou, je-li zapnuto míchání) dosud nezodpovězenou
otázku dané verze; po dosažení `max_questions` vyhodí `MaximumQuestionsExceededException`,
kterou kontroler přeloží na HTTP 400 s kódem `maximum_questions_exceeded`. Observer po
každé uložené odpovědi ověří, zda respondent zodpověděl maximum, a nastaví `finished`.
Závěrečné shrnutí (`RespondentSummaryController`) spočítá úspěšnost a seskupí věty
`summary` zvolených možností do bloků „zvládli jste“ a „na co si dát pozor“.

### 5.5 Pexeso (verze 2)

Frontend `QuestionsTiles.vue` drží stav 16 políček ve store `QuestionsTilesStore`
(zodpovězeno, správně, špatně, pokus). Otázky typu `multiselect` vyžadují označit všechny
správné možnosti; shrnutí používá šablonu `settings.multiselectSummary` s zástupnými
`:totalSelected` a `:totalRight`. Livewire komponenta `RevealingImage` přijímá událost
`answered` a odkrývá odpovídající dílek obrazu; po odkrytí všech dílků je hra dokončena.

### 5.6 Obnovení rozehrané výpravy

Server drží token rozehraného respondenta v session (klíč `island_game_respondent_token`).
Při návratu na `/ostrov` se použije stejný respondent, pokud odpovídá i akce v URL; jinak
vznikne nový. „Začít znovu“ (`/ostrov-znovu`) token ze session zahodí. Na straně
prohlížeče se pod klíčem svázaným s tokenem drží v `localStorage` vizuální postup
(stavy kamenů, karty bezpečí) a ulovené rybky; při startu nového tokenu se cizí klíče
smažou. Autoritativní postup (splněné situace) je vždy v databázi a frontend si ho může
dotáhnout přes `/kviz/respondent/situations`.

### 5.7 Bonusové úkoly (easter eggy) a rybky

Počet rybek ve scéně se rovná počtu záznamů v `easter_eggs`. Kliknutí na rybku načte
první nesplněný úkol respondenta (`/kviz/easter-egg`), po zavření se uloží splnění
s časem (`/kviz/respondent/easter-egg`) a rybka se přesune do koše v majáku, odkud lze
úkol znovu prohlížet bez logování. Rybky jsou při pauze (intro tour) zastavené a rozmístěné
tak, aby neplavaly společně.

### 5.8 Obrázky v textu

Texty otázek a bonusových úkolů obsahují zástupné značky `[[image:key]]`. Model je při
serializaci nahradí HTML značkou `<img>` s URL z `questions_images`, resp.
`easter_egg_images` (`Question::renderedDescription()`, `EasterEgg::renderedDescription()`).
Obsah se tak ukládá bez absolutních cest a obrázky lze měnit nezávisle na textu.

## 6. Rozhraní

### 6.1 Veřejné stránky

| Metoda | Cesta | Popis |
|---|---|---|
| GET | `/` | Rozcestník režimů; nabídne pokračování rozehrané výpravy. |
| GET | `/kviz/{hash?}` | Založí respondenta verze 1 a vykreslí kvíz. |
| GET | `/pexeso/{hash?}` | Založí respondenta verze 2 a vykreslí pexeso. |
| GET | `/ostrov/{hash?}` | Založí nebo obnoví respondenta verze 3 a vykreslí výpravu. |
| GET | `/ostrov-znovu` | Zahodí rozehranou výpravu ze session a přesměruje na `/ostrov`. |
| GET | `/kviz/dokonceni`, `/kviz/podekovani` | Závěrečné stránky. |
| GET | `/v1/kviz/{hash?}` | Zpětně kompatibilní vstup do kvízu (starší odkazy). |

### 6.2 JSON endpointy hry

Všechny přijímají a vracejí JSON, jsou chráněné CSRF tokenem (meta značka v layoutu,
axios ho posílá automaticky) a vyžadují `respondent_token`. Odpověď je obalená v klíči `data`.

| Metoda | Cesta | Vstup | Výstup |
|---|---|---|---|
| GET | `/kviz/age-list` | – | seznam věkových skupin `{id, name}` |
| POST | `/kviz/question` | `respondent_token`, `version` | otázka `{id, perex, description, type, options[{id,name,description}], settings, group}`; 400 `maximum_questions_exceeded` / `question_not_found` |
| POST | `/kviz/answer` | `respondent_token`, `question_id`, `option_ids[]`, `seconds`, `attempt` | `{end, evaluations[{optionId, evaluation, evaluationTitle, rightAnswer}], correctAnswerEvaluation}` |
| POST | `/kviz/respondent/identification` | `respondent_token`, `sex?`, `age_id?` | 204 |
| POST | `/kviz/respondent/student-id` | `respondent_token`, `student_id` | 204 |
| POST | `/kviz/respondent/summary` | `respondent_token` | `{statistics{totalQuestions, correctAnswers, percentageCorrectAnswers, …}, evaluation{right[], wrong[]}}` |
| POST | `/kviz/respondent/situations` | `respondent_token` | ostrovy se situacemi a příznakem `completed` |
| POST | `/kviz/easter-egg` | `respondent_token` | první nesplněný bonusový úkol `{id, description, evaluation}` nebo `null` |
| POST | `/kviz/respondent/easter-egg` | `respondent_token`, `easter_egg_id`, `seconds` | `{completed: true}` |
| POST | `/ostrov/situace` | `respondent_token`, `island_id`, `button` (1–5) | `{id, position, title, question}`; 404 `no_situation_available` |
| POST | `/ostrov/odpoved` | `respondent_token`, `question_id`, `option_ids[]`, `seconds`, `attempt`, `island_id`, `button` | `{correct, safetyCard, evaluations[], correctAnswerEvaluation}` |

Validace možností kontroluje, že každá `option_id` patří k dané otázce. Chybná validace
vrací HTTP 422 se standardní strukturou Laravelu.

### 6.3 Rozhraní frontend ↔ Blade

Blade předává Vue komponentám vše potřebné jako atributy, takže frontend nezná routy
napevno: `respondent-token`, `situation-url`, `answer-url`, `easter-egg-url`,
`respondent-easter-egg-url`, `completion-url`, `home-url`, `completion-video-url`,
`islands-data` (JSON), `easter-eggs-count`, `settings` (JSON s `maxQuestions`,
`showEvaluationsForOtherOptions`, `requireStudentId`). Kvízové endpointy používá klient
`resources/js/web/services/QuizAPI.js` nad základní URL `VITE_QUIZ_API_URL`.

## 7. Frontend

### 7.1 Struktura

| Část | Soubory | Odpovědnost |
|---|---|---|
| Vstup | `resources/js/web/app.js` | Vytvoří Vue aplikaci, Pinia, i18n, event bus; registruje kořenové komponenty. |
| Výprava | `components/IslandGame/IslandScene.vue` + `components/*` | Scéna moře, mapa ostrovů, detail ostrova, modal otázky, maják (kontakty, karty bezpečí, koš), rybky, intro tour, závěrečný panel s videem. |
| Composables výpravy | `composables/useIslands`, `useQuestionFlow`, `useSafetyCard`, `useGuide`, `useSeaFish`, `useEasterEggs`, `useLighthouseModals`, `assets` | Každý drží jednu oblast stavu a logiky; `IslandScene` je jen skládá. |
| Kvíz | `components/Quiz/*`, stores `QuestionStore`, `QuizStatusBarStore`, `OptionEvalutationStore`, `RespondentIdentificationStore`, `QuizSettingsStore`, `RespondentTokenStore` | Průchod otázkami, progress bar témat, identifikace respondenta, závěr. |
| Pexeso | `components/QuizGrid/*`, store `QuestionsTilesStore` | Mřížka políček, detail otázky, vyhodnocení, spolupráce s Livewire přes událost `answered`. |
| Lokalizace | `i18n/locales/cs.js` | Veškeré texty výpravy (průvodce, tour, maják, karty), HTML v textech se vykresluje přes `v-html`. |

### 7.2 Nastavení otázky (`questions.settings`)

| Klíč | Režim | Význam |
|---|---|---|
| `time_limit` | výprava | Limit na odpověď v sekundách; zobrazí odpočet. |
| `actionTitle` | pexeso | Nadpis nad možnostmi (výchozí „Co uděláte?“). |
| `actionPerex` | pexeso | Doplňující instrukce nad možnostmi. |
| `multiselectSummary` | pexeso | Šablona shrnutí u `multiselect` (`:totalSelected`, `:totalRight`). |

### 7.3 Přístupnost a cílová skupina

Rozhraní je navržené pro seniory: velká tlačítka a písmo, vysoký kontrast, jednoduché
kroky, průvodce s jasnými pokyny, možnost druhého pokusu bez penalizace, intro tour
s možností přeskočení, funkční rozložení na mobilu naležato i na výšku, `aria-label`
u ikonových ovládacích prvků, zoom obrázků ze zadání.

## 8. Správa obsahu

Herní obsah se zadává datovými migracemi (`database/migrations/*seed*`), které vytvářejí
otázky, možnosti, obrázky a situace v transakci a mají implementované `down()`. Zdrojem
byly scénáře odborného týmu (dokumenty DOCX), ze kterých se obsah importoval. Standardní
osazení výpravy: 4 ostrovy × 5 kamenů × 3 obtížnosti (15 situací na ostrov), sada
bonusových otázek a 5 bonusových úkolů. Události kvízu se zakládají migrací
(např. 20 událostí AU3V s maskou `au3v-XX??`). Grafika je v `public/images` ve formátu
WebP, videa v `public/videos`.

## 9. Bezpečnost a ochrana dat

- Hra nevyžaduje registraci ani osobní údaje; identifikace pohlavím, věkovou skupinou
  a studijním číslem je dobrovolná. Ukládá se IP adresa a identifikátor session.
- Respondenta autorizuje pouze náhodný UUID token; endpointy nikdy nevrací data jiného
  respondenta.
- Všechny vstupy procházejí validací (typ, existence v DB, vazba možnosti na otázku,
  rozsah kamene 1–5).
- Formuláře a JSON požadavky chrání CSRF token.
- Chyby se hlásí do Sentry, aplikační log neobsahuje obsah odpovědí.
- Texty obsahu obsahující HTML pochází výhradně z migrací (důvěryhodný zdroj), nikdy od
  uživatele.

## 10. Nasazení a provoz

1. Nastavit `.env` (`APP_URL`, `APP_NAME`, `DB_*`, `VITE_QUIZ_API_URL="${APP_URL}/kviz"`,
   `SENTRY_LARAVEL_DSN`, `SESSION_DRIVER`).
2. `composer install --no-dev --optimize-autoloader`, `npm ci && npm run build`.
3. `php artisan migrate --force` (schéma i obsah), `php artisan config:cache route:cache view:cache`.
4. Webserver směřuje na `public/`; zapisovatelné `storage/` a `bootstrap/cache/`.

CI v GitHub Actions spouští PHPStan, PHP_CodeSniffer a testy nad MySQL službou pro každý
pull request a po úspěchu slučuje `feature/*` a `bugfix/*` větve do `staging`.

## 11. Kvalita kódu a ověřitelnost

- **Statická analýza:** PHPStan (Larastan) na úrovni `max`, generika povolena bez anotace.
- **Styl:** PHP_CodeSniffer s vlastním standardem (`phpcs-standard.xml`, Slevomat).
- **Testy:** PHPUnit, tři sady (Unit, Feature, Integration), 99 testů. Pokrývají
  všechny JSON endpointy včetně validace, adaptivní výběr situací (11 scénářů), bonusy,
  easter eggy, obrázky v textu a shrnutí.
- **Verzování:** git tagy `1.x` (kvíz) a `2.x` (pexeso); vývoj přes pull requesty.

## 12. Softwarový přínos, novost a odlišení od existujících řešení

### 12.1 Řešený technický problém

Software řešil, jak v jedné webové aplikaci propojit adaptivní výběr vzdělávacích situací,
volný (nelineární) průchod herním prostředím a výzkumně využitelný sběr dat, a to pro
anonymního hráče s krátkou historií (nejvýše 20 kamenů) a bez jakýchkoli kalibračních dat.
Výchozí nejistotou bylo, zda lze z takto krátké historie průběžně odhadovat vhodnou
obtížnost tak, aby jedna chyba nevedla k nepřiměřenému snížení obtížnosti, ale opakované
chyby úlohy postupně zjednodušily. Podrobně viz Analýza funkčních požadavků, kap. 1.4.

### 12.2 Vývojové varianty adaptivního algoritmu

Adaptivní výběr situace prošel dvěma implementovanými variantami. Obě jsou zachyceny
v git historii souboru `app/Services/IslandGame/SituationSelector.php` a v testech
`tests/Feature/Web/IslandGame/SituationTest.php`.

| Vlastnost | Varianta A – série (prototyp) | Varianta B – kumulativní skóre (finální) |
|---|---|---|
| Commit, datum | `cf7fbd7`, 17. 5. 2026 | `22ab993`, 18. 6. 2026 |
| Stavová veličina | počet po sobě jdoucích správných odpovědí na první pokus, počítáno od poslední odpovědi zpět | součet +1 za správnou a −1 za špatnou odpověď na první pokus přes celou historii, po každém kroku `max(0, score)` |
| Reakce na správnou odpověď | série +1 | skóre +1 |
| Reakce na chybu | série = 0 | skóre −1 (nikdy pod 0) |
| Prahy pro obtížnost 2 / 3 | 2 / 4 | 2 / 4 (převzato beze změny) |
| Jedna chyba na obtížnosti 3 | okamžitý propad na obtížnost 1 | obtížnost 3 zůstává (skóre 4 → 3) |
| Počet čistých chyb pro pokles 3 → 1 | 1 | 4 |
| Rozlišení jedné chyby od řady chyb | ne | ano |
| Závislost na pořadí ostrovů | vysoká: rozhoduje jen konec historie | nízká: rozhoduje celková bilance |
| Fallback při nedostupné obtížnosti | `t, t−1, …, 1, t+1, …, 3` | beze změny |
| Testy chování | `testServesHarderQuestionAfterCorrectStreak`, `testWrongAnswerResetsDifficultyToEasy` | `testServesHarderQuestionAfterEnoughCorrectAnswers`, `testSingleWrongAnswerDoesNotDropDifficultyToEasy`, `testRepeatedMistakesGraduallyLowerDifficulty`, `testScoreNeverFallsBelowZero`, `testScoreCountsCorrectAnswersFromAllIslands`, `testFallsBackToNearestLowerDifficultyWhenTargetUnavailable` |

Změna byla vyvolána chováním prototypu při hraní: test `testWrongAnswerResetsDifficultyToEasy`
zachycoval jako správné právě to chování (propad po jediné chybě), které se ukázalo pro
cílovou skupinu nevhodné, a byl commitem `22ab993` nahrazen testem opačného tvrzení.

**Porovnání variant simulací.** Rozdíl obou variant je kvantifikován reprodukovatelnou
simulací `docs/simulace/adaptivita.py` (20 kamenů, 20 000 běhů na scénář, hráč odpovídá
správně na první pokus s pevnou pravděpodobností *p*, cílová obtížnost se počítá stejně
jako v kódu):

| p | Varianta | Podíl kamenů s obtížností 1 / 2 / 3 [%] | Změn úrovně za hru | Propadů 3 → 1 za hru | Hráčů, kteří dosáhli obt. 3 [%] |
|---|---|---|---|---|---|
| 0,5 | A | 77,4 / 17,5 / 5,1 | 5,0 | 0,47 | 45,9 |
| 0,5 | B | 54,0 / 27,4 / 18,6 | 5,5 | 0 | 62,0 |
| 0,7 | A | 55,8 / 24,9 / 19,3 | 6,8 | 1,08 | 88,6 |
| 0,7 | B | 24,1 / 22,5 / 53,4 | 4,4 | 0 | 97,1 |
| 0,9 | A | 27,0 / 20,3 / 52,7 | 5,2 | 0,98 | 99,9 |
| 0,9 | B | 12,5 / 12,5 / 75,0 | 2,5 | 0 | 100,0 |

Interpretace: u varianty A dostane i velmi úspěšný hráč (p = 0,9) přibližně jednou za hru
propad ze 3 na 1 a téměř polovinu kamenů řeší pod svou úrovní; u varianty B propad ze 3 na 1
v jednom kroku nenastává nikdy, počet změn úrovně za hru je u silných hráčů poloviční
a podíl těžkých situací odpovídá úspěšnosti hráče. Průměrný hráč (p = 0,5) přitom u obou
variant tráví většinu hry na obtížnosti 1, tedy varianta B nezvyšuje obtížnost slabším
hráčům. Simulace pracuje s konstantním *p* nezávislým na obtížnosti a neuvažuje učení
během hry; slouží k porovnání mechanismů, nikoli k predikci reálných výsledků.
Kalibrace parametrů na datech respondentů je otevřeným bodem (Analýza, kap. 10).

Hodnoty parametrů jsou konfigurační konstanty pravidlového algoritmu, nikoli prvek
novosti. Prahy skóre 2 a 4 byly v prototypové fázi stanoveny heuristicky, expertním
odhadem, a nebyly odvozeny statistickou optimalizací ani porovnáním více číselných variant;
jejich funkčnost potvrzují scénářové testy, optimálnost dosud empiricky prokázána nebyla.
Prahy série 3 a 6 a limit dvou bonusů vycházejí z neformálního pilotního odehrání hry
zástupci cílové skupiny a jsou předběžnou uživatelskou kalibrací. Podrobně Analýza
funkčních požadavků, kap. 1.6 a 1.7.

### 12.3 Softwarový přínos a inovace

**Inovace LAKRIM spočívá v mechanismu adaptivního vzdělávacího průchodu pro krátký
anonymní běh v nelineárním prostředí.** Hráč bez účtu, bez předchozích dat a s nejvýše
20 rozhodnutími volí libovolně mezi tematickými ostrovy. Systém přitom průběžně odhaduje
jeho úroveň z jediného globálního skóre, které vzniká ze všech prvních pokusů napříč
tématy, je tlumeno dolní mezí a preferencí nižší obtížnosti, a řídí současně tři věci:
výběr další situace, zařazení bonusových úloh a podobu zpětné vazby. Tento mechanismus
je spojen s dvoupokusovým průchodem, který chybu vysvětlí, ale nepenalizuje ani neblokuje,
a s událostním datovým modelem, ve kterém výprava sdílí banku otázek i záznam odpovědí
se dvěma referenčními režimy (lineární kvíz, pexeso). Stejný software tak slouží
k výuce i k výzkumnému srovnání účinnosti herních režimů na stejném obsahu.

Přínos má čtyři složky, které jsou každá zvlášť známé, ale v této kombinaci a pro tyto
podmínky nebyly ve srovnávaných řešeních (kap. 12.5) nalezeny:

1. **Globální adaptace nad nelineární mapou.** Výkon se nevyhodnocuje po ostrovech, ale
   jedním skóre ze všech prvních pokusů. Dolní mez nula tlumí reakci na jednotlivou chybu,
   opakované chyby úroveň snižují postupně, při nedostupnosti cílové obtížnosti se volí
   nejbližší nižší. Mechanismus nepotřebuje kalibrační data ani účet hráče, tedy funguje
   v podmínkách, ve kterých psychometrické modely nelze použít.
2. **Řízené zařazování bonusových úloh.** Bonus není obsahem kamene, ale odměnou za sérii
   správných odpovědí, s omezeným počtem za hru, aby nenarušoval srovnatelnost průchodů
   mezi respondenty.
3. **Dvoustupňová zpětná vazba a karty bezpečí.** Odlišné vysvětlení po prvním a druhém
   neúspěchu, blokace již zvolené možnosti, odemčení dalšího kamene i po neúspěchu
   a karta bezpečí jako přenositelná jednotka učení tvoří prostředí, ve kterém chyba
   nepenalizuje ani neblokuje.
4. **Společný událostní datový model tří režimů.** Každá volba se ukládá s časem, pořadím
   pokusu, kontextem ostrova a kamene a zvolenou možností, ve stejné tabulce pro lineární
   kvíz, pexeso i výpravu nad jednou bankou otázek.

**Čím inovace není.** Přínosem není adaptivita jako taková, herní rámec s odměnami,
dvoupokusový kvíz ani sběr odpovědí do databáze; každý z těchto prvků je znám (kap. 12.6).
Přínosem nejsou ani konkrétní hodnoty parametrů (prahy skóre 2 a 4, prahy série 3 a 6,
limit dvou bonusů); jde o konfiguraci, která se může po kalibraci na datech změnit, aniž
by se změnil princip.

**Jak je inovace doložena.** Git historie zachycuje dvě implementované varianty adaptivního
mechanismu a důvod přechodu mezi nimi (kap. 12.2); chování finální varianty ověřuje
11 scénářových testů a reprodukovatelná simulace, která kvantifikuje rozdíl proti prototypu.
Spojení adaptivity, bonusů a zpětné vazby je čitelné v jedné službě (`SituationSelector`)
a jednom kontroleru (`AnswerController`), sdílený datový model v tabulce `answers`.
Co doloženo není, je optimálnost parametrů a účinnost na cílovou skupinu; to jsou otevřené
body pro nasazení v kurzech AU3V (Analýza funkčních požadavků, kap. 10).

### 12.4 Návaznost evaluace na technický vývoj

Tabulka spojuje zjištění z vývoje a evaluace s konkrétní změnou softwaru a jejím dokladem.
Pilotní testování s cílovou skupinou nebylo systematicky protokolováno; jeho průběh
shrnuje Analýza funkčních požadavků, kap. 1.7.

| Zjištění | Úprava softwaru | Technická realizace | Ověření | Doklad |
|---|---|---|---|---|
| Lineární kvíz (verze 1) dává všem stejně těžké otázky a nemotivuje pokračovat. | Nový režim s vizuální odměnou (pexeso), poté výprava s adaptivní obtížností. | verze 2 a 3 nad společnou bankou otázek | funkční testy kvízu a pexesa | tagy `1.0`–`1.6.1`, `2.0`; export dat verze 1 pro výzkumný tým (`7101329`, 7. 4. 2025); vyhodnocení dat eviduje odborný tým mimo repozitář. |
| Hráč potřebuje možnost chybu opravit a odlišné vysvětlení po prvním a druhém omylu. | Druhý pokus; dvě samostatná pole vyhodnocení. | sloupec `attempt`, `first_/second_wrong_answer_evaluation`, `AnswerController` (kvíz i výprava) | `Quiz\AnswerTest`, `IslandGame\AnswerTest` | `14b5ffd` (28. 8. 2025), `4491ef9` (26. 5. 2026) |
| Modelové situace mají více přijatelných reakcí. | Odpověď je správná, pokud hráč zvolil pouze správné možnosti. | `AnswerController::isCorrect` | `testAnswerWithOneOfMultipleRightOptionsIsCorrect` | `9d504e1` (17. 5. 2026), import scénářů odborného týmu |
| Jedna chyba nemá hráče výrazně penalizovat; opakované chyby mají úlohy zjednodušit. | Kumulativní skóre s dolní mezí 0 místo série. | `SituationSelector::knowledgeScore`, `targetDifficulty` | 5 scénářů obtížnosti v `SituationTest`; simulace variant (kap. 12.2) | `22ab993` (18. 6. 2026) |
| Neúspěch nesmí hráče zablokovat. | Po druhé chybě se kámen uzavře červeně a další kámen se odemkne. | stavy kamenů v `useIslands` / `assets.js` | funkční ověření průchodu | `4491ef9` (26. 5. 2026) |
| Delší průchod není vždy možné dokončit najednou. | Obnovení rozehrané hry, volba pokračovat / začít znovu. | session token, `respondent_situations`, `localStorage` | `RespondentSituationsTest`, `HomepageController` | `ac6c073`, `99d856a` (18. 6. 2026) |
| Vzdělávací zásady mají zůstat dostupné i po vyřešení situace. | Karty bezpečí na nástěnce v majáku; univerzální karta za bonus. | `situations.safety_card`, `AnswerController::BONUS_SAFETY_CARD`, `useSafetyCard` | `testCorrectAnswerCompletesSituationAndReturnsSafetyCard` | `ed87e56` (17. 5. 2026), `ce231bc` (25. 6. 2026) |
| Bonusové otázky nemají narušovat srovnatelnost průchodů. | Bonus jen po sérii, nejvýše 2× za hru. | `SituationSelector::maybeBonusSituation` | 3 bonusové scénáře v `SituationTest` | `208e1db` (22. 6. 2026) |
| Pilotáž (05–06/2026, 5 seniorů, Analýza kap. 1.7): hráči postupují plynule; bonusy nemají přicházet příliš brzy ani často. | Bonus po sérii 3 a 6, nejvýše 2× za hru (předběžná uživatelská kalibrace). | konstanty `SituationSelector` | bonusové scénáře v `SituationTest` | `208e1db` (22. 6. 2026) |
| Pilotáž: hráči se chtěli vracet k úvodním instrukcím ostrova; ovládání na mobilu naležato. | Klik na průvodce opakuje úvod; rozložení pro mobil naležato, větší tlačítka; interaktivní prohlídka. | `useGuide`, `GuideBubble.vue`, `IntroTour.vue` | funkční ověření řešitelským týmem | `1f7d85e` (22. 6. 2026), `24e35ff` (24. 6.), `a702349`, `b769ab6` (25. 6. 2026) |
| Pilotáž: chybějící karta u bezpečné situace působila jako chyba; po závěrečném videu chyběly kontakty. | Univerzální karta bezpečí za bonus; telefonní čísla po videu. | `AnswerController::BONUS_SAFETY_CARD`, `ContactsList.vue` | `IslandGame\AnswerTest`; funkční ověření | `ce231bc`, `a702349` (25. 6. 2026) |
| Pilotáž: u bonusových úkolů hráči klikali do obrázků. | Zpřesněné instrukce úkolů (rozdíly se neoznačují; cesty se sledují očima nebo prstem) a doplněné řešení. | texty v migracích easter eggů | funkční ověření | `9b034eb` (15. 6. 2026), `e740227` (25. 6. 2026) |
| Pilotáž a finální scénáře: zpřesnění textů, časový limit u vybraných situací. | Reimport otázek verze 3; limit 59 s u 9 situací. | datové migrace, `settings.time_limit` | `useQuestionFlow.handleTimeUp`; funkční ověření | `13241ce`–`0e05514` (22. 6. 2026) |

### 12.5 Srovnání s existujícími řešeními

Jako přímé komparátory byly zvoleny zavedené digitální hry proti dezinformacím
s publikovanou evaluací. U vlastností, které veřejná dokumentace nebo odborný článek
daného systému nepopisují, je uvedeno „ve veřejné dokumentaci nezjištěno“, nikoli
tvrzení, že systém funkci nemá.

| Řešení | Veřejně popsaný princip | Cílová skupina, délka | Adaptivní obtížnost | Rozdíl LAKRIM |
|---|---|---|---|---|
| **Bad News** (Cambridge Social Decision-Making Lab, DROG, 2018) | Hráč vstupuje do role tvůrce dezinformací, prochází scénáři a získává odznaky za zvládnutí šesti manipulačních technik; založeno na inokulační teorii. | Široká veřejnost, cca 15 minut, lineární scénář. | Ve veřejné dokumentaci nezjištěno. | LAKRIM staví hráče do role příjemce a rozhodující osoby, používá modelové situace z běžného života seniorů (banka, e-shop, „vnuk v nesnázích“), nabízí volný výběr témat a adaptivní obtížnost. |
| **Harmony Square** (Cambridge, DROG, 2020) | Krátká narativní hra založená na psychologické inokulaci proti politickým dezinformacím; hráč rozděluje fiktivní město. | Široká veřejnost, cca 10 minut, 4 úrovně v pevném pořadí. | Ve veřejné dokumentaci nezjištěno. | LAKRIM se zaměřuje na bezpečné reakce seniorů na podvody a manipulaci, nikoli na politickou polarizaci, a propojuje obsah s adaptivním výběrem obtížnosti a dvoupokusovou zpětnou vazbou. |
| **GO VIRAL!** (Cambridge, UK Cabinet Office, WHO, 2020) | Přibližně pětiminutová hra o technikách šíření covidových dezinformací (strach, falešní experti, konspirace). | Široká veřejnost, cca 5 minut. | Ve veřejné dokumentaci nezjištěno. | LAKRIM má čtyři tematické oblasti, 20 kamenů se třemi úrovněmi obtížnosti, možnost hru přerušit a vrátit se a výzkumný sběr dat na úrovni pokusu. |
| **Fakey** (Indiana University OSoMe, 2018) | Simulace sociální sítě; hráč u příspěvků volí sdílení, označení „To se mi líbí“ nebo ověření zprávy; skóre za správné zacházení s věrohodným a nevěrohodným obsahem. | Široká veřejnost, opakovatelná kola. | Ve veřejné dokumentaci nezjištěno. | LAKRIM pokrývá širší typy digitálních rizik (podvodné platby, falešné e-shopy, telefonáty, romantické podvody), používá vysvětlující dvoupokusovou zpětnou vazbu a adaptivní volbu situací místo jednotného kanálu. |
| **The (Mis)Information Game** (Butler a kol., 2023) | Otevřený konfigurovatelný simulátor sociální sítě pro behaviorální výzkum; výzkumník nastavuje příspěvky, zdroje, reakce a sleduje chování účastníků. | Výzkumní účastníci, délka podle konfigurace. | Konfigurace je na straně výzkumníka; přizpůsobení hráči během hry ve veřejné dokumentaci nezjištěno. | LAKRIM propojuje výzkumný sběr dat s individuálním vzdělávacím průchodem pro seniory: adaptace probíhá za běhu podle výkonu hráče a data se sbírají jako vedlejší produkt vzdělávání, nikoli v laboratorním uspořádání. |

Žádný z uvedených systémů podle veřejně dostupných zdrojů necílí primárně na seniory
a nekombinuje nelineární volbu témat s adaptací obtížnosti za běhu. To nedokazuje, že
taková kombinace nikde neexistuje; rešerše pokryla zavedené hry proti dezinformacím
s publikovanou evaluací, ne celý trh vzdělávacích aplikací. Srovnání v této kapitole
vzniklo při zpracování dokumentace (září 2026) nad veřejně dostupnými zdroji; případná
rešerše z přípravy projektu je součástí projektové dokumentace mimo tento repozitář.

Zdroje ke komparátorům:
Roozenbeek a van der Linden, *Fake news game confers psychological resistance against
online misinformation*, Palgrave Communications 2019
(https://www.nature.com/articles/s41599-019-0279-9);
Roozenbeek a van der Linden, *Breaking Harmony Square*, HKS Misinformation Review 2020
(https://misinforeview.hks.harvard.edu/wp-content/uploads/2020/11/roozenbeek_harmony_square_game_misinformation_20201106.pdf);
Basol a kol., *Towards psychological herd immunity*, Big Data & Society 2021
(https://journals.sagepub.com/doi/full/10.1177/20539517211013868);
Micallef a kol., *Fakey: A Game Intervention to Improve News Literacy on Social Media*,
Proc. ACM HCI (CSCW) 2021 (https://dl.acm.org/doi/10.1145/3449080);
Butler a kol., *The (Mis)Information Game: A social media simulator*, Behavior Research
Methods 2023 (https://www.ncbi.nlm.nih.gov/pmc/articles/PMC10991066/,
zdrojový kód https://github.com/TheMisinformationGame/MisinformationGame).

### 12.6 Vymezení novosti vůči stavu poznání

Dynamické přizpůsobování obtížnosti (dynamic difficulty adjustment, DDA) v serious games
je popsaným oborem; přehledové studie rozlišují přístupy založené na pravidlech, na
modelování hráče z výkonu či fyziologických dat a na strojovém učení (například Streicher
a Smeddinck, *Personalized and Adaptive Serious Games*, 2016; přehled DDA metod pro
serious games, Springer 2023, https://link.springer.com/chapter/10.1007/978-3-031-23236-7_11;
*Dynamic Difficulty Adjustment in Serious Games: A Literature Review*, Information 2026,
https://doi.org/10.3390/info17010096). LAKRIM proto **netvrdí novost adaptivity jako
takové**. V této taxonomii je jeho mechanismus pravidlový model hráče z výkonu.

Novost, kterou dokumentace dokládá, spočívá v konkrétním řešení pro podmínky, pro které
běžné DDA přístupy nejsou navrženy: krátký anonymní běh (nejvýše 20 rozhodnutí), žádná
kalibrační data položek, cílová skupina citlivá na neúspěch a volný průchod tematickými
okruhy. Pro tyto podmínky byl navržen a proti prototypové variantě ověřen mechanismus
globálního kumulativního skóre s asymetrickým tlumením (dolní mez, preference nižší
obtížnosti, započítání pouze prvních pokusů), propojený s řízeným zařazováním bonusů,
dvoustupňovou zpětnou vazbou a událostním sběrem dat společným pro tři režimy.

Nejlépe obhajitelným prvkem je způsob propojení nelineárního průchodu, globální adaptace
obtížnosti, odstupňované zpětné vazby a výzkumného sběru dat, nikoli hodnoty parametrů.

Pro označení výsledku za nový poznatek v oblasti programování bude v dalším kroku nutné:
(a) doplnit chybějící údaje o pilotním testování a při dalším testování vést protokol,
(b) kalibrovat parametry na datech z nasazení v kurzech AU3V a
(c) rozšířit rešerši o adaptivní vzdělávací systémy pro seniory mimo oblast dezinformací.
