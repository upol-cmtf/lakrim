# Labyrinty kritického myšlení (LAKRIM)

Webová vzdělávací hra, která učí seniory rozpoznávat podvody, manipulaci a dezinformace
v digitálním prostředí a bezpečně na ně reagovat. Hráč prochází modelové situace z běžného
života (e-mail od banky, „vnuk v nesnázích“, falešný e-shop, hoax, romantický podvod…),
volí reakci a dostává okamžitou zpětnou vazbu s vysvětlením.

Software vznikl na Cyrilometodějské teologické fakultě Univerzity Palackého v Olomouci
ve spolupráci s Asociací univerzit třetího věku ČR (AU3V) s podporou Technologické agentury
České republiky (TA ČR).

> Hra je vzdělávacím dílem. Situace, osoby, organizace, značky a weby ve hře jsou fiktivní
> nebo upravené pro vzdělávací účely. Podrobné prohlášení je v patičce aplikace.

## Herní režimy

Aplikace nabízí tři způsoby hraní nad společnou bankou otázek a společným sběrem dat:

| Režim | URL | Popis |
|---|---|---|
| **Dobrodružná výprava** (doporučená) | `/ostrov` | Herní mapa se čtyřmi ostrovy (digitální pasti, klamavé zprávy, zneužité city, falešné bohatství). Každý ostrov má 5 kamenů, pod každým kamenem baterie situací ve třech obtížnostech. Obtížnost se přizpůsobuje hráči, správné odpovědi přinášejí karty bezpečí, série správných odpovědí odemyká bonusové otázky. Hru lze přerušit a vrátit se k ní. |
| **Znalostní kvíz** | `/kviz` | Lineární kvíz s 15 otázkami popořadě, průběžný ukazatel témat a závěrečné shrnutí. |
| **Pexeso** | `/pexeso` | 16 herních políček, odkrývání výsledného obrazu za správné odpovědi. Otázky mohou mít i více správných odpovědí. |

Ke každému režimu lze přidat identifikátor akce (`/ostrov/{hash}`, `/kviz/{hash}`, `/pexeso/{hash}`),
podle kterého se respondenti z jedné přednášky či kurzu seskupí ve výstupech.

## V čem je LAKRIM nový

LAKRIM přizpůsobuje obtížnost situací hráči, který nemá účet, o kterém nejsou žádná
předchozí data a který si sám volí, jaké téma bude hrát. Jedno globální skóre ze všech
prvních pokusů napříč ostrovy, tlumené tak, aby jedna chyba hráče nesrazila a opakované
chyby úlohy postupně zjednodušily, určuje obtížnost další situace. Bonusové otázky se
zařazují samostatně podle série správných odpovědí a zpětná vazba vychází ze správnosti
a pořadí pokusu. Chyba se vysvětlí ve dvou krocích, ale hráče nepenalizuje ani nezablokuje. Výprava, kvíz
a pexeso sdílejí jednu banku otázek a jeden záznam odpovědí, takže stejný software slouží
k výuce i k výzkumnému srovnání herních režimů. Podrobně v technické dokumentaci, kap. 12.

## Dokumentace

| Dokument | Soubor |
|---|---|
| Analýza funkčních požadavků | [docs/analyza-funkcnich-pozadavku.md](docs/analyza-funkcnich-pozadavku.md) |
| Technická dokumentace (architektura, datový model, algoritmy, rozhraní) | [docs/technicka-dokumentace.md](docs/technicka-dokumentace.md) |
| Programátorská dokumentace (členění kódu, konvence, ukázky zdrojového kódu, sestavení a testy) | [docs/programatorska-dokumentace.md](docs/programatorska-dokumentace.md) |
| Simulace variant adaptivního algoritmu (porovnání prototypu a finální verze) | [docs/simulace/adaptivita.py](docs/simulace/adaptivita.py) |

