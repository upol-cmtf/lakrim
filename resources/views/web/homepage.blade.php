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

            {{-- Hlavní cesta: Dobrodružná výprava (ostrovy) --}}
            <div class="max-w-3xl mx-auto mb-10">
                <div
                   class="group block bg-gradient-to-br from-cyan-500 to-blue-700 rounded-3xl p-10 shadow-2xl hover:shadow-cyan-300/50 hover:scale-[1.02] transition-all duration-300 text-white text-center relative overflow-hidden">
                    {{-- Dekorativní vlny na pozadí --}}
                    <div class="absolute inset-0 opacity-10 pointer-events-none">
                        <svg viewBox="0 0 800 200" preserveAspectRatio="none" class="w-full h-full">
                            <path d="M0,100 C150,0 350,200 500,100 C650,0 750,150 800,100 L800,200 L0,200 Z" fill="white"/>
                        </svg>
                    </div>

                    <span class="inline-block bg-white/20 text-white text-sm font-semibold px-4 py-1 rounded-full mb-5 tracking-wide uppercase">
                        Doporučená cesta
                    </span>

                    <div class="text-5xl mb-4">🏝️</div>

                    <h2 class="text-3xl font-extrabold mb-4">
                        Dobrodružná výprava
                    </h2>

                    <p class="text-white/90 text-lg mb-8 max-w-xl mx-auto">
                        To největší dobrodružství. Na herním poli budete prozkoumávat různé ostrovy, sbírat karty a přinášet světlo do temnot. Tato cesta je nejvíce hravá. Můžete se k ní kdykoliv vrátit a pokračovat tam, kde jste skončili.
                    </p>

                    @if (! empty($hasInProgressGame))
                        {{-- Rozehraná výprava – nabídneme pokračování i restart --}}
                        <div class="flex flex-col sm:flex-row gap-3 justify-center items-center">
                            <a href="{{ route('web.island-game.homepage') }}"
                               class="inline-flex items-center justify-center px-8 py-4 text-lg font-bold text-[#3a2a05] bg-gradient-to-b from-amber-400 to-amber-500 rounded-xl hover:from-amber-300 hover:to-amber-400 transition-colors shadow-md">
                                Pokračovat ve výpravě
                                <svg class="w-6 h-6 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"></path>
                                </svg>
                            </a>
                            <a href="{{ route('web.island-game.restart') }}"
                               class="inline-flex items-center justify-center px-8 py-4 text-lg font-bold text-white border-2 border-white/80 rounded-xl hover:bg-white/10 transition-colors">
                                Začít znovu
                            </a>
                        </div>
                    @else
                        <a href="{{ route('web.island-game.homepage') }}"
                           class="inline-flex items-center justify-center px-8 py-4 text-lg font-bold text-blue-700 bg-white rounded-xl hover:bg-blue-50 transition-colors shadow-md">
                            Vyrazit na výpravu
                            <svg class="w-6 h-6 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"></path>
                            </svg>
                        </a>
                    @endif
                </div>
            </div>

            {{-- Oddělující text --}}
            <div class="text-center mb-8">
                <span class="text-sm font-medium text-gray-400 uppercase tracking-widest">nebo vyzkoušejte jiný způsob hraní</span>
            </div>

            {{-- Dvě menší cesty --}}
            <div class="grid md:grid-cols-2 gap-6 max-w-4xl mx-auto">
                {{-- Cesta 1: Znalostní kvíz --}}
                <div class="bg-gray-50 rounded-2xl p-7 border border-gray-200 hover:border-blue-300 hover:shadow-md transition-all">
                    <div class="text-center">
                        <span class="inline-block bg-blue-50 text-blue-600 text-xs font-semibold px-3 py-1 rounded-full mb-4 uppercase tracking-wide">
                            1. cesta
                        </span>
                        <div class="text-3xl mb-3">📋</div>
                        <h2 class="text-xl font-bold text-gray-900 mb-3">
                            Znalostní kvíz
                        </h2>
                        <p class="text-gray-500 text-sm mb-6 min-h-[80px]">
                            Ideální pro ty, kteří chtějí pouze prověřit svoje znalosti. Znalostní kvíz s&nbsp;15&nbsp;otázkami pěkně popořadě. Doba hraní je zhruba 20&nbsp;minut.
                        </p>
                        <a href="{{ route('web.quiz.homepage') }}"
                           class="inline-flex items-center justify-center w-full px-5 py-2.5 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700 focus:ring-4 focus:outline-none focus:ring-blue-300 transition-colors">
                            Spustit kvíz
                            <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"></path>
                            </svg>
                        </a>
                    </div>
                </div>

                {{-- Cesta 2: Pexeso --}}
                <div class="bg-gray-50 rounded-2xl p-7 border border-gray-200 hover:border-purple-300 hover:shadow-md transition-all">
                    <div class="text-center">
                        <span class="inline-block bg-purple-50 text-purple-600 text-xs font-semibold px-3 py-1 rounded-full mb-4 uppercase tracking-wide">
                            2. cesta
                        </span>
                        <div class="text-3xl mb-3">🃏</div>
                        <h2 class="text-xl font-bold text-gray-900 mb-3">
                            Pexeso
                        </h2>
                        <p class="text-gray-500 text-sm mb-6 min-h-[80px]">
                            Máte rádi pexeso? Otáčejte herní políčka a hledejte správné odpovědi na&nbsp;16&nbsp;otázek. Za odměnu se vám postupně složí výsledný obraz. Pozor, v&nbsp;této verzi může být správných odpovědí i&nbsp;více najednou. Doba hraní je zhruba 30&nbsp;minut.
                        </p>
                        <a href="{{ route('web.quiz-grid.homepage') }}"
                           class="inline-flex items-center justify-center w-full px-5 py-2.5 text-sm font-medium text-white bg-purple-600 rounded-lg hover:bg-purple-700 focus:ring-4 focus:outline-none focus:ring-purple-300 transition-colors">
                            Hrát pexeso
                            <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"></path>
                            </svg>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
