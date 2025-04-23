<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>{{ $title }}</title>

    @if($isAudio)
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
    <meta property="og:image" content="{{ $secure_url }}">
    <meta property="og:url" content="{{ $url }}">


    <meta property="og:site_name" content="{{ config('app.name', 'Media Server') }}">

    <!-- <meta property="og:image" content="{{ asset('storage/thumbnails/default.webp') }}" /> -->
    <!-- <meta property="og:image" content="https://img.anili.st/media/176301" data-vue-meta="true"> -->
</head>

<body style="flex-direction: column; display: flex;">
    <h1>{{ $title }}</h1>
    <p>{{ $description }}</p>
    @if($isAudio ?? false)
    <p>🎵 Audio Content</p>
    @else
    <p>🎥 Video Content</p>
    @endif
    <img src="{{ $secure_url }}" width="200" />
</body>

</html>