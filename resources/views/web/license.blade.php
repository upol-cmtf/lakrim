@extends('layouts.web')

@section('content')
    <section class="bg-white">
        <div class="max-w-screen-xl px-4 pt-10 pb-8 mx-auto lg:py-16 lg:pt-16 text-gray-800">
            <h1 class="mb-8 text-4xl font-extrabold leading-none tracking-tight md:text-5xl">
                Licenční podmínky
            </h1>

            <p class="text-xl mb-8">
                Hra Labyrinty kritického myšlení (LAKRIM) vznikla v rámci projektu TA ČR SIGMA TQ01000315
                „Labyrinty kritického myšlení“ na Cyrilometodějské teologické fakultě Univerzity Palackého
                v Olomouci ve spolupráci s Asociací univerzit třetího věku ČR.
            </p>

            <h2 class="mb-3 text-2xl font-extrabold leading-none tracking-tight">Vzdělávací obsah</h2>
            <p class="mb-3">
                Není-li u konkrétního materiálu uvedeno jinak, původní vzdělávací obsah vytvořený v rámci projektu,
                zejména herní scénáře, otázky, vysvětlující zpětná vazba, karty bezpečí, metodické texty a původní
                grafické materiály, je licencován pod licencí
                <a href="https://creativecommons.org/licenses/by/4.0/deed.cs" target="_blank" rel="noopener" class="underline text-blue-600 hover:text-blue-800">Creative Commons Uveďte původ 4.0 Mezinárodní (CC BY 4.0)</a>.
            </p>
            <p class="mb-3">
                Licence umožňuje obsah kopírovat, upravovat a dále šířit, včetně komerčního využití, při uvedení
                autora, zdroje, odkazu na licenci a informace o případných změnách.
            </p>
            <p class="mb-3">
                Licence CC BY 4.0 se nevztahuje na loga institucí, video Policie České republiky ani na jiné
                označené materiály třetích stran.
            </p>
            <p class="mb-8">
                Doporučené uvedení původu: <em>Labyrinty kritického myšlení (LAKRIM), Cyrilometodějská teologická
                fakulta Univerzity Palackého v Olomouci a Asociace univerzit třetího věku ČR, projekt TA ČR SIGMA
                TQ01000315, licence CC BY 4.0.</em>
            </p>

            <h2 class="mb-3 text-2xl font-extrabold leading-none tracking-tight">Zdrojový kód</h2>
            <p class="mb-8">
                Zdrojový kód hry je zveřejněn pod licencí MIT ve veřejném repozitáři
                <a href="https://github.com/upol-cmtf/lakrim" target="_blank" rel="noopener" class="underline text-blue-600 hover:text-blue-800">github.com/upol-cmtf/lakrim</a>,
                kde je také technická a programátorská dokumentace. Stručný
                <a href="{{ route('web.functionality') }}" class="underline text-blue-600 hover:text-blue-800">popis funkčnosti softwaru</a>
                je k dispozici i na tomto webu.
            </p>

            <h2 class="mb-3 text-2xl font-extrabold leading-none tracking-tight">Zpracování údajů při hraní</h2>
            <p class="mb-3">
                Hra nevyžaduje registraci ani uvedení jména či kontaktu. Herní běh je identifikován náhodným
                kódem. Pro provoz hry a výzkumné vyhodnocení projektu se ukládá průběh hry (zvolené odpovědi, časy,
                pořadí pokusu), IP adresa a identifikátor relace prohlížeče. Pohlaví a věková skupina jsou
                dobrovolné; studijní číslo se vyplňuje jen v kurzech, které to vyžadují.
            </p>
            <p>
                K uloženým údajům má přístup pouze řešitelský tým projektu. Údaje se uchovávají po dobu řešení
                a vyhodnocení projektu a slouží výhradně k ověření funkčnosti hry a k výzkumu v rámci projektu.
            </p>
        </div>
    </section>
@endsection
