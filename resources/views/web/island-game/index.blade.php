@extends('layouts.web')

@section('content')
    <island-scene
        :islands-data='@json($islands)'
        respondent-token="{{ $respondentToken }}"
        situation-url="{{ route('web.island-game.situation') }}"
        answer-url="{{ route('web.island-game.answer') }}"
        :easter-eggs-count="{{ $easterEggsCount }}"
        easter-egg-url="{{ route('web.quiz.easter-egg') }}"
        respondent-easter-egg-url="{{ route('web.quiz.respondent.easter-egg') }}"
        home-url="{{ url('/') }}"
    ></island-scene>
@endsection
