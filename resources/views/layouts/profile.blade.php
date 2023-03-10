<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ $name->toHtml() }} | {{ config('app.name') }} </title>

        <!-- Fonts -->
        <link rel="stylesheet" href="https://fonts.bunny.net/css2?family=Nunito:wght@400;600;700&display=swap">

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])

        <!-- Styles -->
    </head>
    <body class="font-sans antialiased">
        @if(Auth::check())
            @include('layouts.navigation')
        @endif
        <div class="min-h-screen bg-gray-100 dark:bg-gray-900 z-0">
            <!-- Page Heading -->
            <header class="md:fixed md:top-0 md:left-0 md:w-80 md:min-h-screen bg-white dark:bg-gray-800 shadow-lg">
                @if (isset($header))
                <div class="max-w-7xl md:mx-auto py-6 px-4 sm:px-6 lg:px-8">
                    {{ $header }}
                </div>
                @endif
            </header>

            <!-- Page Content -->
            <main class="md:ml-80">
                {{ $slot }}
            </main>
        </div>
    </body>
</html>
