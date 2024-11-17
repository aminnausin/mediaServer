<script setup>
import useMetaData from '../../composables/useMetaData';

import { watch } from 'vue';
import { RouterLink } from 'vue-router';
import { toFormattedDate } from '../../service/util';
import ChipTag from '../labels/ChipTag.vue';

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
        class="relative group flex flex-wrap flex-col gap-x-8 gap-y-4 p-3 w-full shadow rounded-md ring-inset cursor-pointer dark:bg-primary-dark-800/70 dark:hover:bg-violet-700/70 bg-gray-100 hover:bg-violet-400/30 odd:bg-violet-100 dark:odd:bg-primary-dark-600"
        :data-id="props.data?.id"
        :data-path="`../${props.data?.path}`"
        @click.left.stop.prevent.capture="handlePlay"
    >
        <section class="flex justify-between gap-4 w-full items-start overflow-hidden" :title="metaData?.fields?.description">
            <h3 class="w-full line-clamp-1 flex gap-8 items-end min-w-fit max-w-[30%]">
                <span>{{ metaData?.fields?.title }}</span>
                <!-- <span class="text-ellipsis text-wrap line-clamp-1 text-sm sm:text-base text-neutral-500 dark:text-neutral-400">{{
                    metaData?.fields?.description
                }}</span> -->
            </h3>
            <h3 class="text-ellipsis text-wrap line-clamp-1 text-neutral-500 dark:text-neutral-400">
                {{ metaData?.fields?.duration }}
            </h3>
        </section>
        <section
            class="flex justify-between gap-4 w-full items-start text-sm sm:text-base sm:w-auto text-neutral-500 dark:text-neutral-400 overflow-hidden"
        >
            <span class="flex gap-2 items-center w-full flex-1">
                <h3 class="text-sm sm:text-base text-nowrap text-start">
                    {{ metaData?.fields?.views }}
                </h3>

                <span class="hidden sm:flex flex-wrap gap-1 max-h-5 h-full sm:max-h-[24px] overflow-x-hidden px-2 flex-1">
                    <ChipTag
                        v-for="(tag, index) in props.data?.video_tags"
                        v-bind:key="index"
                        :label="tag.name"
                        :colour="'bg-neutral-200 leading-none text-neutral-500 shadow dark:bg-neutral-900 hover:bg-violet-600 hover:text-neutral-50 hover:dark:bg-violet-600/90 z-20'"
                    />
                </span>
            </span>

            <h3 class="text-end truncate">
                {{ toFormattedDate(new Date(props.data?.date + ' GMT')) }}
            </h3>
        </section>
        <div
            v-if="metaData?.fields?.description?.length > 0"
            class="z-30 left-20 bottom-10 absolute opacity-0 group-hover:opacity-100 transition-opacity ease-in-out duration-500 w-1/2"
        >
            <div
                class="p-3 shadow rounded-md ring-inset dark:bg-primary-dark-800 bg-gray-100 odd:bg-violet-100 dark:odd:bg-primary-dark-600"
            >
                {{ metaData?.fields?.description }}
            </div>
        </div>
    </RouterLink>
</template>
