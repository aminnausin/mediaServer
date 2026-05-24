<script setup lang="ts">
import type { StoryboardResource } from '@/contracts/media';
import type { HTMLAttributes } from 'vue';
import type { StoryboardCue } from '@/service/storyboard/types';

import { buildStoryboardCues } from '@/service/storyboard';
import { useContentStore } from '@/stores/ContentStore';
import { useBreakpoints } from '@vueuse/core';
import { storeToRefs } from 'pinia';
import { computed } from 'vue';
import { cn } from '@aminnausin/cedar-ui';

const { stateVideo } = storeToRefs(useContentStore());

const breakpoints = useBreakpoints({ xs: 320 });
const isDesktop = breakpoints.greaterOrEqual('xs');

const props = defineProps<{
    tooltipTime: number;
}>();

const storyboard = computed<StoryboardResource | undefined>(() => stateVideo.value.storyboard);

const storyboardCues = computed<StoryboardCue[]>(() => {
    const uuid = stateVideo.value.metadata?.uuid;
    const durationSeconds = stateVideo.value.duration;

    if (!storyboard.value || !uuid || !durationSeconds) {
        return [];
    }

    return buildStoryboardCues(uuid, durationSeconds, storyboard.value);
});

const activeCue = computed<StoryboardCue | undefined>(() => storyboardCues.value.find((c) => props.tooltipTime >= c.start && props.tooltipTime < c.end) ?? storyboardCues.value[0]);

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
    <div v-if="activeCue" :class="cn('xs:w-40 w-20 rounded border border-neutral-800 bg-neutral-800')" :style="spriteStyle"></div>
</template>
