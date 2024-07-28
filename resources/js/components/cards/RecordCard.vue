<script setup>
    import { toTimeSpan } from '../../service/util';

    const { record } = defineProps(['record']);
    const timeSpan = toTimeSpan((new Date(record.attributes.created_at.replace(' ', 'T'))))
</script>

<template>
    <div class="p-2 flex flex-wrap rounded-xl dark:bg-primary-dark-800 bg-primary-800 dark:text-white shadow-md w-full divide-y divide-gray-300 group">
        <section class="flex justify-between items-baseline w-full">
            <h2 class="text-xl text-left truncate">{{ record.relationships.video_name }}</h2>
        </section>
        <aside class="flex justify-between items-center w-full pt-1">
            <h3 class="text-left text-neutral-500 truncate">{{ record.relationships.folder_name }}</h3>
                <h3 class="text-right text-neutral-500 text-nowrap line-clamp-1 truncate">{{ timeSpan }}</h3>
                <span class="hidden space-x-1">
                    <button class="hover:bg-orange-500 hover:stroke-none border-orange-500 border-2 rounded shadow px-2">Watch</button>
                </span>
        </aside>
    </div>
</template>