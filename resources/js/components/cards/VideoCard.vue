<script setup lang="ts">
import type { ContextMenuItem } from '@/types/types';
import type { VideoResource } from '@/types/resources';

import { formatFileSize, toFormattedDate } from '@/service/util';
import { useContentStore } from '@/stores/ContentStore';
import { computed, toRef } from 'vue';
import { useAuthStore } from '@/stores/AuthStore';
import { storeToRefs } from 'pinia';
import { useAppStore } from '@/stores/AppStore';
import { RouterLink } from 'vue-router';
import { MediaType } from '@/types/types';

import useMetaData from '@/composables/useMetaData';
import HoverCard from '@/components/cards/HoverCard.vue';
import ChipTag from '@/components/labels/ChipTag.vue';

import TablerMicrophone2 from '~icons/tabler/microphone-2';
import ProiconsComment from '~icons/proicons/comment';
import CircumShare1 from '~icons/circum/share-1';
import CircumEdit from '~icons/circum/edit';

const emit = defineEmits(['clickAction', 'otherAction']);

const { data: videoData, currentID } = defineProps<{ data: VideoResource; index: number; currentID: any }>();
const { stateFolder, stateDirectory } = storeToRefs(useContentStore());
const { setContextMenu } = useAppStore();
const { userData } = storeToRefs(useAuthStore());

const { title, views, duration } = useMetaData(
    toRef(() => videoData),
    true,
);

const isAudio = computed(() => {
    return videoData.metadata?.media_type === MediaType.AUDIO;
});

const contextMenuItems = computed(() => {
    const items: ContextMenuItem[] = [
        {
            text: 'Edit',
            icon: CircumEdit,
            hidden: !userData.value,
            action: () => {
                emit('otherAction', videoData?.id, 'edit');
            },
        },
        {
            text: 'Share',
            icon: CircumShare1,
            action: () => {
                emit('otherAction', videoData?.id, 'share');
            },
        },
    ];
    return items;
});
</script>

<template>
    <RouterLink
        :class="{ 'ring-violet-700 ring-[0.125rem]': currentID === videoData.id }"
        :to="encodeURI(`/${stateDirectory.name}/${stateFolder.name}?video=${videoData.id}`)"
        class="relative flex flex-wrap flex-col gap-x-8 gap-y-4 p-3 w-full shadow rounded-md ring-inset cursor-pointer dark:bg-primary-dark-800/70 dark:hover:bg-violet-700/70 bg-neutral-50 hover:bg-violet-400/30 odd:bg-neutral-100 dark:odd:bg-primary-dark-600"
        :videoData-id="videoData.id"
        :videoData-path="`../${videoData.path}`"
        :title="videoData.title"
        @contextmenu="
            (e: any) => {
                setContextMenu(e, { items: contextMenuItems });
            }
        "
    >
        <section class="flex justify-between gap-4 w-full items-center overflow-hidden">
            <HoverCard
                class="items-end"
                v-if="videoData.description"
                :content="videoData.description"
                :content-title="videoData.title"
                :hover-card-delay="400"
                :hover-card-leave-delay="300"
            >
                <template #trigger>
                    <span class="flex group">
                        <h3 class="line-clamp-1 break-all">
                            {{ title }}
                        </h3>
                        <ProiconsComment class="my-auto ms-4 group-hover:opacity-20 opacity-100 transition-opacity duration-300 shrink-0 h-5 w-5" title="Description" />
                    </span>
                </template>
            </HoverCard>
            <h3 v-else class="line-clamp-1 break-all" :title="videoData.title">
                {{ title }}
            </h3>
            <HoverCard
                class="items-end flex-1 -ms-2 hidden sm:block"
                v-if="isAudio && videoData.metadata?.lyrics"
                :content-title="'Has Lyrics'"
                :hover-card-delay="400"
                :hover-card-leave-delay="300"
            >
                <template #trigger>
                    <TablerMicrophone2
                        class="[&>*]:stroke-[1.4px] shrink-0 h-5 w-5 hover:opacity-20 opacity-100 transition-opacity duration-300"
                        title="Has Lyrics"
                        v-if="isAudio"
                    />
                </template>
            </HoverCard>

            <span class="flex gap-1 truncate text-neutral-600 dark:text-neutral-400 text-sm uppercase min-w-fit">
                <h4 class="text-nowrap truncate" :title="`File Size: ${videoData.file_size ? formatFileSize(videoData.file_size) : ''}`">
                    {{ videoData.file_size ? formatFileSize(videoData.file_size) : '' }}
                </h4>
                <h4 v-if="(videoData.metadata?.codec && isAudio) || (!isAudio && videoData.metadata?.resolution_height)">|</h4>
                <h4 class="text-nowrap" v-if="isAudio && videoData?.metadata?.codec" title="File Codec">
                    {{ videoData.metadata.codec }}
                </h4>
                <h4 class="text-nowrap" v-else-if="videoData.metadata?.resolution_height && !isAudio" title="Resolution and Codec">
                    {{ videoData.metadata.resolution_height }}P{{ videoData.metadata.codec ? ` | ${videoData.metadata.codec}` : '' }}
                </h4>
            </span>
        </section>
        <section class="flex flex-wrap justify-between gap-x-4 gap-y-2 w-full items-start text-sm sm:w-auto text-neutral-600 dark:text-neutral-400 group">
            <span class="flex gap-2 items-center w-full flex-1">
                <span class="flex gap-1">
                    <h4 class="min-w-fit" :title="`View Count: ${views}`">
                        {{ views }}
                    </h4>

                    <h4>|</h4>
                    <h4 class="text-ellipsis text-wrap line-clamp-1" title="Duration">
                        {{ duration }}
                    </h4>
                </span>

                <span v-if="videoData.video_tags.length" class="hidden sm:flex flex-wrap gap-1 max-h-[22px] px-2 flex-1 overflow-clip [overflow-clip-margin:4px]" title="Tags">
                    <ChipTag
                        v-for="(tag, index) in videoData.video_tags"
                        :key="index"
                        :label="tag.name"
                        :colour="'bg-neutral-200 leading-none text-neutral-500 shadow dark:bg-neutral-900 hover:bg-violet-600 hover:text-neutral-50 hover:dark:bg-violet-600/90'"
                    />
                </span>
            </span>

            <h4
                class="text-end truncate"
                :title="`Date Uploaded: ${toFormattedDate(new Date(videoData.date_uploaded ?? videoData.date + ' GMT'))}\nDate Added: ${toFormattedDate(new Date(videoData.date_created))}`"
            >
                {{ toFormattedDate(new Date(videoData.date_uploaded ?? videoData.date + ' GMT')) }}
            </h4>

            <span v-if="videoData.video_tags.length" class="sm:hidden w-full flex flex-wrap gap-1 overflow-clip [overflow-clip-margin:4px]" title="Tags">
                <ChipTag
                    v-for="(tag, index) in videoData.video_tags"
                    :key="index"
                    :label="tag.name"
                    :colour="'bg-neutral-200 leading-none text-neutral-500 shadow dark:bg-neutral-900 hover:bg-violet-600 hover:text-neutral-50 hover:dark:bg-violet-600/90'"
                />
            </span>
        </section>
    </RouterLink>
</template>
