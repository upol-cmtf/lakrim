<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>

    <!-- Scripts -->
    @vite(['resources/sass/web/app.scss', 'resources/js/web/app.js'],'build/web')
</head>
<body class="flex flex-col min-h-screen">
<div id="app-web">
    @include('web.partials.header')

    <main class="flex-auto">
        @yield('content')
    </main>

    @include('web.partials.footer')
</div>
</html>
