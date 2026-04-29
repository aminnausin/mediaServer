<script setup lang="ts">
import type { ContextMenuItem } from '@/types/types';
import type { VideoResource } from '@/types/resources';

import { formatFileSize, toFormattedDate, toPlural } from '@/service/util';
import { getMediaDateDescription } from '@/service/media/mediaFormatter';
import { computed, toRef } from 'vue';
import { useContentStore } from '@/stores/ContentStore';
import { useAuthStore } from '@/stores/AuthStore';
import { storeToRefs } from 'pinia';
import { useAppStore } from '@/stores/AppStore';
import { RouterLink } from 'vue-router';
import { HoverCard } from '@/components/cedar-ui/hover-card';
import { MediaType } from '@/types/types';
import { cn } from '@aminnausin/cedar-ui';

import TablerSubtitles from '@/components/icons/TablerSubtitles.vue';
import useMetaData from '@/composables/useMetaData';
import MediaTag from '@/components/labels/MediaTag.vue';

import TablerMicrophone2 from '~icons/tabler/microphone-2';
import ProiconsCheckmark from '~icons/proicons/checkmark';
import ProiconsComment from '~icons/proicons/comment';
import CircumShare1 from '~icons/circum/share-1';
import ProiconsPlay from '~icons/proicons/play';
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

const resumeOffset = computed(() => (videoData.progress_offset && videoData.progress_percentage < 95 && !isAudio.value ? `&t=${videoData.progress_offset}` : ''));

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
        {
            text: 'Open in New Tab',
            icon: ProiconsPlay,
            action: () => {
                if (videoData?.id) window.open(`/${stateDirectory.value.name}/${stateFolder.value.name}?video=${videoData.id}`, '_blank');
                else window.open(`/${stateDirectory.value.name}/${stateFolder.value.name}`, '_blank');
            },
        },
    ];
    return items;
});

const dateInformation = computed(() => getMediaDateDescription(videoData));
</script>

