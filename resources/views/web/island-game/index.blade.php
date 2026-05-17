@extends('layouts.web')

@section('content')
    <island-scene :islands-data='@json($islands)'></island-scene>
@endsection
