<script setup>
    const { record } = defineProps(['record']);
</script>

<template>
    <div class="p-2 flex flex-wrap rounded-xl bg-neutral-100 dark:bg-neutral-800 dark:text-white shadow-md w-full divide-y divide-gray-300 group">
        <section class="flex justify-between items-baseline w-full">
            <h2 class="text-xl text-left truncate">{{ record.videoName}}</h2>
        </section>
        <aside class="flex justify-between items-center w-full pt-1">
            <h3 class="text-lg text-left text-neutral-500 truncate">{{ record.folderName}}</h3>
                <h3 class="text-lg text-right text-neutral-500 text-nowrap line-clamp-1 truncate">{{ record.timeSpan}}</h3>
                <span class="hidden space-x-1">
                    <button class="hover:bg-orange-500 hover:stroke-none border-orange-500 border-2 rounded shadow px-2">Watch</button>
                    <svg xmlns="https://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 13.5 12 21m0 0-7.5-7.5M12 21V3" />
                    </svg>
                </span>
        </aside>
    </div>
</template>