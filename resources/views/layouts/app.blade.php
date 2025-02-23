<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="A self hosted web media server to keep everything in one place.">

    <title>{{ config('app.name', 'Media Server') }}</title>

    <!-- local -->
    @vite('resources/css/app.css')
    <style>
        html {
            scrollbar-gutter: stable;
        }

        html.fullscreen {
            scrollbar-gutter: auto;
        }
    </style>
</head>

<body class="bg-primary-950 dark:bg-primary-dark-950 dark:text-[#e2e0e2] font-sans text-gray-900 antialiased dark:[color-scheme:dark]" id="root"> <!-- dark:bg-[#121216] dark:text-[#e2e0e2] text-gray-900 -->
    <div id="reverb-config" data-reverb-config='
    @json(["key" => env("REVERB_APP_KEY"), "host" => env("REVERB_HOST"), "port" => env("REVERB_PORT")])'></div>

    @vite('resources/js/app.ts')
    <div id='app'></div>
    {{ $slot }}
</body>

</html>