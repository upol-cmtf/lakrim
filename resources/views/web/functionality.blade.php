@extends('layouts.web')

@section('content')
    <section class="bg-white">
        <div class="max-w-screen-xl px-4 pt-10 pb-8 mx-auto lg:py-16 lg:pt-16 text-gray-800">
            <h1 class="mb-8 text-4xl font-extrabold leading-none tracking-tight md:text-5xl">
                Popis funkčnosti
            </h1>

            <p class="text-xl mb-8">
                LAKRIM – Labyrinty kritického myšlení je webová vzdělávací aplikace vytvořená jako výsledek projektu
                TA ČR SIGMA TQ01000315. Je zaměřena na rozvoj kritického posuzování informací, rozpoznávání misinformací
                a dezinformací a bezpečné rozhodování v digitálním prostředí. Software je určen zejména starším dospělým
                a lze jej využívat při výuce na univerzitách třetího věku i k samostatnému procvičování.
            </p>

            <h2 class="mb-3 text-2xl font-extrabold leading-none tracking-tight">Herní režimy</h2>
            <p class="mb-8">
                Aplikace obsahuje tři samostatné herní režimy: Dobrodružnou výpravu, Znalostní kvíz a Pexeso.
                Jednotlivé režimy využívají modelové situace, otázky, okamžité vyhodnocení odpovědí a edukativní
                zpětnou vazbu.
            </p>

            <h2 class="mb-3 text-2xl font-extrabold leading-none tracking-tight">Dobrodružná výprava</h2>
            <p class="mb-3">
                Klíčovou funkcí Dobrodružné výpravy je adaptivní výběr obtížnosti úloh. Hra obsahuje čtyři tematické
                ostrovy, každý s pěti postupně odemykanými situacemi. Obtížnost předkládaných situací se průběžně
                přizpůsobuje dosavadním výsledkům hráče na základě kumulativního skóre z prvních pokusů napříč ostrovy.
                Mechanismus je nastaven tak, aby jednotlivá chyba nevedla k přímému poklesu z nejvyšší na nejnižší
                úroveň obtížnosti, zatímco opakované chyby vedou k jejímu postupnému snižování. Pokud situace v cílové
                obtížnosti není dostupná, systém vybírá nejbližší dostupnou variantu s preferencí nižší obtížnosti.
            </p>
            <p class="mb-8">
                Dobrodružná výprava dále využívá dvoupokusové řešení úloh s odlišnou zpětnou vazbou po prvním a druhém
                neúspěšném pokusu, bonusové úlohy za sérii správných odpovědí, karty bezpečí, vybrané časově omezené
                situace a možnost přerušit a následně obnovit rozehranou hru.
            </p>

            <h2 class="mb-3 text-2xl font-extrabold leading-none tracking-tight">Znalostní kvíz a Pexeso</h2>
            <p class="mb-8">
                Znalostní kvíz umožňuje procvičovat znalosti prostřednictvím sady otázek s okamžitým vyhodnocením
                a závěrečným shrnutím výsledků. Pexeso propojuje řešení modelových situací s postupným odkrýváním
                výsledného obrazu a poskytuje hráči zpětnou vazbu k jeho odpovědím.
            </p>

            <h2 class="mb-3 text-2xl font-extrabold leading-none tracking-tight">Záznam průběhu hry</h2>
            <p>
                Software zaznamenává průběh hry a zvolené odpovědi pro vyhodnocení výsledků. Používání aplikace
                nevyžaduje registraci ani vytvoření uživatelského účtu. Podrobnosti o ukládaných údajích uvádějí
                <a href="{{ route('web.license') }}" class="underline text-blue-600 hover:text-blue-800">licenční podmínky</a>.
            </p>
        </div>
    </section>
@endsection
