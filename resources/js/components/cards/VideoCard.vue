<script setup>
import useMetaData from '../../composables/useMetaData';

import { toFormattedDate } from '@/service/util';
import { watch } from 'vue';


const props = defineProps(['video', 'index', 'currentID']);
const metaData = useMetaData(props.video?.attributes);
const emit = defineEmits(['playByID']);
const handlePlay = () => {
    emit('playByID', props.video?.id);
}

const handlePropsUpdate = () => {
    metaData.updateData(props.video?.attributes);
}

watch(props, handlePropsUpdate, {immediate: true});
</script>

<template>
    <div :class="{ 'ring-violet-600/70 ring-[0.125rem]': (props.currentID === props.video?.id) }"
        class="flex flex-wrap flex-col sm:flex-row sm:flex-nowrap gap-x-8 gap-y-4 p-3 w-full shadow rounded-md ring-inset dark:bg-neutral-800 dark:hover:bg-indigo-900 bg-gray-100 hover:bg-violet-300 odd:bg-violet-100 hover:odd:bg-violet-300 dark:odd:bg-zinc-700 dark:hover:odd:bg-indigo-900"
        :data-id="props.video?.id" :data-path="`../${props.video?.attributes.path}`" @click="handlePlay">
        <section class="flex justify-between gap-4 w-full items-start sm:items-center">
            <h3 class="w-full line-clamp-2 sm:line-clamp-1">
                {{ metaData?.fields?.title }}
            </h3>
            <h3 class="text-sm sm:text-base flex justify-end text-nowrap dark:text-neutral-300 text-neutral-500">
                {{ metaData?.fields?.views }}
            </h3>
        </section>
        <section class="text-sm sm:text-base flex justify-between sm:w-auto items-center dark:text-neutral-300 text-neutral-500 gap-8">
            <h3 class="text-nowrap">
                {{ metaData?.fields?.duration }} 
            </h3>
            <h3 class="line-clamp-1 sm:min-w-40">
                {{ toFormattedDate(new Date(props.video?.attributes.date + ' GMT')) }}
            </h3>
        </section>
        
        <!-- <td class="flex gap-12 w-full">
            <p class="vid-row-title w-full xl:min-w-48 sm:max-w-1/2 sm:w-auto flex justify-between items-center gap-12 sm:gap-0"><span class="sm:hidden">Title: </span><span class="line-clamp-1">{{ metaData?.fields?.title }}</span></p>
            <p class="vid-row-duration truncate hidden sm:block">{{ metaData?.fields?.duration }}</p>
        </td>
        <td class="flex gap-12 w-full sm:justify-end">
            <p class="vid-row-title truncate hidden sm:block">{{ metaData?.fields?.views }}</p>
            <p class="vid-row-date w-full xl:min-w-36 sm:max-w-1/3 sm:w-auto flex justify-between items-center gap-12 sm:gap-0"><span class="sm:hidden">Date: </span><span class="line-clamp-1">{{ toFormattedDate(new Date(props.video?.attributes.date + ' GMT')) }}</span></p>
        </td> -->
    </div>
</template>