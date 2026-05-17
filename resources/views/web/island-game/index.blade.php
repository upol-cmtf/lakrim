@extends('layouts.web')

@section('content')
    <island-scene
        :islands-data='@json($islands)'
        respondent-token="{{ $respondentToken }}"
    ></island-scene>
@endsection
