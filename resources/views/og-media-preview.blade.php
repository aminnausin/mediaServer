<!DOCTYPE html>
<html lang="en">

@php
$thumbnail_url = str_replace('http://', 'https://', $thumbnail_url ?? '');
$banner_url = str_replace('http://', 'https://', $banner_url ?? $thumbnail_url);
@endphp


<head>
    <meta charset="UTF-8">
    @vite('resources/css/app.css')
    <title>Open Graph Preview</title>
    <style>

    </style>
</head>

<body class="flex items-center justify-center text-xl">
    <div class="relative flex w-[1200px] h-[630px] overflow-hidden">
        <div class="relative flex-1"></div>

        <img src="{{ $thumbnail_url }}" alt="Cover" class="relative overflow-hidden z-10 object-cover min-w-[40%]" />

        <div class="z-20 absolute h-full -skew-x-3 -left-8 bg-cover bg-center overflow-clip w-[65%]" style="background-image: url('{{ $banner_url ?? $thumbnail_url }}'); backface-visibility: hidden; transform-style: preserve-3d; background-size: 150%;">
            <div class="absolute inset-0 bg-slate-900/50 backdrop-blur-md "></div>
            <div class=" z-30 h-full p-8 px-16 text-white flex flex-col justify-center skew-x-3 ">
                <section class="flex flex-col">
                    <p class="">{{$content_string}}</p>
                    <h2 class="text-4xl font-bold mt-2">{{ $title }}</h2>
                    <p class="text-yellow-400 mt-1 text-2xl">{{ $studio ?? ""}}</p>
                </section>
                <section class="flex flex-col justify-end mt-auto gap-2">
                    <p class="line-clamp-2">{{ $description ?? ""}}</p>
                    <div class="mt-auto flex items-center gap-3">
                        <div class="flex items-center text-4xl font-semibold flex-initial">
                            ⭐ <span class="ml-1">{{$rating ?? 87}}%</span>
                        </div>
                        @foreach (collect($tags ?? [])->take(3) as $tag)
                        <span class="bg-white/10 px-3 py-1 rounded-full ">{{ $tag }}</span>
                        @endforeach

                    </div>
                </section>
            </div>
        </div>

    </div>
</body>

</html>