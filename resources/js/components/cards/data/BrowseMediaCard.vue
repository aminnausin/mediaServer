<script setup lang="ts">
import type { VideoResource } from '@/contracts/media';

import { MediaType } from '@/types/types';
import { computed } from 'vue';
import { FLAGS } from '@/config/featureFlags';
import { cn } from '@aminnausin/cedar-ui';

import PlayerOSDBase from '@/components/video/OSD/PlayerOSDBase.vue';
import VideoPreview from '@/components/video/VideoPreview.vue';

import CircumPlay1 from '~icons/circum/play-1';

const props = defineProps<{ media: VideoResource; forceAudio?: boolean; eagerLoad?: boolean }>();

const mediaUrl = computed(() => {
    if (!props.media.url) return '/';

    const url = new URL(props.media.url, window.location.origin);

    if (props.media.progress_offset && props.media.metadata?.media_type === MediaType.VIDEO) {
        url.searchParams.set('t', String(props.media.progress_offset));
    }

    return url.pathname + url.search;
});
</script>

<template>
    <RouterLink
        :to="mediaUrl"
        :class="
            cn(
                'group relative flex flex-col gap-2',
                'focus-within:outline-none',
                'data-card hover:data-card-hover',
                'w-56 shrink-0 rounded-md',
                'content-auto [contain-intrinsic-size:224px_174px]',
                { 'rounded-none bg-transparent shadow-none': FLAGS.USE_TRANSPARENT_HOME_CARDS },
                $attrs.class,
            )
        "
    >
        <div :class="cn('relative overflow-clip rounded-t-md shadow-sm', { 'rounded-b-md': FLAGS.USE_TRANSPARENT_HOME_CARDS })">
            <VideoPreview
                :data="media"
                :data-active="false"
                :eager-load="eagerLoad"
                :poster-url="media.metadata?.poster_image?.path"
                :is-audio="media.metadata?.media_type === MediaType.AUDIO"
                :is-folder-majority-audio="forceAudio"
                :class="cn('size-full dark:bg-neutral-950/80')"
                :wrapper-class="cn('peer content-auto ', forceAudio ? 'aspect-square [contain-intrinsic-size:160px_160px]' : 'aspect-video [contain-intrinsic-size:224px_126px]')"
            />
            <div
                :class="
                    cn(
                        'duration-input h-1 opacity-0 transition-opacity',
                        'dark:bg-primary-dark-800/70 dark:odd:bg-primary-dark-600 absolute bottom-0 left-0 w-full bg-neutral-50 odd:bg-neutral-100',
                        {
                            'opacity-100': media.progress_percentage,
                            'peer-hover:opacity-0': media.metadata?.media_type === MediaType.VIDEO && media.storyboard,
                        },
                    )
                "
            >
                <div
                    :class="
                        cn(
                            'playback-progress-bar flex h-full',
                            'duration-input mt-auto bg-neutral-300 opacity-80 transition-[translate,opacity,margin] dark:bg-neutral-700 dark:opacity-60',
                        )
                    "
                    :title="`Progress: ${media.progress_percentage}%`"
                >
                    <div
                        :class="cn('bg-foreground-3 duration-input mt-auto h-full w-full transition-[background-color]', 'playback-progress-fill')"
                        :style="{ width: `${media.progress_percentage}%` }"
                    ></div>
                </div>
            </div>
            <div :class="cn('absolute right-0 bottom-1 size-7')">
                <div
                    :class="
                        cn(
                            'pointer-events-auto size-6 p-0 opacity-0',
                            'origin-center scale-80 transform-gpu will-change-transform',
                            'duration-input transition-[opacity,scale] group-hover:scale-100 group-hover:opacity-100 hover:scale-110 active:scale-95',
                        )
                    "
                >
                    <PlayerOSDBase class="size-full p-0">
                        <CircumPlay1 class="ms-px size-4" stroke-width="0.25" stroke="currentColor" />
                    </PlayerOSDBase>
                </div>
            </div>
        </div>
        <div class="flex w-full flex-col px-2 pb-2 text-xs">
            <slot name="title">
                <p class="truncate" :title="media.title">
                    {{ media.title }}
                </p>
            </slot>
            <slot />
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
