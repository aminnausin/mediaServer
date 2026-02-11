<script setup lang="ts">
import type { HTMLAttributes } from 'vue';

import { handleStorageURL } from '@/service/util';
import { computed, inject } from 'vue';

import LazyImage from '@/components/lazy/LazyImage.vue';

const props = defineProps<{
    isNormalView: boolean;
    isTheatreView: boolean;
    isVisible?: boolean;
    aspectRatio: {
        isPortrait: boolean;
        isAspectVideo: boolean;
    };
    audioPosterUrl: string;
    posterUrl?: string;
}>();

const isAudio = inject<boolean>('isAudio');

const audioPosterStyle = computed<HTMLAttributes['style']>(() => {
    return {
        backgroundImage: `url("${props.audioPosterUrl}")`,
        backgroundPosition: 'center',
        backgroundSize: 'cover',
        backgroundRepeat: 'no-repeat',
    };
});

const videoPosterStyle = computed<HTMLAttributes['style']>(() => {
    return {
        backgroundImage: `url("${props.posterUrl}")`,
        backgroundPosition: 'center',
        backgroundSize: 'cover',
        backgroundRepeat: 'no-repeat',
    };
});
</script>

<template>
    <Transition leave-from-class="opacity-100" leave-to-class="opacity-0" leave-active-class="absolute! inset-0 duration-700 transition-opacity ease-in-out">
        <div v-if="isVisible" :class="['pointer-events-none z-3 size-full', { 'aspect-video': aspectRatio.isAspectVideo }, { relative: isVisible }]">
            <template v-if="isAudio">
                <div id="audio-poster" :class="['absolute inset-0 scale-105 blur-sm', { 'dark:scale-100': isNormalView }]" :style="audioPosterStyle"></div>
                <LazyImage
                    :src="audioPosterUrl"
                    alt="Album Art"
                    wrapper-class="flex items-center justify-center"
                    :class="['mx-auto object-contain select-none md:h-screen', { 'max-h-[71vh]': isNormalView }, { 'max-h-screen': isTheatreView }]"
                    loading="eager"
                    fetchpriority="high"
                />
            </template>
            <div v-else class="contents">
                <div id="thumbnail-blocker" class="absolute inset-0 scale-105 blur-sm" :style="videoPosterStyle"></div>
                <LazyImage
                    :src="handleStorageURL(posterUrl) ?? ''"
                    alt="Thumbnail"
                    :wrapper-class="[{ 'max-h-[71vh]': !aspectRatio.isAspectVideo && isNormalView }]"
                    :class="['mx-auto h-full object-contain']"
                    loading="eager"
                    fetchpriority="high"
                />
            </div>
        </div>
    </Transition>
</template>
