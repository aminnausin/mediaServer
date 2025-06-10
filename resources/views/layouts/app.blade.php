<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="A self hosted web media server to keep everything in one place.">
    <meta property="og:title" content="{{ config('app.name', 'Media Server') }}" />

    <title>{{ config('app.name', 'Media Server') }}</title>

    <script defer data-domain="{{ config('app.host') }}" src="{{ config('services.plausible.url') }}"></script>
    <script>
        window.plausible = window.plausible || function() {
            (window.plausible.q = window.plausible.q || []).push(arguments)
        }
    </script>

    <!-- local -->
    @vite('resources/css/app.css')
    <style>
        #app {
            scrollbar-gutter: stable;
            font-optical-sizing: auto;
        }

        @media (min-width: 640px) {
            #app {
                scrollbar-gutter: stable both-edges;
            }
        }

        #app.fullscreen {
            scrollbar-gutter: auto;
        }

        #nprogress .bar {
            background: #9333ea !important;
        }
    </style>
</head>

<body class="bg-primary-900 dark:bg-primary-dark-900 sm:bg-primary-950 sm:dark:bg-primary-dark-950 dark:text-white text-gray-900 antialiased dark:[color-scheme:dark]" id="root"> <!-- dark:bg-[#121216] dark:text-[#e2e0e2] text-gray-900 -->
    <div
        id="reverb-config"
        data-reverb-config='@json(["key" => config("reverb.apps.apps.0.key"), "host" => config("reverb.apps.apps.0.options.host"), "port" => config("reverb.apps.apps.0.options.port")])'>
    </div>
    @vite('resources/js/app.ts')
    <div id='app' class="h-screen overflow-auto scrollbar-minimal scrollbar-track:bg-neutral-300 scrollbar-track:dark:bg-neutral-800"></div>
    {{ $slot }}
</body>

</html>