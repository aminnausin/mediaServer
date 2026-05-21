<script setup lang="ts">
import type { StoryboardResource } from '@/contracts/media';
import type { HTMLAttributes } from 'vue';

import { useContentStore } from '@/stores/ContentStore';
import { useBreakpoints } from '@vueuse/core';
import { storeToRefs } from 'pinia';
import { computed } from 'vue';
import { cn } from '@aminnausin/cedar-ui';

interface ThumbnailCue {
    start: number;
    end: number;
    image: string;
    x: number;
    y: number;
    w: number;
    h: number;
}

const { stateVideo } = storeToRefs(useContentStore());

const breakpoints = useBreakpoints({ xs: 320 });
const isDesktop = breakpoints.greaterOrEqual('xs');

const props = defineProps<{
    tooltipTime: number;
}>();

const storyboard = computed<StoryboardResource | undefined>(() => stateVideo.value.storyboard);

const thumbnailCues = computed<ThumbnailCue[]>(() => {
    const uuid = stateVideo.value.metadata?.uuid;
    const durationSeconds = stateVideo.value.duration;

    if (!storyboard.value || !uuid || !durationSeconds) {
        return [];
    }

    const { tile_rows: rows, tile_cols: cols, tile_height: tileHeight, tile_width: tileWidth, interval_ms: interval_ms } = storyboard.value;

    const interval = interval_ms / 1000;
    const tilesPerSheet = rows * cols;
    const cues = [];

    for (let t = 0; t < durationSeconds; t += interval) {
        const index = Math.floor(t / interval);
        const sheet = Math.floor(index / tilesPerSheet) + 1;
        const tileIndex = index % tilesPerSheet;
        const col = tileIndex % cols;
        const row = Math.floor(tileIndex / cols);

        cues.push({
            start: t,
            end: Math.min(t + interval, durationSeconds),
            image: `/storage/metadata/${uuid.slice(0, 2)}/${uuid}/storyboard/${sheet}.jpg`,
            x: col * tileWidth,
            y: row * tileHeight,
            w: tileWidth,
            h: tileHeight,
        });
    }

    return cues;
});

const activeCue = computed<ThumbnailCue | undefined>(() => thumbnailCues.value.find((c) => props.tooltipTime >= c.start && props.tooltipTime < c.end) ?? thumbnailCues.value[0]);

const spriteStyle = computed<HTMLAttributes['style']>(() => {
    const c = activeCue.value;
    if (!c || c.x === undefined || !storyboard.value) return undefined;

    const maxWidth = isDesktop.value ? 160 : 80;

    const scale = maxWidth / c.w;
    return {
        backgroundImage: `url(${c.image})`,
        backgroundPosition: `-${c.x * scale}px -${c.y * scale}px`,
        backgroundSize: `${c.w * storyboard.value.tile_cols * scale}px ${c.h * storyboard.value.tile_rows * scale}px`,
        width: `${maxWidth}px`,
        height: `${c.h * scale}px`,
    };
});
</script>

<template>
    <div v-if="activeCue" :class="cn('xs:w-40 w-20 rounded border border-neutral-800')" :style="spriteStyle"></div>
</template>
