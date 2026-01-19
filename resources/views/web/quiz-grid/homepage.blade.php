@extends('layouts.web')

@section('content')
    <section class="bg-white">
        <div class="max-w-screen-xl px-4 pt-12 pb-8 mx-auto lg:py-10 lg:pt-16">
            {{-- Hlavní nadpis a úvod --}}
            <div class="text-center mb-12">
                <h1 class="mb-6 text-4xl font-extrabold leading-none tracking-tight text-gray-900 md:text-5xl xl:text-5xl">
                    Vítejte v Labyrintech kritického myšlení
                </h1>

                <p class="max-w-3xl mx-auto text-lg text-gray-600 mb-4">
                    Dobrý den, jsme rádi, že jste se rozhodli vyzkoušet naši hru, která vás naučí, jak se nenechat v digitálním světě napálit. Je pro vás připraveno několik příběhů, se kterými se můžete v běžném životě setkat.
                </p>

                <p class="text-2xl font-semibold text-gray-800 mt-8">
                    Kterou cestou se vydáte?
                </p>
            </div>

            {{-- Dvě cesty --}}
            <div class="grid md:grid-cols-2 gap-8 max-w-5xl mx-auto">
                {{-- Cesta 1: Pro začátečníky --}}
                <div class="bg-blue-50 rounded-2xl p-8 border-2 border-blue-200 hover:border-blue-400 transition-colors">
                    <div class="text-center">
                        <span class="inline-block bg-blue-100 text-blue-800 text-sm font-medium px-3 py-1 rounded-full mb-4">
                            1. cesta
                        </span>
                        <h2 class="text-2xl font-bold text-gray-900 mb-4">
                            Pro začátečníky
                        </h2>
                        <p class="text-gray-600 mb-6 min-h-[80px]">
                            Chcete se nejprve seznámit s tím, jak podvody fungují? Tato verze vás provede úkoly pěkně popořadě.
                        </p>
                        <a href="{{ route('web.quiz.run') }}"
                           class="inline-flex items-center justify-center w-full px-6 py-3 text-base font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700 focus:ring-4 focus:outline-none focus:ring-blue-300 transition-colors">
                            Chci začít od začátku
                            <svg class="w-5 h-5 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"></path>
                            </svg>
                        </a>
                    </div>
                </div>

                {{-- Cesta 2: Pro odvážné --}}
                <div class="bg-purple-50 rounded-2xl p-8 border-2 border-purple-200 hover:border-purple-400 transition-colors">
                    <div class="text-center">
                        <span class="inline-block bg-purple-100 text-purple-800 text-sm font-medium px-3 py-1 rounded-full mb-4">
                            2. cesta
                        </span>
                        <h2 class="text-2xl font-bold text-gray-900 mb-4">
                            Pro odvážné
                        </h2>
                        <p class="text-gray-600 mb-6 min-h-[80px]">
                            Máte už s internetem zkušenosti a chcete si rovnou vyzkoušet složitější situace? Tady si sami vybíráte, který příběh odhalíte dříve.
                        </p>
                        <a href="{{ route('web.quiz-grid.index') }}"
                           class="inline-flex items-center justify-center w-full px-6 py-3 text-base font-medium text-white bg-purple-600 rounded-lg hover:bg-purple-700 focus:ring-4 focus:outline-none focus:ring-purple-300 transition-colors">
                            Zkusím rovnou složitější
                            <svg class="w-5 h-5 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"></path>
                            </svg>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
