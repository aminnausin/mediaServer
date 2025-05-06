<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    @vite('resources/css/app.css')
    <title>Open Graph Preview</title>
    <style>

    </style>
</head>

<body class="flex items-center justify-center text-xl text-white">
    <section class="relative flex w-[1200px] h-[630px] overflow-hidden bg-cover bg-left" style="background-image: url('{{ $banner_url ?? $thumbnail_url }}'); backface-visibility: hidden; transform-style: preserve-3d; background-size: 80%;">
        <!-- Banner + Overlay Z-10 (Covers Thumbnail) -->
        <div class="z-10 absolute h-full -skew-x-3 -left-8 overflow-clip w-[65%] bg-cover bg-center" style="background-image: url('{{ $banner_url ?? $thumbnail_url }}'); backface-visibility: hidden; transform-style: preserve-3d; background-size: 150%;">
            <div class="w-screen h-screen bg-slate-900/50 backdrop-blur-md"></div>
        </div>
        <!-- Text (LEFT) Z-20 (Covers Everything) -->
        <div class="z-20 flex flex-col flex-1 w-3/5 justify-center p-8">
            <div class="flex flex-col">
                <p class="drop-shadow-lg">{{$content_string}}</p>
                <h2 class="text-4xl font-bold mt-2 drop-shadow-lg">{{ $title }}</h2>
                <p class="text-yellow-400 mt-2 text-2xl drop-shadow-lg">{{ $studio ?? ""}}</p>
            </div>
            <div class="flex flex-col justify-end mt-auto gap-6">
                <span class="flex items-center gap-6">
                    @if ($rating ?? false)
                    <div class="flex items-center text-4xl font-semibold">
                        ⭐ <span class="ml-1">{{$rating ?? 0}}%</span>
                    </div>
                    @endif
                    <p class="line-clamp-2 drop-shadow-lg whitespace-pre-wrap flex-1">{{ $description ?? ""}}</p>
                </span>

                <div class="flex items-center gap-3">
                    <div class="flex overflow-hidden w-full gap-3">
                        @foreach (collect($tags ?? ['no tags yet'])->take(5) as $tag)
                        <span class="bg-white/10 px-3 py-1 rounded-full ">{{ $tag }}</span>
                        @endforeach
                    </div>
                    @if ($upload_date ?? false)
                    <span class="ml-auto py-1 flex-1 text-end min-w-fit">{{ $upload_date}}</span>
                    @endif
                </div>
            </div>
        </div>
        <!-- Thumbnail (RIGHT) -->
        <img src="{{ $thumbnail_url }}" alt="Cover" class="relative overflow-hidden object-cover w-2/5" />
    </section>
</body>

</html>