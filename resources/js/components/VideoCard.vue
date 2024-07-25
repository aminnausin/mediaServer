<script setup>
    import { toFormattedDate } from '../service/util';
    const { video, index, currentID = -1 } = defineProps(['video', 'index', 'currentID']);
    const rawDate = new Date(video.attributes.date + ' GMT');
    const emit = defineEmits(['playByID']);

    const handlePlay = () => {
        // console.log(`handlePlay ${currentID} ${video.id}`);
        // if(currentID.id !== video.id) emit('playByID', video.id);
        emit('playByID', video.id);
    }
</script>

<template>
    <tr :class="{ 'ring-violet-600/50 ring': (currentID === video.id)}" class="vid-row w-full rounded-md dark:bg-neutral-800 dark:hover:bg-indigo-900 bg-violet-100 hover:bg-violet-300 p-3 flex ring-inset" :data-id="video.id" :data-path="`../${video.attributes.path}`" @click="handlePlay">
        <td class="flex gap-12 w-full">
            <span class="vid-row-title truncate min-w-48">{{ video.attributes.name }}</span>
            <span class="vid-row-duration truncate">20m 30s</span>
        </td>
        <td class="flex gap-12 w-full justify-end">
            <span class="vid-row-title">{{ currentID === video.id }} {{ index }}</span>
            <span class="vid-row-date truncate overflow-hidden min-w-36">{{ toFormattedDate(rawDate) }}</span>
        </td>
    </tr>
</template>