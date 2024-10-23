@extends('layouts.web')

@section('content')
    <section class="bg-white dark:bg-gray-900">
        <div class="grid max-w-screen-xl px-4 pt-20 pb-8 mx-auto lg:gap-8 xl:gap-0 lg:py-16 lg:grid-cols-12 lg:pt-28">
            <div class="mr-auto place-self-center lg:col-span-7 md:text-lg lg:text-xl">
                <h1 class="max-w-2xl mb-8 text-4xl font-extrabold leading-none tracking-tight md:text-5xl xl:text-5xl dark:text-white">
                    {{ __('homepage.title') }}
                </h1>

                <p class="max-w-2xl mb-6 lg:mb-8">{!! __('homepage.description') !!}</p>

                <p class="font-bold">{{ __('homepage.what_will_you_learn.title') }}</p>

                <ul class="list-disc ml-8 mt-3">
                    <li>{{ __('homepage.what_will_you_learn.list_1') }}</li>
                    <li>{{ __('homepage.what_will_you_learn.list_2') }}</li>
                    <li>{{ __('homepage.what_will_you_learn.list_3') }}</li>
                    <li>{{ __('homepage.what_will_you_learn.list_4') }}</li>
                </ul>

                <p class="mt-5">{{ __('homepage.everything_is_done_anonymously') }}</p>

                <p class="text-center mt-8">
                    <a href="{{ route('web.quiz.run') }}"
                       class="lg:text-lg inline-flex items-center px-5 py-2.5 text-sm font-medium text-center text-white bg-blue-700 rounded-lg hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 dark:bg-blue-600 dark:hover:bg-blue-700 dark:focus:ring-blue-800">
                        {{ __('homepage.start_to_play') }}
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
