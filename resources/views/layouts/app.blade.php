<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="description" content="A self hosted web media server to keep everything in one place.">

        <title>{{ config('app.name', 'Media Server') }}</title>

        <!-- local -->
        @vite('resources/css/app.css')
        <script>
            toastr.options = {
                "closeButton": true,
                "debug": false,
                "newestOnTop": false,
                "progressBar": true,
                "positionClass": "toast-bottom-left",
                "preventDuplicates": false,
                "onclick": null,
                "showDuration": "300",
                "hideDuration": "1000",
                "timeOut": "5000",
                "extendedTimeOut": "1000",
                "showEasing": "swing",
                "hideEasing": "linear",
                "showMethod": "fadeIn",
                "hideMethod": "fadeOut"
            }
        </script>
    </head>

    <body class="bg-primary-950 dark:bg-primary-dark-950 dark:text-[#e2e0e2] font-sans text-gray-900 antialiased" id="root"> <!-- dark:bg-[#121216] dark:text-[#e2e0e2] text-gray-900 -->
        @vite('resources/js/app.js')
        <div id='app'></div>
        {{ $slot }}
    </body>
</html>