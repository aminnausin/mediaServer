<script setup lang="ts">
import type { VideoResource } from '@/contracts/media';

import BlurhashImage from '@/components/lazy/BlurhashImage.vue';
import MediaTag from '@/components/labels/MediaTag.vue';

defineProps<{ media: VideoResource; isPaused: boolean }>();

const emit = defineEmits<{ play: [] }>();
</script>
<template>
    <div v-if="media.metadata?.poster_image?.path" class="pointer-events-auto h-full min-w-0 flex-col justify-center gap-6 p-8">
        <div
            class="relative max-w-72 cursor-pointer 2xl:max-w-96"
            :style="{ animation: 'spin 10s linear infinite', animationPlayState: isPaused ? 'paused' : 'running' }"
            @click="emit('play')"
        >
            <BlurhashImage
                class="aspect-square size-full rounded-full border-2 border-white/5 object-cover object-top shadow-xs"
                alt="Album Art"
                :wrapper-class="'aspect-square size-full'"
                :blurhash="media.metadata.poster_image.blur_hash"
                :src="media.metadata.poster_image.path"
                :style="{
                    maskImage: 'radial-gradient(circle, transparent 0 10%, black 10.5% 100%)',
                    WebkitMaskImage: 'radial-gradient(circle, transparent 0 10%, black 10.5% 100%)',
                }"
            />
            <div class="absolute inset-0 z-4 flex items-center justify-center">
                <div class="size-[16%] rounded-full ring-2"></div>
            </div>
        </div>
        <div class="space-y-1 p-4 text-left">
            <div class="flex items-start gap-2">
                <h3 class="line-clamp-2 text-xl text-shadow-xs">{{ media.title }}</h3>
                <div class="flex h-7 flex-1 items-center gap-1 *:pointer-events-none *:text-xs *:shadow-xs">
                    <MediaTag class="text-foreground-1 dark:text-foreground-4 h-5">{{ media.metadata.codec }}</MediaTag>
                    <MediaTag v-if="media.metadata.bitrate && media.metadata.codec === 'mp3'" class="3xl:block text-foreground-1 dark:text-foreground-4 hidden h-5"
                        >{{ media.metadata.bitrate / 1000 }}kbps</MediaTag
                    >
                </div>
            </div>
            <p class="truncate text-sm text-shadow-xs">
                {{ media.artist }}
            </p>
            <p class="truncate text-sm opacity-80 text-shadow-xs">
                {{ media.album }}
            </p>
        </div>
    </div>
</template>
