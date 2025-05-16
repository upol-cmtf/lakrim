@extends('layouts.web')

@section('content')
    <div class="flex flex-col items-center p-2 mt-10">
{{--        <h1 class="text-xl md:text-2xl md:font-semibold mb-10">Nová hra</h1>--}}

        <h1 class="max-w-2xl mb-8 text-4xl font-extrabold leading-none tracking-tight md:text-5xl xl:text-5xl text-center">
            Vítejte! Jsme rádi, že jste tu.
        </h1>

        <livewire:web.revealing-image/>
    </div>
@endsection