<template>
    <RouterLink
        :class="[
            'dark:hover:bg-primary-active/70 dark:bg-primary-dark-800/70 dark:odd:bg-primary-dark-600 hover:bg-primary/5 bg-neutral-50 odd:bg-neutral-100',
            'relative flex w-full flex-col gap-x-8 gap-y-4 rounded-md',
            'data-card content-visible hover:data-card-hover overflow-clip p-3 shadow-sm focus-within:outline-none',
        ]"
        :to="encodeURI(`/${stateDirectory.name}/${stateFolder.name}?video=${videoData.id}${resumeOffset}`)"
        @contextmenu="
            (e: any) => {
                setContextMenu(e, { items: contextMenuItems });
            }
        "
    >
        <div class="flex w-full items-center justify-between gap-4 overflow-hidden">
            <h3 class="line-clamp-1 min-w-8 break-all" :title="`Title: ${videoData.title}${videoData.name !== videoData.title ? `\nFile: ${videoData.name}` : ''}`">
                {{ title }}
            </h3>
            <div class="-ms-2 flex flex-1 gap-1">
                <HoverCard
                    class="items-end"
                    v-if="videoData.description"
                    :content="videoData.description"
                    :content-title="`${videoData.title}`"
                    :hover-card-delay="400"
                    :hover-card-leave-delay="300"
                >
                    <template #trigger>
                        <ProiconsComment class="my-auto size-5 shrink-0 opacity-100 transition-opacity duration-300 hover:opacity-20" title="Description" />
                    </template>
                </HoverCard>
                <HoverCard class="xs:block hidden" v-if="videoData.metadata?.lyrics" :content-title="'Has Lyrics'" :hover-card-delay="400" :hover-card-leave-delay="300">
                    <template #trigger>
                        <TablerMicrophone2 class="size-5 shrink-0 opacity-100 transition-opacity duration-300 *:stroke-[1.4px] hover:opacity-20" title="Has Lyrics" />
                    </template>
                </HoverCard>
                <HoverCard
                    class="xs:block hidden"
                    v-if="videoData.subtitles.length > 0 && !isAudio"
                    :content-title="'Has Subtitles'"
                    :hover-card-delay="400"
                    :hover-card-leave-delay="300"
                >
                    <template #trigger>
                        <TablerSubtitles class="size-5 shrink-0 opacity-100 transition-opacity duration-300 *:stroke-[1.4px] hover:opacity-20" title="Has Subtitles" />
                    </template>
                </HoverCard>

                <HoverCard
                    class="xs:block hidden"
                    v-if="videoData.progress_percentage === 100 || videoData.completion_count > 0"
                    :content-title="`Completed ${videoData.completion_count || 1} time${toPlural(videoData.completion_count || 1)}`"
                    :hover-card-delay="400"
                    :hover-card-leave-delay="300"
                >
                    <template #trigger>
                        <ProiconsCheckmark class="size-5 shrink-0 opacity-100 transition-opacity duration-300 *:stroke-[1.4px] hover:opacity-20" title="Completed" />
                    </template>
                </HoverCard>
            </div>

            <div class="text-foreground-1 flex min-w-fit gap-1 text-sm uppercase *:text-nowrap">
                <span v-if="videoData.file_size" :title="`File Size: ${formatFileSize(videoData.file_size)}`">
                    {{ formatFileSize(videoData.file_size) }}
                </span>
                <span v-if="(videoData.metadata?.codec && isAudio) || (!isAudio && videoData.metadata?.resolution_height)">|</span>
                <span v-if="isAudio && videoData?.metadata?.codec" :title="`File Codec: ${videoData.metadata.codec}`">
                    {{ videoData.metadata.codec }}
                </span>
                <span
                    v-else-if="videoData.metadata?.resolution_height && !isAudio"
                    :title="`Resolution: ${videoData.metadata.resolution_width}x${videoData.metadata.resolution_height}${videoData.metadata.codec && `\nFile Codec: ${videoData.metadata.codec}`}`"
                >
                    {{ videoData.metadata.resolution_height }}P{{ videoData.metadata.codec ? ` | ${videoData.metadata.codec}` : '' }}
                </span>
            </div>
        </div>
        <div class="group text-foreground-1 flex w-full flex-wrap items-start justify-between gap-x-4 gap-y-2 overflow-x-clip text-sm sm:w-auto">
            <div class="flex items-center gap-2">
                <div class="flex gap-1">
                    <span class="min-w-fit" :title="`View Count: ${views}`">
                        {{ views }}
                    </span>

                    <span>|</span>
                    <span class="text-nowrap" :title="`Duration: ${duration}`">
                        {{ duration }}
                    </span>
                </div>
            </div>
            <span
                v-if="videoData.video_tags.length"
                class="order-3 flex w-full flex-wrap gap-1 overflow-clip [overflow-clip-margin:4px] sm:order-0 sm:h-5.5 sm:flex-1 sm:gap-y-2"
                title="Tags"
            >
                <MediaTag v-for="(tag, index) in videoData.video_tags" :key="index" :label="tag.name" />
            </span>

            <HoverCard class="text-end text-nowrap" :hover-card-delay="400" :hover-card-leave-delay="300" :content="dateInformation">
                <template #trigger>
                    {{ toFormattedDate(videoData.file_modified_at) }}
                </template>
            </HoverCard>
        </div>
        <div
            v-if="videoData.progress_percentage"
            :class="
                cn(
                    'playback-progress-bar absolute bottom-0 left-0 flex h-1 w-full',
                    'duration-input bg-neutral-300 opacity-80 transition-[translate,opacity] dark:bg-neutral-700 dark:opacity-60',
                    {
                        '-translate-y-0.5 opacity-100 dark:opacity-100': currentID === videoData.id,
                        'in-focus:-translate-y-0.5': currentID !== videoData.id,
                    },
                )
            "
            :title="`Progress: ${videoData.progress_percentage}s`"
        >
            <div
                :class="
                    cn('bg-foreground-3 duration-input mt-auto h-full w-full transition-[background-color]', {
                        'bg-primary-muted dark:in-focus:bg-primary dark:bg-primary-active': currentID === videoData.id,
                        'playback-progress-fill': currentID !== videoData.id,
                    })
                "
                :style="{ width: `${videoData.progress_percentage}%` }"
            ></div>
        </div>
        <div
            :class="
                cn('pointer-events-none absolute inset-0 rounded-md', 'transition-input duration-input', 'ring-transparent ring-inset', {
                    'ring-primary-muted dark:ring-primary-active in-focus:ring-primary ring-2': currentID === videoData.id,
                    'in-focus:ring-foreground-0 in-focus:ring-2': currentID !== videoData.id,
                })
            "
        ></div>
    </RouterLink>
</template>
<style lang="css" scoped>
.data-card {
    &:hover .playback-progress-bar {
        opacity: 1;
    }

    &:hover .playback-progress-fill {
        background-color: var(--color-primary-muted);
    }
}

.dark .data-card:hover .playback-progress-fill {
    background-color: var(--color-primary-active);
}
</style>
