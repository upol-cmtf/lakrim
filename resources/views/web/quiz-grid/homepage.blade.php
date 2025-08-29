@extends('layouts.web')

@section('content')
    <section class="bg-white">
        <div class="grid max-w-screen-xl px-4 pt-20 pb-8 mx-auto lg:gap-8 xl:gap-0 lg:py-16 lg:grid-cols-12 lg:pt-28">
            <div class="mr-auto place-self-center lg:col-span-7 md:text-lg lg:text-xl">
                <h1 class="max-w-2xl mb-8 text-4xl font-extrabold lg:leading-3 leading-7 tracking-tight md:text-5xl xl:text-5xl text-center">
                    Zahrajte si hru s 16 krátkými příběhy ze života
                </h1>

                <div class="max-w-2xl mb-6 lg:mb-8">
                    <p>Jejich luštěním se <span class="font-bold">naučíte poznat triky podvodníků</span> a zjistíte, <span class="font-bold">jak se před nimi bránit</span>.</p>
                    <p>Pořadí si zvolíte sami – kliknutím na číslo se otevře nový příběh.</p>
                    <p>Každý vyřešený příběh vám přidá dílek do skládačky.</p>
                    <p class="font-bold mt-5">Co se stane, až složíte všechny?</p>
                </div>

                <p class="text-center mt-8">
                    <a href="{{ route('web.quiz-grid.index') }}"
                       class="lg:text-lg inline-flex items-center px-5 py-2.5 text-sm font-medium text-center text-white bg-blue-700 rounded-lg hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300">
                        Začít hrát hru
                        <icon-arrow-right class="ml-3"></icon-arrow-right>
                    </a>
                </p>
            </div>

            <div class="hidden lg:mt-0 lg:col-span-5 lg:flex">
                <img class="object-contain" src="{{ asset('images/homepage.png') }}" alt="homepage">
            </div>
        </div>
    </section>
@endsection
