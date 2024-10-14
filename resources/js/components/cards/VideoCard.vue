<script setup>
import useMetaData from '../../composables/useMetaData';

import { watch } from 'vue';
import { RouterLink } from 'vue-router';
import { toFormattedDate } from '../../service/util';

const props = defineProps(['data', 'index', 'currentID']);
const metaData = useMetaData({ ...props.data, id: props.data.id, skipBaseURL: true });
const emit = defineEmits(['clickAction']);
const handlePlay = () => {
    emit('clickAction', props.data?.id);
};

const handlePropsUpdate = () => {
    metaData.updateData({ ...props.data, id: props.data.id, skipBaseURL: true });
};

watch(props, handlePropsUpdate, { immediate: true });
</script>

<template>
    <RouterLink
        :class="{ 'ring-violet-600/70 ring-[0.125rem]': props?.currentID === props.data?.id }"
        :to="metaData.fields.url"
        class="flex flex-wrap flex-col gap-x-8 gap-y-4 p-3 w-full shadow rounded-md ring-inset cursor-pointer dark:bg-primary-dark-800/70 dark:hover:bg-violet-700/70 bg-gray-100 hover:bg-violet-400/30 odd:bg-violet-100 dark:odd:bg-primary-dark-600"
        :data-id="props.data?.id"
        :data-path="`../${props.data?.path}`"
        @click.left.stop.prevent.capture="handlePlay"
    >
        <section class="flex justify-between gap-4 w-full items-start">
            <h3 class="w-full line-clamp-2 sm:line-clamp-1">
                {{ metaData?.fields?.title }}
            </h3>
            <h3 class="text-nowrap text-neutral-500 dark:text-neutral-400">
                {{ metaData?.fields?.duration }}
            </h3>
        </section>
        <section class="text-sm sm:text-base flex justify-between sm:w-auto items-center text-neutral-500 dark:text-neutral-400 gap-8">
            <h3 class="text-sm sm:text-base flex justify-end text-nowrap">
                {{ metaData?.fields?.views }}
            </h3>

            <h3 class="line-clamp-1 text-end">
                {{ toFormattedDate(new Date(props.data?.date + ' GMT')) }}
            </h3>
        </section>
    </RouterLink>
</template>
