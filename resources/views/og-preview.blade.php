<!DOCTYPE html>
<html lang="en">

@php
$thumbnail_url = str_replace('http://', config('app.scheme'). '://', $thumbnail_url ?? '');
$raw = str_replace('http://', config('app.scheme'). '://', $raw ?? '');

function highlight_json($json) {
$json = e($json); // escape for HTML safety

// Highlight keys
$json = preg_replace('/(&quot;[^&]+&quot;)(\s*:\s*)/', '<span class="text-blue-400">$1</span>$2', $json);
// Highlight string values
$json = preg_replace('/:\s*(&quot;[^&]+&quot;)/', ': <span class="text-orange-400">$1</span>', $json);
// Highlight numbers
$json = preg_replace('/:\s*(\d+)/', ': <span class="text-green-400">$1</span>', $json);
// Highlight booleans
$json = preg_replace('/\b(true|false)\b/', '<span class="text-pink-400">$1</span>', $json);
// Highlight null
$json = preg_replace('/\bnull\b/', '<span class="text-gray-400">null</span>', $json);
// Highlight brackets
$json = preg_replace('/([{}\[\]\(\)])/','<span class="text-yellow-400">$1</span>', $json);

return $json;
}
@endphp

<head>
    <meta charset="UTF-8">
    <title>{{ $title }}</title>

    @if($is_audio ?? false)
    <meta property="og:type" content="music.song">
    <meta property="og:audio:type" content="{{ $mime_type ?? 'audio/mpegf' }}">
    <meta property="music:duration" content="{{ $duration ?? '' }}">
    @else
    <meta property="og:type" content="video.other">
    <meta property="og:video:type" content="{{ $mime_type ?? 'video/mp4' }}">
    <meta property="og:video:duration" content="{{ $duration ?? '' }}">
    <meta property="og:video:release_date" content="{{ $release_date ?? '' }}">
    @endif

    <meta property="og:title" content="{{ $title }}">
    <meta property="og:description" content="{{ $description }}">
    <meta property="og:image" content="{{ $thumbnail_url ?? asset('storage/thumbnails/default.webp') }}">
    <meta property="og:url" content="{{ $url }}">
    <meta property="og:site_name" content="Media Server">

    @if ($is_generated ?? false)
    <meta property="og:image:width" content="1200">
    <meta property="og:image:height" content="630">
    <meta name="twitter:card" content="summary_large_image">
    @endif

    @vite('resources/css/app.css')
    <style>
        html {
            scrollbar-gutter: stable;
        }

        html.fullscreen {
            scrollbar-gutter: auto;
        }
    </style>

    <link rel="manifest" href="/manifest.json" crossorigin="use-credentials">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:ital,opsz,wght@0,9..40,100..1000;1,9..40,100..1000&family=Quicksand:wght@300..700&family=Rubik:ital,wght@0,300..900;1,300..900&display=swap" rel="stylesheet">
</head>

<body class="flex flex-col gap-2 text-sm p-4">
    <section>
        <h1 class="text-xl">{{ $title }}</h1>
        <p>{{ $description }}</p>
    </section>
    @if($is_audio ?? false)
    <p>🎵 Audio Content</p>
    @else
    <p>🎥 Video Content</p>
    @endif
    @if($updated_at ?? false)
    <p>{{$updated_at}}</p>
    @endif
    <img src="{{ $raw ?: $thumbnail_url }}" width="1200" class="rounded-xl overflow-clip" alt="open graph preview" />
    @if (app()->environment('local'))
    <pre class="max-w-full whitespace-pre-wrap bg-neutral-900 text-white p-2 overflow-x-auto text-sm leading-tight">
{!! highlight_json(json_encode(get_defined_vars(), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)) !!}
    </pre>
    @endif
</body>

</html>
