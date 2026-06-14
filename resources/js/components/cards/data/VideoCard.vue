<script setup lang="ts">
import type { ContextMenuItem } from '@/types/types';
import type { VideoResource } from '@/types/resources';

import { formatFileSize, handleStorageURL, toFormattedDate, toFormattedDuration, toPlural } from '@/service/util';
import { computed, toRef, useTemplateRef } from 'vue';
import { getMediaDateDescription } from '@/service/media/mediaFormatter';
import { handleEditMediaImages } from '@/service/media/mediaActions';
import { useContentStore } from '@/stores/ContentStore';
import { useBreakpoints } from '@vueuse/core';
import { useAuthStore } from '@/stores/AuthStore';
import { storeToRefs } from 'pinia';
import { useAppStore } from '@/stores/AppStore';
import { RouterLink } from 'vue-router';
import { HoverCard } from '@/components/cedar-ui/hover-card';
import { MediaType } from '@/types/types';
import { cn } from '@aminnausin/cedar-ui';

import TablerSubtitles from '@/components/icons/TablerSubtitles.vue';
import VideoPreview from '@/components/video/VideoPreview.vue';
import useMetaData from '@/composables/useMetaData';
import MediaTag from '@/components/labels/MediaTag.vue';

import ProiconsInfoSquare from '~icons/proicons/info-square';
import TablerMicrophone2 from '~icons/tabler/microphone-2';
import ProiconsCheckmark from '~icons/proicons/checkmark';
import ProiconsComment from '~icons/proicons/comment';
import ProiconsPhoto from '~icons/proicons/photo';
import CircumShare1 from '~icons/circum/share-1';
import ProiconsPlay from '~icons/proicons/play';
import CircumEdit from '~icons/circum/edit';

const emit = defineEmits(['clickAction', 'otherAction']);

const preview = useTemplateRef('preview');

const { stateFolder, stateDirectory } = storeToRefs(useContentStore());
const { data: videoData, currentID } = defineProps<{ data: VideoResource; index: number; currentID: any }>();
const { isAuthenticated } = storeToRefs(useAuthStore());
const { setContextMenu } = useAppStore();

const { title, views, duration } = useMetaData(toRef(() => videoData));

const breakpoints = useBreakpoints({ smm: 480 });
const isSmallScreen = breakpoints.smallerOrEqual('smm');

const isAudio = computed(() => {
    return videoData.metadata?.media_type === MediaType.AUDIO;
});

const posterUrl = computed(() => {
    const url = videoData.metadata?.poster_image?.path;
    const audioFallback = stateFolder.value.series?.poster_image?.path ?? handleStorageURL(stateFolder.value.series?.thumbnail_url) ?? '/storage/thumbnails/default.webp';

    return isAudio.value ? (url ?? audioFallback) : url;
});

const resumeOffset = computed(() => (videoData.progress_offset && videoData.progress_percentage < 95 && !isAudio.value ? `&t=${videoData.progress_offset}` : ''));

