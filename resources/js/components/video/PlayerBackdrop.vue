<script setup lang="ts">
import type { HTMLAttributes } from 'vue';

import { handleStorageURL } from '@/service/util';
import { inject } from 'vue';

import LazyImage from '@/components/lazy/LazyImage.vue';

const isAudio = inject<boolean>('isAudio');

const props = defineProps<{
    isTheatreView: boolean;
    isVisible?: boolean;
    isPlayerSizeConstrained?: boolean; // (isAudio OR isPortrait) AND isNormalView
    aspectRatio: {
        isPortrait: boolean;
        isAspectVideo: boolean;
    };
    audioPosterUrl: string;
    posterUrl?: string;
}>();

const generatePosterStyle = (url?: string): HTMLAttributes['style'] => {
    if (!url) return {};

    return {
        backgroundImage: `url("${url}")`,
        backgroundPosition: 'center',
        backgroundSize: 'cover',
        backgroundRepeat: 'no-repeat',
    };
};
</script>

<template>
    <Transition leave-from-class="opacity-100" leave-to-class="opacity-0" leave-active-class="absolute! inset-0 duration-700 transition-opacity ease-in-out">
        <div v-if="isVisible" :class="['pointer-events-none z-3 size-full', { 'aspect-video': !isPlayerSizeConstrained }, { relative: isVisible }]">
            <template v-if="isAudio">
                <div
                    id="audio-poster"
                    :class="['absolute inset-0 scale-105 blur-sm', { 'dark:scale-100': isPlayerSizeConstrained }]"
                    :style="generatePosterStyle(audioPosterUrl)"
                ></div>
                <LazyImage
                    :src="audioPosterUrl"
                    alt="Album Art"
                    wrapper-class="flex items-center justify-center"
                    :class="['mx-auto object-contain select-none md:h-screen', { 'max-h-[71vh]': isPlayerSizeConstrained }, { 'max-h-screen': isTheatreView }]"
                    loading="eager"
                    fetchpriority="high"
                />
            </template>
            <div v-else class="contents">
                <div id="thumbnail-blocker" class="absolute inset-0 scale-105 blur-sm" :style="generatePosterStyle(posterUrl)"></div>
                <LazyImage
                    :src="handleStorageURL(posterUrl) ?? ''"
                    alt="Thumbnail"
                    :class="['mx-auto h-full object-contain', { 'max-h-[71vh]': isPlayerSizeConstrained }]"
                    loading="eager"
                    fetchpriority="high"
                />
            </div>
        </div>
    </Transition>
</template>
