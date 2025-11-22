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

const { title, views, duration } = useMetaData(toRef(() => videoData));

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
        :class="{ 'ring-2 ring-violet-700': currentID === videoData.id }"
        :to="encodeURI(`/${stateDirectory.name}/${stateFolder.name}?video=${videoData.id}`)"
        class="dark:bg-primary-dark-800/70 dark:odd:bg-primary-dark-600 relative flex w-full cursor-pointer flex-col flex-wrap gap-x-8 gap-y-4 rounded-md bg-neutral-50 p-3 shadow-sm ring-inset odd:bg-neutral-100 hover:bg-violet-400/30 dark:hover:bg-violet-700/70"
        :videoData-id="videoData.id"
        :videoData-path="`../${videoData.path}`"
        @contextmenu="
            (e: any) => {
                setContextMenu(e, { items: contextMenuItems });
            }
        "
    >
        <section class="flex w-full items-center justify-between gap-4 overflow-hidden">
            <HoverCard
                class="items-end"
                v-if="videoData.description"
                :content="videoData.description"
                :content-title="`${videoData.title}`"
                :hover-card-delay="400"
                :hover-card-leave-delay="300"
            >
                <template #trigger>
                    <span class="group flex">
                        <h3 class="line-clamp-1 break-all" :title="`Title: ${videoData.title}${videoData.name !== videoData.title ? `\nFile: ${videoData.name}` : ''}`">
                            {{ title }}
                        </h3>
                        <ProiconsComment class="my-auto ms-4 h-5 w-5 shrink-0 opacity-100 transition-opacity duration-300 group-hover:opacity-20" title="Description" />
                    </span>
                </template>
            </HoverCard>
            <h3 v-else class="line-clamp-1 break-all" :title="`Title: ${videoData.title}${videoData.name !== videoData.title ? `\nFile: ${videoData.name}` : ''}`">
                {{ title }}
            </h3>
            <HoverCard
                class="-ms-2 hidden flex-1 items-end sm:block"
                v-if="isAudio && videoData.metadata?.lyrics"
                :content-title="'Has Lyrics'"
                :hover-card-delay="400"
                :hover-card-leave-delay="300"
            >
                <template #trigger>
                    <TablerMicrophone2 class="h-5 w-5 shrink-0 opacity-100 transition-opacity duration-300 *:stroke-[1.4px] hover:opacity-20" title="Has Lyrics" v-if="isAudio" />
                </template>
            </HoverCard>

            <span class="flex min-w-fit gap-1 truncate text-sm text-neutral-600 uppercase dark:text-neutral-400">
                <h4 v-if="videoData.file_size" class="truncate text-nowrap" :title="`File Size: ${formatFileSize(videoData.file_size)}`">
                    {{ formatFileSize(videoData.file_size) }}
                </h4>
                <h4 v-if="(videoData.metadata?.codec && isAudio) || (!isAudio && videoData.metadata?.resolution_height)">|</h4>
                <h4 class="text-nowrap" v-if="isAudio && videoData?.metadata?.codec" :title="`File Codec: ${videoData.metadata.codec}`">
                    {{ videoData.metadata.codec }}
                </h4>
                <h4
                    class="text-nowrap"
                    v-else-if="videoData.metadata?.resolution_height && !isAudio"
                    :title="`Resolution: ${videoData.metadata.resolution_width}x${videoData.metadata.resolution_height}${videoData.metadata.codec && `\nFile Codec: ${videoData.metadata.codec}`}`"
                >
                    {{ videoData.metadata.resolution_height }}P{{ videoData.metadata.codec ? ` | ${videoData.metadata.codec}` : '' }}
                </h4>
            </span>
        </section>
        <section class="group flex w-full flex-wrap items-start justify-between gap-x-4 gap-y-2 text-sm text-neutral-600 sm:w-auto dark:text-neutral-400">
            <span class="flex w-full flex-1 items-center gap-2">
                <span class="flex gap-1">
                    <h4 class="min-w-fit" :title="`View Count: ${views}`">
                        {{ views }}
                    </h4>

                    <h4>|</h4>
                    <h4 class="line-clamp-1 text-wrap text-ellipsis" :title="`Duration: ${videoData.metadata?.duration}`">
                        {{ duration }}
                    </h4>
                </span>

                <span v-if="videoData.video_tags.length" class="hidden max-h-[22px] flex-1 flex-wrap gap-1 overflow-clip px-2 [overflow-clip-margin:4px] sm:flex" title="Tags">
                    <ChipTag
                        v-for="(tag, index) in videoData.video_tags"
                        :key="index"
                        :label="tag.name"
                        :colour="'bg-neutral-200 leading-none text-neutral-500 shadow-sm dark:bg-neutral-900 hover:bg-violet-600 hover:text-neutral-50 dark:hover:bg-violet-600/90'"
                    />
                </span>
            </span>

            <h4
                class="truncate text-end"
                :title="`Date Uploaded: ${toFormattedDate(new Date(videoData.date_uploaded ?? videoData.date + ' GMT'))}\nDate Scanned: ${toFormattedDate(new Date(videoData.date_created))}`"
            >
                {{ toFormattedDate(new Date(videoData.date_uploaded ?? videoData.date + ' GMT')) }}
            </h4>

            <span v-if="videoData.video_tags.length" class="flex w-full flex-wrap gap-1 overflow-clip [overflow-clip-margin:4px] sm:hidden" title="Tags">
                <ChipTag
                    v-for="(tag, index) in videoData.video_tags"
                    :key="index"
                    :label="tag.name"
                    :colour="'bg-neutral-200 leading-none text-neutral-500 shadow-sm dark:bg-neutral-900 hover:bg-violet-600 hover:text-neutral-50 dark:hover:bg-violet-600/90'"
                />
            </span>
        </section>
    </RouterLink>
</template>
