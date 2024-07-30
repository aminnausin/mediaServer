<script setup>
import ButtonCorner from '../inputs/ButtonCorner.vue';

import CircumLogin from '~icons/circum/login';
import CircumMaximize1 from '~icons/circum/maximize-1';
import CircumPlay1 from '~icons/circum/play-1';

import { toTimeSpan } from '../../service/util';
import { computed } from 'vue';

const { record } = defineProps(['record']);

const rawDate = new Date(record.attributes.created_at.replace(' ', 'T'));
const timeSpan = toTimeSpan(rawDate);

const videoLink = computed(() => {
    return (`/${encodeURIComponent(record.relationships.category_name)}/${encodeURIComponent(record.relationships.folder_name)}?video=${record.relationships.video_id}`);
})

</script>

<template>
    <div class="flex gap-12 group cursor-default">
        <div
            class="hidden flex rounded-xl items-center justify-center dark:bg-primary-dark-800 bg-primary-800 dark:hover:bg-neutral-700 hover:bg-neutral-200 dark:text-white shadow p-3 w-full  group flex-grow cursor-pointer">
            <div class="w-full flex-wrap divide-y divide-gray-300">
                <section class="flex justify-between items-baseline w-full">
                    <h2 class="text-xl text-left truncate">{{ record.relationships.video_name }}</h2>
                    <h2 class="text-xl text-right truncate">{{ `${rawDate.toLocaleDateString('en-ca', {
                        year: "numeric",
                        month: '2-digit', day: '2-digit', hour: '2-digit', hour12: false, minute:'2-digit'})}` }}</h2>
                </section>
                <aside class="flex justify-between items-center w-full pt-1">
                    <h3 class="text-lg text-left text-neutral-500 truncate">{{ record.relationships.folder_name }}</h3>
                    <h3 class="text-lg text-right text-neutral-500 text-nowrap line-clamp-1 truncate">{{ timeSpan }}
                    </h3>
                    <span class="hidden space-x-1">
                        <button
                            class="hover:bg-orange-500 hover:stroke-none border-orange-500 border-2 rounded shadow px-2">Watch</button>
                    </span>
                </aside>
            </div>
        </div>
        <div class="hidden aspect-square rounded-full items-center justify-center group-hover:visible invisible flex">
            <button class="record-delete w-full p-3 items-center justify-center rounded-xl bg-red-700 hover:bg-red-600"
                :data-id="record" @click="$emit('deleteRecord')">
                Delete
            </button>
        </div>
        <div class="relative flex flex-col gap-2 sm:flex-row flex-wrap rounded-xl dark:bg-primary-dark-800 bg-primary-800 dark:hover:bg-neutral-700 hover:bg-slate-200 dark:text-white shadow p-3 w-full group cursor-pointer sm:divide-y divide-gray-300 dark:divide-gray-400">
            <section class="flex justify-between gap-4 w-full">
                <h2 class="text-xl w-full truncate">{{ record.relationships.video_name }}</h2>
                <div class="flex justify-end gap-1">
                    <ButtonCorner :positionClasses="'w-7 h-7'" :textClasses="'hover:text-indigo-600 dark:hover:text-indigo-300'" :colourClasses="'dark:hover:bg-neutral-800 hover:bg-slate-300'" :to="videoLink">
                        <template #icon>
                            <CircumPlay1 width="20" height="20" fill="none" viewBox="0 0 24 24"/>
                        </template>
                    </ButtonCorner>
                    <ButtonCorner :positionClasses="'w-7 h-7'" :textClasses="'text-rose-700'"  :colourClasses="'dark:hover:bg-neutral-800 hover:bg-slate-300'" @click="$emit('deleteRecord')"/> 
                </div>
            </section>
            <section class="flex flex-col sm:flex-row sm:justify-between w-full pt-2">
                <h3 class="text-lg text-neutral-500 w-full text-wrap truncate">{{ record.relationships.folder_name }} · {{ timeSpan }}</h3>
                <h3 class="text-lg truncate sm:text-right text-neutral-500 w-full line-clamp-2">{{ `${rawDate.toLocaleDateString('en-ca', {
                    year: "numeric",
                    month: '2-digit', day: '2-digit', hour: '2-digit', hour12: false, minute:'2-digit'})}` }}</h3>
            </section>
        </div>
    </div>
</template>