@extends('layouts.web')

@section('content')
    <section class="bg-white">
        <div class="grid max-w-screen-xl px-4 pt-10 pb-8 mx-auto lg:gap-8 xl:gap-0 lg:py-16 lg:grid-cols-1 lg:pt-16">
            <h1 class="mb-8 text-4xl font-extrabold leading-none tracking-tight md:text-5xl xl:text-5xl">
                A co teď? Zůstaňte aktivní a informovaní.
            </h1>

            <div class="mt-5">
                <h2 class="mb-3 text-2xl font-extrabold leading-none tracking-tight">
                    Pokračujte ve vzdělávání s Univerzitami třetího věku tzv. U3V:
                </h2>
                <p>Objevte kurzy a přednášky určené seniorům. Získáte nové znalosti, poznáte zajímavé lidi a zůstanete aktivní.</p>
                <p><a href="https://au3v.cz/najdete-u3v-v-okoli-seznam-clenu" target="_blank" class="underline text-blue-600 hover:text-blue-800 visited:text-purple-600">Klikněte sem a najděte Univerzity třetího věku ve vašem okolí.</a></p>
            </div>

            <div class="mt-10">
                <h2 class="mb-3 text-2xl font-extrabold leading-none tracking-tight">
                    Potřebujete právní radu?
                </h2>
                <p>Byli jste podvedeni nebo si nevíte rady se spotřebitelským problémem? Obraťte se na právní poradnu dTestu, kde vám odborníci poradí, jak postupovat. Volání je za cenu běžného hovoru dle vašeho tarifu.</p>
                <p><a href="https://www.dtest.cz/clanek-1530/spotrebitelsky-problem-volejte-nasi-poradnu?utm_source=GoG&utm_medium=poradna_pro_spotrebitele&utm_campaign=STR_S_Poradenstvi&gad_source=1&gclid=CjwKCAiAnKi8BhB0EiwA58DA4eY2cjyWCHwXo85G4GH8wCiWH2_IjuyUttPMrYsNMxzTBoKg0Ba-dB" target="_blank" class="underline text-blue-600 hover:text-blue-800 visited:text-purple-600">Klikněte sem a zjistěte více.</a></p>
            </div>

            <div class="mt-10">
                <h2 class="mb-3 text-2xl font-extrabold leading-none tracking-tight">
                    Nejste na to sami
                </h2>
                <p>Cítíte se osaměle nebo pod tlakem? Zavolejte na Linku seniorů: <span class="font-bold">800 200 007</span> – zdarma, anonymně, každý den od 8:00 do 20:00.</p>
                <p><a href="https://linka-senioru.elpida.cz/" target="_blank" class="underline text-blue-600 hover:text-blue-800 visited:text-purple-600">Více informací o lince seniorů najdete zde.</a></p>
            </div>

            <p class="text-center mt-10">
                <a href="{{ route('web.quiz.thank-you') }}"
                   class="lg:text-lg inline-flex items-center px-5 py-2.5 text-sm font-medium text-center text-white bg-blue-700 rounded-lg hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300">
                    Pokračovat na závěrečné poděkování
                    <icon-arrow-right class="ml-3"></icon-arrow-right>
                </a>
            </p>
        </div>
    </section>
@endsection