Úplným zdrojovým kódem je tento repozitář: kód s komentáři (česky), typové anotace
kontrolované PHPStanem na úrovni `max` a sada automatizovaných testů. Programátorská
dokumentace je průvodcem po tomto kódu s jeho ukázkami.

## Technologie

- **Backend:** PHP 8.2+, Laravel 12, Livewire 3, MySQL 8, Sentry (monitoring chyb)
- **Frontend:** Vue 3, Pinia, vue-i18n, Tailwind CSS 3, Sass, Vite 5
- **Kvalita:** PHPUnit 11, PHPStan/Larastan (level max), PHP_CodeSniffer se Slevomat standardem, GitHub Actions CI

## Instalace pro vývoj

Požadavky: PHP 8.2+, Composer, Node.js 18+, MySQL 8 (nebo Docker s Laravel Sail).

```bash
git clone git@github.com:upol-cmtf/lakrim.git
cd lakrim
composer install
npm install
cp .env.example .env
php artisan key:generate
# v .env nastavte DB_* a APP_URL, VITE_QUIZ_API_URL="${APP_URL}/kviz"
php artisan migrate        # vytvoří schéma a naplní veškerý herní obsah
npm run build              # nebo `npm run dev` pro vývoj s HMR
php artisan serve
```

Alternativně přes Docker: `./vendor/bin/sail up -d && ./vendor/bin/sail artisan migrate`.

Herní obsah (ostrovy, situace, otázky, karty bezpečí, bonusové úkoly, události kvízu) je
verzován jako datové migrace v `database/migrations`, takže čistá instalace obsahuje kompletní hru.

## Testy a kontrola kódu

```bash
vendor/bin/phpunit          # jednotkové, feature a integrační testy (vyžadují MySQL databázi `testing`)
composer check-all          # phpcs + phpstan
```

CI (`.github/workflows/ci.yml`) spouští všechny tři kontroly nad každým pull requestem
a po jejich úspěchu slučuje feature větve do větve `staging`.

## Struktura repozitáře

```
app/
  Http/Controllers/Web/Quiz        endpointy znalostního kvízu a společné endpointy respondenta
  Http/Controllers/Web/QuizGrid    vstup do pexesa
  Http/Controllers/Web/IslandGame  vstup do výpravy, výběr situace, uložení odpovědi
  Services/IslandGame              adaptivní výběr situací (SituationSelector)
  Services/Quiz                    výběr otázek lineárního kvízu
  Services/Respondent              statistiky a přehled postupu respondenta
  Models                           Eloquent modely (Island, Situation, Question, Respondent, …)
database/migrations                schéma i herní obsah
resources/js/web                   Vue aplikace (Quiz, QuizGrid, IslandGame), Pinia stores, i18n
resources/views                    Blade šablony (web, Livewire)
tests                              PHPUnit testy
docs                               projektová dokumentace
```

## Verze

Vydání jsou označena git tagy: řada `1.x` (znalostní kvíz), `2.x` (pexeso) a `3.x`
(Dobrodružná výprava; `3.0` z června 2026 je první vydání se všemi třemi režimy). Změny
procházejí pull requesty do větve `master`.

## Autoři a licence

Zadavatel a odborný obsah: Cyrilometodějská teologická fakulta UP v Olomouci, AU3V ČR.
Vývoj softwaru: Tomáš Pavlík.

Zdrojový kód je poskytován pod licencí MIT (soubor [LICENSE](LICENSE)). Původní vzdělávací
obsah (scénáře, otázky, zpětná vazba, karty bezpečí, původní grafika) je poskytován pod
licencí CC BY 4.0 (soubor [CONTENT-LICENSE.md](CONTENT-LICENSE.md)), není-li u konkrétního
materiálu uvedeno jinak; licence se nevztahuje na loga institucí a video Policie ČR.
Licenční podmínky jsou také na https://www.lakrim.cz/licencni-podminky/.
