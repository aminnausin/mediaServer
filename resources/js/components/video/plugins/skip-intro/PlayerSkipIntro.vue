<script setup lang="ts">
import { useContentStore } from '@/stores/ContentStore';
import { storeToRefs } from 'pinia';
import { computed } from 'vue';

import PlayerOverlayButton from '@/components/video/button/PlayerOverlayButton.vue';

import ProiconsFastForward from '~icons/proicons/fast-forward';

const props = defineProps<{
    isShowingControls: boolean;
    handleAutoSeek: (seconds: number) => void;
    timeElapsedPercent: number;
    timeDuration: number;
    isNormalView: boolean;
}>();

const { stateVideo, stateFolder } = storeToRefs(useContentStore());

const currentTime = computed(() => (props.timeElapsedPercent * props.timeDuration) / 100); // This is pre-optimised because timeElapsed does not change if controls are not showing which is cool
const introDuration = computed(() => stateVideo.value.intro_duration ?? stateFolder.value.series?.avg_intro_duration);
const introStart = computed(() => stateVideo.value.intro_start);

const intro = computed(() => {
    // Catch intro start as a null value
    if (introStart.value == null || introDuration.value == null)
        return {
            isActive: false,
            timeRemaining: 0,
        };

    const current = currentTime.value;
    const start = introStart.value;
    const end = start + introDuration.value;

    return {
        isActive: current >= start && current < end,
        timeRemaining: Math.max(end - current, 0),
    };
});
</script>
<template>
    <PlayerOverlayButton v-cloak :is-visible="intro.isActive && isShowingControls" style="z-index: 7" @click="handleAutoSeek(intro.timeRemaining)">
        <template #default>Skip Intro ({{ Math.round(intro.timeRemaining) }}s)</template>
        <template #icon><ProiconsFastForward class="size-4" /></template>
    </PlayerOverlayButton>
</template>
