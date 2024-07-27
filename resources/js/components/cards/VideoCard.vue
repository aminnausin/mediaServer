<script setup>
import { toFormattedDate } from '../../service/util';


const props = defineProps(['video', 'index', 'currentID']);
const rawDate = new Date(props.video.attributes.date + ' GMT');

const emit = defineEmits(['playByID']);
const handlePlay = () => {
    // console.log(`handlePlay ${currentID} ${video.id}`);
    // if(currentID.id !== video.id) emit('playByID', video.id);
    emit('playByID', props.video.id);
}
</script>

<template>
    <tr :class="{ 'ring-violet-600/70 ring-[0.125rem]': (props.currentID === props.video.id) }"
        class="shadow vid-row w-full rounded-md dark:bg-neutral-800 dark:hover:bg-indigo-900 bg-gray-100 hover:bg-violet-300 odd:bg-violet-100 hover:odd:bg-violet-300 dark:odd:bg-zinc-700 dark:hover:odd:bg-indigo-900 p-3 flex ring-inset flex-wrap flex-col sm:flex-row sm:flex-nowrap"
        :data-id="props.video.id" :data-path="`../${props.video.attributes.path}`" @click="handlePlay">
        <td class="flex gap-12 w-full">
            <p class="vid-row-title w-full xl:min-w-48 sm:max-w-1/2 sm:w-auto flex justify-between items-center gap-12 sm:gap-0"><span class="sm:hidden">Title: </span><span class="line-clamp-1">{{ props.video.attributes.name }}</span></p>
            <p class="vid-row-duration truncate hidden sm:block">20m 30s</p>
        </td>
        <td class="flex gap-12 w-full sm:justify-end">
            <p class="vid-row-title truncate hidden sm:block">20 Views</p>
            <p class="vid-row-date w-full xl:min-w-36 sm:max-w-1/3 sm:w-auto flex justify-between items-center gap-12 sm:gap-0"><span class="sm:hidden">Date: </span><span class="line-clamp-1">{{ toFormattedDate(rawDate) }}</span></p>
        </td>
    </tr>
</template>