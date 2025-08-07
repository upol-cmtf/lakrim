@extends('layouts.web')

@section('content')
    <div class="flex flex-col items-center p-2">

        <h1 class="max-w-2xl mb-8 text-4xl font-extrabold leading-none tracking-tight md:text-5xl xl:text-5xl text-center">
            Vítejte! Jsme rádi, že jste tu.
        </h1>

        <questions-tiles
                completion-url="{{ route('web.quiz.finish') }}"
                respondent-token="{{ $respondentToken }}"
                :max-tiles="{{ $maxTiles }}"
        >
        </questions-tiles>
    </div>
@endsection
