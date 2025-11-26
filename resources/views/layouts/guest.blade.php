<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans text-gray-900 antialiased">
        <div class="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0 bg-gray-100">
            <div>
                <a href="/">
                    <x-brand-logo class="w-20 h-20" />
                </a>
            </div>

            <div class="w-full sm:max-w-md mt-6 px-6 py-4 bg-white shadow-md overflow-hidden sm:rounded-lg">
                {{ $slot }}
            </div>
        </div>
    </body>
    <script>
        (function(){
            var key = 'scroll:'+location.pathname;
            function save(){try{sessionStorage.setItem(key, String(window.scrollY||0));}catch(e){}}
            function restore(){try{var y=parseInt(sessionStorage.getItem(key)||'0',10);if(!isNaN(y)&&y>0){window.scrollTo(0,y);}}catch(e){}}
            document.addEventListener('DOMContentLoaded', restore);
            window.addEventListener('beforeunload', save);
            document.addEventListener('submit', save, true);
            document.addEventListener('click', function(e){var t=e.target; if(t && t.closest('a,button,[type="submit"],input[type="submit"]')) save();}, true);
        })();
    </script>
</html>
