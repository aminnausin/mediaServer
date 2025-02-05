<script setup lang="ts">
import type { ContextMenuItem } from '@/types/types';
import type { VideoResource } from '@/types/resources';

import { formatFileSize, toFormattedDate } from '@/service/util';
import { useContentStore } from '@/stores/ContentStore';
import { computed, watch } from 'vue';
import { storeToRefs } from 'pinia';
import { useAppStore } from '@/stores/AppStore';
import { RouterLink } from 'vue-router';

import useMetaData from '@/composables/useMetaData';
import HoverCard from '@/components/cards/HoverCard.vue';
import ChipTag from '@/components/labels/ChipTag.vue';

import ProiconsComment from '~icons/proicons/comment';
import CircumShare1 from '~icons/circum/share-1';
import CircumEdit from '~icons/circum/edit';

const emit = defineEmits(['clickAction', 'otherAction']);
const props = defineProps<{ data: VideoResource; index: number; currentID: any }>();
const metaData = useMetaData({ ...props.data }, true);
const { stateFolder, stateDirectory } = storeToRefs(useContentStore());
const { setContextMenu } = useAppStore();

const contextMenuItems = computed(() => {
    let items: ContextMenuItem[] = [
        {
            text: 'Edit',
            icon: CircumEdit,
            action: () => {
                emit('otherAction', props.data?.id, 'edit');
            },
        },
        {
            text: 'Share',
            icon: CircumShare1,
            action: () => {
                emit('otherAction', props.data?.id, 'share');
            },
        },
    ];
    return items;
});

watch(
    props,
    () => {
        metaData.updateData({ ...props.data });
    },
    { immediate: true, deep: true },
);
</script>

<template>
    <RouterLink
        :class="{ 'ring-violet-600/70 ring-[0.125rem]': props?.currentID === props.data?.id }"
        :to="encodeURI(`/${stateDirectory.name}/${stateFolder.name}?video=${props.data.id}`)"
        class="relative flex flex-wrap flex-col gap-x-8 gap-y-4 p-3 w-full shadow rounded-md ring-inset cursor-pointer dark:bg-primary-dark-800/70 dark:hover:bg-violet-700/70 bg-gray-100 hover:bg-violet-400/30 odd:bg-violet-100 dark:odd:bg-primary-dark-600"
        :data-id="props.data?.id"
        :data-path="`../${props.data?.path}`"
        @contextmenu="
            (e: any) => {
                setContextMenu(e, { items: contextMenuItems });
            }
        "
    >
        <section class="flex justify-between gap-4 w-full items-center overflow-hidden group">
            <HoverCard class="items-end" v-if="data.description" :hover-card-delay="400" :hover-card-leave-delay="300">
                <template #trigger>
                    <span class="flex">
                        <h3 class="line-clamp-1">
                            {{ data.title }}
                            <!-- <span class="text-ellipsis text-wrap line-clamp-1 text-sm sm:text-base text-neutral-500 dark:text-neutral-400">{{
                    metaData?.fields?.description
                }}</span> -->
                        </h3>
                        <ProiconsComment class="my-auto ms-4 group-hover:opacity-20 opacity-100 transition-opacity duration-300 shrink-0 h-5 w-5" title="Description" />
                    </span>
                </template>
                <template #content>
                    {{ data.description }}
                </template>
            </HoverCard>
            <h3 v-else class="w-full line-clamp-1 flex gap-8 items-end min-w-fit max-w-[30%]" title="Title">
                {{ data.title }}
                <!-- <span class="text-ellipsis text-wrap line-clamp-1 text-sm sm:text-base text-neutral-500 dark:text-neutral-400">{{
                    metaData?.fields?.description
                }}</span> -->
            </h3>
            <span class="flex gap-1 truncate text-neutral-500 dark:text-neutral-400 text-sm">
                <h4 class="text-nowrap text-start truncate" :title="`File Size: ${data.file_size ? formatFileSize(data.file_size) : ''}`">
                    {{ data.file_size ? formatFileSize(data.file_size) : '' }}
                </h4>
                <h4 v-if="data.metadata?.codec || data.metadata?.resolution_height">|</h4>
                <h4
                    class="text-nowrap text-start truncate uppercase"
                    :title="`File Size: ${data.file_size ? formatFileSize(data.file_size) : ''}`"
                    v-if="data.metadata?.mime_type?.includes('audio') && data.metadata.codec"
                >
                    {{ data.metadata.codec }}
                </h4>
                <h4
                    class="text-nowrap text-start truncate uppercase"
                    :title="`File Size: ${data.file_size ? formatFileSize(data.file_size) : ''}`"
                    v-else-if="data.metadata?.resolution_height"
                >
                    {{ data.metadata.resolution_height }}P
                </h4>
            </span>
        </section>
        <section class="flex flex-wrap justify-between gap-x-4 gap-y-2 w-full items-start text-sm sm:w-auto text-neutral-500 dark:text-neutral-400 group">
            <span class="flex gap-2 items-center w-full flex-1">
                <span class="flex gap-1 truncate">
                    <h4 class="text-nowrap text-start truncate" :title="`View Count: ${metaData?.fields?.views}`">
                        {{ metaData?.fields?.views }}
                    </h4>

                    <h4>|</h4>
                    <h4 class="text-ellipsis text-wrap line-clamp-1 min-w-fit" title="Duration">
                        {{ metaData?.fields?.duration }}
                    </h4>
                </span>

                <span
                    v-if="props.data.video_tags.length"
                    class="hidden sm:flex flex-wrap gap-1 max-h-[22px] px-2 flex-1 overflow-y-auto scrollbar-minimal scrollbar-hover"
                    title="Tags"
                >
                    <ChipTag
                        v-for="(tag, index) in props.data?.video_tags"
                        v-bind:key="index"
                        :label="tag.name"
                        :colour="'bg-neutral-200 leading-none text-neutral-500 shadow dark:bg-neutral-900 hover:bg-violet-600 hover:text-neutral-50 hover:dark:bg-violet-600/90'"
                    />
                </span>
            </span>

            <h4 class="text-end truncate" title="Date Uploaded">
                {{ toFormattedDate(new Date(props.data?.date_uploaded ?? props.data.date + ' GMT')) }}
            </h4>

            <span v-if="props.data.video_tags.length" class="sm:hidden w-full flex flex-wrap gap-1 scrollbar-minimal scrollbar-hover" title="Tags">
                <ChipTag
                    v-for="(tag, index) in props.data?.video_tags"
                    v-bind:key="index"
                    :label="tag.name"
                    :colour="'bg-neutral-200 leading-none text-neutral-500 shadow dark:bg-neutral-900 hover:bg-violet-600 hover:text-neutral-50 hover:dark:bg-violet-600/90'"
                />
            </span>
        </section>
    </RouterLink>
</template>
