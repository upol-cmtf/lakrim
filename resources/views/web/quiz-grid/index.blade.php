@extends('layouts.web')

@section('content')
    <questions-tiles
            class="mb-5"
            completion-url="{{ route('web.quiz.finish') }}"
            respondent-token="{{ $respondentToken }}"
            :max-tiles="{{ $maxTiles }}"
    >
    </questions-tiles>
@endsection
