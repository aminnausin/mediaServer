<script setup>
    import { toTimeSpan } from '../service/util';

    const { record } = defineProps(['record']);
    const rawDate = new Date(record.attributes.created_at.replace(' ', 'T'));
    const timeSpan = toTimeSpan(rawDate);
</script>

<template>
    <div class="flex gap-12 group cursor-default">
        <div class="flex rounded-xl items-center justify-center dark:bg-primary-dark-800 bg-primary-800 dark:hover:bg-neutral-700 hover:bg-slate-200 dark:text-white shadow p-3 w-full  group flex-grow cursor-pointer">
            <div class="w-full flex-wrap divide-y divide-gray-300">
                <section class="flex justify-between items-baseline w-full">
                    <h2 class="text-xl text-left truncate">{{ record.relationships.video_name }}</h2>
                    <h2 class="text-xl text-right truncate">{{ `${rawDate.toLocaleDateString('en-ca', {year: "numeric", month: '2-digit', day: '2-digit', hour: '2-digit', hour12:false, minute:'2-digit'})}` }}</h2>
                </section>
                <aside class="flex justify-between items-center w-full pt-1">
                    <h3 class="text-lg text-left text-neutral-500 truncate">{{ record.relationships.folder_name }}</h3>
                    <h3 class="text-lg text-right text-neutral-500 text-nowrap line-clamp-1 truncate">{{ timeSpan }}</h3>
                    <span class="hidden space-x-1">
                        <button class="hover:bg-orange-500 hover:stroke-none border-orange-500 border-2 rounded shadow px-2">Watch</button>
                        <svg xmlns="https://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 13.5 12 21m0 0-7.5-7.5M12 21V3" />
                        </svg>
                    </span>
                </aside>
            </div>
        </div>
        <div class="aspect-square rounded-full items-center justify-center group-hover:visible invisible flex">
            <button class="record-delete w-full p-3 items-center justify-center rounded-xl bg-red-700 hover:bg-red-600" :data-id={record} @click="$emit('deleteRecord')">
                Delete
            </button>
        </div>
    </div>
</template>