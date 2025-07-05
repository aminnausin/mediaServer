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

        function setVhUnit() {
            const vh = window.innerHeight * 0.01;
            document.documentElement.style.setProperty('--vh', `${vh}px`)
        }

        setVhUnit();
        window.addEventListener('resize', setVhUnit)
    </script>

    <link rel="manifest" href="/manifest.json">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

    <!-- local -->
    @vite('resources/css/app.css')
    <style>
        /* body {
            scrollbar-gutter: stable;
            font-optical-sizing: auto;
        }

        @media (min-width: 640px) {
            body {
                scrollbar-gutter: stable both-edges;
            }
        }

        body.fullscreen {
            scrollbar-gutter: auto;
        } */

        #nprogress .bar {
            background: #9333ea !important;
        }

        #nprogress-container {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            z-index: 10000;
            pointer-events: none;
        }

        html {
            height: 100%;
        }

        html {
            overflow: hidden;
            height: 100%;
            padding-bottom: 32px;
        }

        .h-dynamic-screen {
            height: calc(var(--vh, 1vh) * 100)
        }
    </style>
</head>

<body class="relative h-dynamic-screen overflow-y-auto bg-primary-900 dark:bg-primary-dark-900 sm:bg-primary-950 sm:dark:bg-primary-dark-950 dark:text-white text-gray-900 antialiased dark:[color-scheme:dark] scrollbar-minimal scrollbar-track:bg-neutral-300 scrollbar-track:dark:bg-neutral-800" id="root"> <!-- dark:bg-[#121216] dark:text-[#e2e0e2] text-gray-900 -->
    <div
        id="reverb-config"
        data-reverb-config='@json(["key" => config("reverb.apps.apps.0.key"), "host" => config("reverb.apps.apps.0.options.host"), "port" => config("reverb.apps.apps.0.options.port")])'>
    </div>
    @vite('resources/js/app.ts')
    <div id='app' class=""></div>
    <div id="nprogress-container" class="h-2"></div>

    {{ $slot }}
</body>

</html>
