@extends('layouts.web')

@section('content')
    <div class="container mx-auto">
        <quiz-form completion-url="{{ route('web.quiz.finish') }}"
                   token="{{ $respondentToken }}"
                   :settings="{{ json_encode($settings) }}">
        </quiz-form>
    </div>
@endsection
