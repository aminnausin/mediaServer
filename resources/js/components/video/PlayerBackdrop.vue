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
    audioPosterUrl: string;
    posterUrl?: string;
    isLoading?: boolean;
    isVisible?: boolean;
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
</script>

<template>
    <!-- <LazyImage :src="audioPosterUrl" alt="Album Art" class="mx-auto h-full max-h-full max-w-full object-contain select-none" loading="eager" fetchpriority="high" /> -->
    <!-- <img :src="audioPosterUrl" class="mx-auto h-full max-h-[71vh] min-h-[56.25cqw] object-cover" /> -->
    <div
        v-if="isVisible"
        :class="[
            'pointer-events-none relative z-3',
            // (isAudio || aspectRatio.isPortrait) && isNormalView ? 'min-h-[56.25cqw]' : 'aspect-video',
            // isAudio && !isLoading ? 'min-h-[56.25cqw]' : 'absolute inset-0',
            // 'absolute inset-0',
            { 'aspect-video': aspectRatio.isAspectVideo },
        ]"
    >
        <template v-if="isAudio">
            <div id="audio-poster" class="absolute inset-0 -z-10 scale-105 blur-sm dark:scale-100" :style="audioPosterStyle"></div>
            <!-- <img :src="audioPosterUrl" :class="['mx-auto h-screen object-contain select-none', { 'max-h-[71vh]': isNormalView }]" /> -->
            <LazyImage
                :src="audioPosterUrl"
                alt="Album Art"
                :class="['mx-auto h-screen object-contain select-none', { 'max-h-[71vh]': isNormalView }]"
                loading="eager"
                fetchpriority="high"
            />
            <!-- <img :src="audioPosterUrl" class="mx-auto h-full max-h-[71vh] min-h-[56.25cqw] object-cover" /> -->
        </template>
        <!-- <img v-show="!isAudio && posterUrl && !isThumbnailDismissed" :src="handleStorageURL(posterUrl) ?? ''" class="mx-auto h-full max-h-[71vh] min-h-[56.25cqw] object-cover" /> -->
        <!-- <img
            v-if="!isAudio && posterUrl && !isThumbnailDismissed"
            :src="handleStorageURL(posterUrl) ?? ''"
            :class="['z-3 mx-auto h-full object-cover', { 'max-h-[71vh]': !aspectRatio.isAspectVideo && isNormalView }]"
        /> -->
        <LazyImage
            v-show="!isAudio && posterUrl && !isThumbnailDismissed"
            :src="handleStorageURL(posterUrl) ?? ''"
            alt="Thumbnail"
            :wrapper-class="[{ 'max-h-[71vh]': !aspectRatio.isAspectVideo && isNormalView }]"
            :class="['z-3 mx-auto h-full object-cover']"
            loading="eager"
            fetchpriority="high"
        />
    </div>
</template>
<style lang="css" scoped>
.poster-container {
    --width: calc();
}
</style>