const contextMenuItems = computed<ContextMenuItem[]>(() => [
    {
        text: 'Edit',
        icon: CircumEdit,
        hidden: !isAuthenticated.value,
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
    { divider: true, hidden: !isAuthenticated.value },
    {
        icon: ProiconsPhoto,
        text: 'Edit Images',
        action: () => handleEditMediaImages(videoData),
        hidden: !isAuthenticated.value,
    },
]);

const dateInformation = computed(() => getMediaDateDescription(videoData));
</script>

<template>
    <RouterLink
        :to="encodeURI(`/${stateDirectory.name}/${stateFolder.name}?video=${videoData.id}${resumeOffset}`)"
        :class="
            cn(
                'dark:hover:bg-primary-active/70 dark:bg-primary-dark-800/70 dark:odd:bg-primary-dark-600 hover:bg-primary/5 bg-neutral-50 odd:bg-neutral-100',
                'relative flex rounded-md',
                'data-card hover:data-card-hover max-w-full overflow-clip shadow-sm focus-within:outline-none',
            )
        "
    >
        <VideoPreview
            :data="data"
            :data-active="currentID === videoData.id"
            :poster-url="posterUrl"
            :is-audio="isAudio"
            :is-folder-majority-audio="stateFolder.is_majority_audio"
            :class="cn('shrink-0', stateFolder.is_majority_audio ? 'h-24' : 'h-20 sm:h-24')"
            ref="preview"
        />

        <div
            class="flex flex-1 flex-col text-xs sm:text-sm"
            @contextmenu="
                (e: any) => {
                    setContextMenu(e, { items: contextMenuItems });
                }
            "
        >
            <div :class="cn('flex flex-1 flex-col justify-between gap-x-8 gap-y-2 p-3 pb-2 sm:gap-y-4', { 'mb-1': isAudio || !preview?.hovered })">
                <div class="flex w-full items-center justify-between gap-x-4 gap-y-1 overflow-hidden">
                    <HoverCard
                        :disabled="videoData.name === videoData.title"
                        :content="`File: ${videoData.name}.${videoData.path.split('.').at(-1)}`"
                        :content-title="`${videoData.title}`"
                        :hover-card-delay="400"
                        :hover-card-leave-delay="300"
                    >
                        <template #trigger>
                            <h3 class="line-clamp-1 min-w-8 text-sm break-all sm:text-base" :title="videoData.name === videoData.title ? `Title: ${videoData.title}` : ''">
                                {{ title }}
                            </h3>
                        </template>
                    </HoverCard>

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
                                <ProiconsComment class="my-auto size-4 shrink-0 opacity-100 transition-opacity duration-300 hover:opacity-20 sm:size-5" title="Description" />
                            </template>
                        </HoverCard>
                        <HoverCard class="xs:block hidden" v-if="videoData.metadata?.lyrics" :content-title="'Has Lyrics'" :hover-card-delay="400" :hover-card-leave-delay="300">
                            <template #trigger>
                                <TablerMicrophone2
                                    class="size-4 shrink-0 opacity-100 transition-opacity duration-300 *:stroke-[1.4px] hover:opacity-20 sm:size-5"
                                    title="Has Lyrics"
                                />
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
                                <TablerSubtitles
                                    class="size-4 shrink-0 opacity-100 transition-opacity duration-300 *:stroke-[1.4px] hover:opacity-20 sm:size-5"
                                    title="Has Subtitles"
                                />
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
                                <ProiconsCheckmark
                                    class="size-4 shrink-0 opacity-100 transition-opacity duration-300 *:stroke-[1.4px] hover:opacity-20 sm:size-5"
                                    title="Completed"
                                />
                            </template>
                        </HoverCard>
                    </div>

                    <HoverCard :disabled="!isSmallScreen" :hover-card-delay="100" :hover-card-leave-delay="400" @contextmenu.stop>
                        <template #trigger>
                            <div v-if="!isSmallScreen" class="text-foreground-1 xms:flex hidden gap-1 overflow-clip uppercase *:text-nowrap sm:min-w-fit">
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
                            <ProiconsInfoSquare v-else class="size-5" />
                        </template>
                        <template #content>
                            <div class="text-foreground-1 flex gap-1 overflow-clip uppercase *:text-nowrap sm:min-w-fit">
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
                        </template>
                    </HoverCard>
                </div>
                <div class="group text-foreground-1 flex w-full flex-wrap items-start justify-between gap-2 overflow-x-clip sm:w-auto sm:gap-x-4">
                    <div class="flex items-center gap-1">
                        <span class="min-w-fit" :title="`View Count: ${views}`">
                            {{ views }}
                        </span>

                        <span>|</span>
                        <span class="text-nowrap" :title="`Duration: ${duration}`">
                            {{ toFormattedDuration(data.metadata?.duration ?? 0, false, 'analog', true) }}
                        </span>
                    </div>
                    <span
                        v-if="videoData.video_tags.length"
                        class="3xl:h-5.5 3xl:gap-1 3xl:*:text-sm -order-1 flex w-full flex-wrap gap-0.5 overflow-clip [overflow-clip-margin:4px] *:w-fit *:text-xs sm:order-0 sm:h-5 sm:flex-1 sm:gap-y-2"
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
            </div>
            <div
                :class="
                    cn('duration-input h-1 opacity-0 transition-opacity', {
                        'opacity-100': videoData.progress_percentage,
                        'dark:bg-primary-dark-800/70 dark:odd:bg-primary-dark-600 absolute bottom-0 left-0 w-full bg-neutral-50 odd:bg-neutral-100': isAudio || !preview?.hovered,
                    })
                "
            >
                <div
                    :class="
                        cn(
                            'playback-progress-bar flex h-full',
                            'duration-input mt-auto bg-neutral-300 opacity-80 transition-[translate,opacity,margin] dark:bg-neutral-700 dark:opacity-60',
                            {
                                'me-0.5 -translate-y-0.5 opacity-100 dark:opacity-100': currentID === videoData.id,
                                'in-focus:me-0.5 in-focus:-translate-y-0.5': currentID !== videoData.id,
                            },
                        )
                    "
                    :title="`Progress: ${videoData.progress_percentage}%`"
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
            </div>
            <div
                :class="
                    cn('pointer-events-none absolute inset-0 rounded-md', 'transition-input duration-input', 'z-4 ring-transparent ring-inset', {
                        'ring-primary-muted dark:ring-primary-active in-focus:ring-primary ring-2': currentID === videoData.id,
                        'in-focus:ring-foreground-0 in-focus:ring-2': currentID !== videoData.id,
                    })
                "
            ></div>
        </div>
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
