<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>

    <!-- Scripts -->
    @livewireStyles
    @vite(['resources/sass/web/app.scss', 'resources/js/web/app.js'])
    @livewireScripts
</head>
<body id="app-web" class="flex flex-col min-h-screen h-screen justify-between">
    @include('web.partials.header')

    <main class="mb-auto flex-auto">
        @yield('content')
    </main>

    @include('web.partials.footer')
</html>
