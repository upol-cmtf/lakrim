@extends('layouts.web')

@section('content')
    <island-scene
        :islands-data='@json($islands)'
        respondent-token="{{ $respondentToken }}"
        situation-url="{{ route('web.island-game.situation') }}"
        answer-url="{{ route('web.island-game.answer') }}"
    ></island-scene>
@endsection
