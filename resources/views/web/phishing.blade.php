<html lang="cs">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Phishing</title>
    @vite(['resources/sass/web/app.scss', 'resources/js/web/app.js'],'build/web')
</head>
<body id="app-web" class="flex flex-col min-h-screen h-screen justify-between">
<section class="max-w-2xl px-6 py-8 mx-auto bg-white dark:bg-gray-900">
    <header>
        <a href="#">
            <img class="w-auto h-7 sm:h-8" src="images/placeholders/bank-logo.png" alt="">
        </a>
    </header>

    <main class="mt-8 font-sans">
        <h2 class="text-gray-700 dark:text-gray-200">Dobrý den,</h2>

        <p class="mt-2 leading-loose text-gray-600 dark:text-gray-300">
            V souladu s nařízením <span class="font-bold">CZ BNR 9/520167</span> se na Vás obracíme, abychom Vás
            informovali o nedávných povinných aktualizacích předpisů, které vyžadují shromažďování dalších informací o
            zákaznících.
        </p>
        <p class="mt-8 leading-loose text-gray-600 dark:text-gray-300">
            <span class="font-bold">Od 15. září 2023</span> Vás bohužel musíme informovat, že některé funkce Vašeho účtu
            jsou dočasně nedostupné, dokud nebudou Vaše údaje aktualizovány.
        </p>

        <p class="text-center">
            <button class="lg:px-8 py-6 mt-5 text-md font-bold text-white transition-colors rounded-lg"
                    style="background-color: #4A7C59">
                Přihlásit se
            </button>
        </p>
    </main>
</section>
</html>
