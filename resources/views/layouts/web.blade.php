<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'Laravel') }}</title>
    @vite(['resources/sass/web/app.scss', 'resources/js/web/app.js'])
</head>
<body id="app-web" class="flex flex-col justify-between min-h-screen">
    @include('web.partials.header')

    <main class="mb-auto flex-auto">
        @yield('content')
    </main>

    @include('web.partials.footer')
</html>
