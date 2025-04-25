<!DOCTYPE html>
<html lang="en">

@php
$thumbnail_url = str_replace('http://', 'https://', $thumbnail_url ?? '');
$raw = str_replace('http://', 'https://', $raw ?? '');
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
    <meta property="og:image:width" content="1200">
    <meta property="og:image:height" content="630">
    <meta property="og:url" content="{{ $url }}">
    <meta property="og:site_name" content="Media Server">

    <meta name="twitter:card" content="summary_large_image">

    <!-- <meta property="og:image" content="https://img.anili.st/media/176301" data-vue-meta="true"> -->
    @vite('resources/css/app.css')
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
    <img src="{{ $raw ?: $thumbnail_url }}" width="1200" class="rounded-xl overflow-clip" />
    @if (app()->environment('local'))
    <pre>{{ json_encode(get_defined_vars(), JSON_PRETTY_PRINT) }}</pre>
    @endif
</body>

</html>