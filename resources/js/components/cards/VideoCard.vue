<script setup>
import useMetaData from '../../composables/useMetaData';

import { watch } from 'vue';

const props = defineProps(['data', 'index', 'currentID']);
const metaData = useMetaData(props.data?.attributes);
const emit = defineEmits(['clickAction']);
const handlePlay = () => {
    emit('clickAction', props.data?.id);
}

const handlePropsUpdate = () => {
    metaData.updateData(props.data?.attributes);
}

watch(props, handlePropsUpdate, {immediate: true});
</script>

<template>
    <div :class="{ 'ring-violet-600/70 ring-[0.125rem]': (props?.currentID === props.data?.id) }"
        class="flex flex-wrap flex-col sm:flex-row sm:flex-nowrap gap-x-8 gap-y-4 p-3 w-full shadow rounded-md ring-inset dark:bg-primary-dark-800/70 dark:hover:bg-violet-700/70 bg-gray-100 hover:bg-violet-400/30 odd:bg-violet-100 dark:odd:bg-primary-dark-600"
        :data-id="props.data?.id" :data-path="`../${props.data?.attributes.path}`" @click="handlePlay">
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
                {{ props.data?.attributes.date }}
            </h3>
        </section>
    </div>
</template>