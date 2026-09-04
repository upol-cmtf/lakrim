# Labyrinty kritického myšlení (Lakrim)

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

## Dokumentace

| Dokument | Soubor |
|---|---|
| Analýza funkčních požadavků | [docs/analyza-funkcnich-pozadavku.md](docs/analyza-funkcnich-pozadavku.md) |
| Technická dokumentace (architektura, datový model, algoritmy, rozhraní) | [docs/technicka-dokumentace.md](docs/technicka-dokumentace.md) |

Programátorskou dokumentaci tvoří tento repozitář: zdrojový kód s komentáři (česky),
typové anotace kontrolované PHPStanem na úrovni `max` a sada automatizovaných testů.

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

Vydání jsou označena git tagy: řada `1.x` (znalostní kvíz), `2.x` (pexeso), aktuální vývoj
přidává režim Dobrodružná výprava (verze 3). Změny procházejí pull requesty do větve `master`.

## Autoři a licence

Zadavatel a odborný obsah: Cyrilometodějská teologická fakulta UP v Olomouci, AU3V ČR.
Vývoj softwaru: Tomáš Pavlík. Licence: MIT.
