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
    poster_url?: string;
}>();

const isAudio = inject<boolean>('isAudio');

const audioPosterStyle = computed<HTMLAttributes['style']>(() => {
    return {
        backgroundImage: `url("${props.poster_url}")`,
        backgroundPosition: 'center',
        backgroundSize: 'cover',
        backgroundRepeat: 'no-repeat',
    };
});
</script>

<template>
    <div
        :class="[
            'pointer-events-none absolute top-0 left-0 flex h-full w-full items-center bg-black/10',
            (isAudio || aspectRatio.isPortrait) && isNormalView ? 'max-h-[71vh]' : 'aspect-video',
        ]"
    >
        <template v-if="isAudio">
            <LazyImage :src="poster_url" alt="Album Art" class="z-3 mx-auto h-full object-contain select-none" loading="eager" fetchpriority="high" />
            <div id="audio-poster" class="absolute top-0 left-0 h-full w-full blur-md" :style="audioPosterStyle"></div>
        </template>
        <LazyImage
            v-show="!isAudio && poster_url && !isThumbnailDismissed"
            :src="handleStorageURL(poster_url) ?? ''"
            alt="Thumbnail"
            :class="['z-3 mx-auto h-full']"
            loading="eager"
            fetchpriority="high"
        />
    </div>
</template>
