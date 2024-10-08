@extends('layouts.web')

@section('content')
    <div class="container py-10 px-10 mx-0 min-w-full flex flex-col items-center">
        <h2 class="text-5xl mb-3 text-black">Lakrim</h2>
        <p class="text-black">Kickstart your career in BioPharma with the Mendeleev Institute right now</p>
        <a href="{{ route('web.quiz.run') }}" class="bg-sky-500 hover:bg-sky-700 px-5 py-2 text-sm leading-5 rounded-full font-semibold text-white">
            Spustit test <icon-arrow-right/>
        </a>

        <quiz></quiz>
    </div>
@endsection
