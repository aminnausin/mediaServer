<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    @vite('resources/css/app.css')
    <title>Open Graph Preview</title>
    <style>

    </style>
</head>

<body class="flex items-center justify-center">
    <div class="relative flex w-[1200px] h-[630px] overflow-hidden">
        <div class="relative flex-1"></div>

        <img src="{{ $thumbnail_url }}" alt="Cover" class="relative overflow-hidden z-10 object-cover min-w-[40%]" />

        <div class="z-20 absolute h-full -skew-x-3 -left-8 bg-cover bg-center overflow-clip min-w-[65%]" style="background-image: url('{{ $banner_url ?? $thumbnail_url }}'); backface-visibility: hidden; transform-style: preserve-3d; background-size: 150%;">
            <div class="absolute inset-0 bg-black/50 backdrop-blur-md"></div>
            <div class=" z-30 h-full p-8 px-16 text-white flex flex-col justify-center skew-x-3 ">
                <p class="text-sm">{{$date_start ? "$date_start • " : ''}}{{$file_count ?? 0}} {{($is_audio ?? false) ? "track" : "episode"}}{{($file_count ?? 0 ) === 1 ? '' : 's'}}</p>
                <h2 class="text-3xl font-bold mt-2">{{ $title }}</h2>
                <p class="text-yellow-400 mt-1 text-lg">{{ $studio ?? ""}}</p>
                <div class="mt-6 flex items-center gap-3">
                    <div class="flex items-center text-lg font-semibold">
                        ⭐ <span class="ml-1">{{$rating ?? 87}}%</span>
                    </div>
                    @foreach (collect($tags)->take(2) as $tag)
                    <span class="bg-white/10 px-3 py-1 rounded-full text-sm">{{ $tag }}</span>
                    @endforeach
                </div>
            </div>
        </div>

    </div>
</body>

</html>