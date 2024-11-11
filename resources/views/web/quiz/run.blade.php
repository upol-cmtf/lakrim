@extends('layouts.web')

@section('content')
    <div class="container mx-auto">
        <quiz-form token="{{ $token }}"></quiz-form>
    </div>
@endsection
