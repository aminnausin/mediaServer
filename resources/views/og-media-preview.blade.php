<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    @vite('resources/css/app.css')
    <title>Open Graph Preview</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:ital,opsz,wght@0,9..40,100..1000;1,9..40,100..1000&family=Quicksand:wght@300..700&family=Rubik:ital,wght@0,300..900;1,300..900&display=swap" rel="stylesheet">
</head>

<body class="flex items-center justify-center text-xl text-white">
    <section class="relative flex w-300 h-157.5 overflow-hidden">
        <!-- Banner + Overlay Z-10 (Covers Thumbnail) -->
        <div class="z-10 absolute h-full -skew-x-3 -left-8 overflow-clip w-[65%]">
            <div class="scale-115 bg-cover bg-center blur-md" style="background-image: url('{{ $banner_url ?? $thumbnail_url }}');  backface-visibility: hidden; transform-style: preserve-3d; background-size: 130%;">
                <div class="bg-slate-900/50 w-screen h-screen"></div>
            </div>
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
                        <svg xmlns="http://www.w3.org/2000/svg" width="1.2em" height="1.2em" viewBox="0 0 24 24" class="text-yellow-400">
                            <path fill="currentColor" fill-rule="evenodd" d="M10.788 3.21c.448-1.077 1.976-1.077 2.424 0l2.082 5.006l5.404.434c1.164.093 1.636 1.545.749 2.305l-4.117 3.527l1.257 5.273c.271 1.136-.964 2.033-1.96 1.425L12 18.354L7.373 21.18c-.996.608-2.231-.29-1.96-1.425l1.257-5.273l-4.117-3.527c-.887-.76-.415-2.212.749-2.305l5.404-.434z" clip-rule="evenodd" />
                        </svg>
                        <span class="ml-1 font-quicksand">{{$rating ?? 0}}%</span>
                    </div>
                    @endif
                    <p class="line-clamp-2 drop-shadow-lg whitespace-pre-wrap flex-1">{{ $description ?? ""}}</p>
                </span>

                <div class="flex items-center gap-3">
                    <div class="flex overflow-hidden w-full gap-3 flex-wrap h-9 [overflow-clip-margin:4px]">
                        @foreach (collect($tags ?? ['no tags yet'])->take(5) as $tag)
                        <span class="bg-white/10 px-3 py-1 rounded-full text-nowrap">{{ $tag }}</span>
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
