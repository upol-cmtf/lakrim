@extends('layouts.web')

@section('content')
    <div class="container mx-auto">
        <quiz-form token="{{ $respondentToken }}"
                   :settings="{{ json_encode($settings) }}">
        </quiz-form>
    </div>
@endsection
