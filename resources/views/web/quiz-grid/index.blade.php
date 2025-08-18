@extends('layouts.web')

@section('content')
    <div class="flex flex-col items-center p-2">

        <h1 class="max-w-4xl mb-3 text-2xl font-bold leading-8 tracking-tight text-center mt-5">
            Za každým číslem se skrývá příběh a každý příběh odhalí dílek skládačky.<br/>Co se stane, až je odhalíte všechny?
        </h1>

        <questions-tiles
                class="mb-5"
                completion-url="{{ route('web.quiz.finish') }}"
                respondent-token="{{ $respondentToken }}"
                :max-tiles="{{ $maxTiles }}"
        >
        </questions-tiles>
    </div>
@endsection
