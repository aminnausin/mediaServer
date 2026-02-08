<script setup lang="ts">
import type { HTMLAttributes } from 'vue';

import { handleStorageURL } from '@/service/util';
import { computed, inject } from 'vue';

import LazyImage from '@/components/lazy/LazyImage.vue';

const props = defineProps<{
    isNormalView: boolean;
    aspectRatio: {
        isPortrait: boolean;
        isAspectVideo: boolean;
    };
    isThumbnailDismissed?: boolean;
    audio_poster_url: string;
    poster_url?: string;
}>();

const isAudio = inject<boolean>('isAudio');

const audioPosterStyle = computed<HTMLAttributes['style']>(() => {
    return {
        backgroundImage: `url("${props.audio_poster_url}")`,
        backgroundPosition: 'center',
        backgroundSize: 'cover',
        backgroundRepeat: 'no-repeat',
    };
});
</script>

<template>
    <div
        :class="[
            'pointer-events-none absolute top-0 left-0 z-3 flex h-full w-full items-center',
            (isAudio || aspectRatio.isPortrait) && isNormalView ? 'max-h-[71vh]' : 'aspect-video',
        ]"
    >
        <div class="relative isolate h-full w-full">
            <template v-if="isAudio">
                <div id="audio-poster" class="absolute inset-0 z-0 blur-sm" :style="audioPosterStyle"></div>
                <LazyImage :src="audio_poster_url" alt="Album Art" class="mx-auto h-full object-contain select-none" loading="eager" fetchpriority="high" />
            </template>
            <LazyImage
                v-show="!isAudio && poster_url && !isThumbnailDismissed"
                :src="handleStorageURL(poster_url) ?? ''"
                alt="Thumbnail"
                :class="['mx-auto h-full']"
                loading="eager"
                fetchpriority="high"
            />
        </div>
    </div>
</template>
