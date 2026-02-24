<script setup lang="ts">
import { useContentStore } from '@/stores/ContentStore';
import { storeToRefs } from 'pinia';
import { ButtonText } from '@/components/cedar-ui/button';
import { computed } from 'vue';

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
    <Transition enter-from-class="opacity-0" enter-to-class="opacity-100" leave-from-class="opacity-100" leave-to-class="opacity-0">
        <div
            :class="['absolute bottom-22 transition-opacity duration-200 ease-in-out', isNormalView ? 'left-2' : 'left-4']"
            v-cloak
            v-if="intro.isActive && isShowingControls"
            style="z-index: 7"
        >
            <ButtonText class="pointer-events-auto bg-neutral-900/60 text-white ring-0 backdrop-blur-xs select-none" @click="handleAutoSeek(intro.timeRemaining)">
                <template #default>Skip Intro ({{ Math.round(intro.timeRemaining) }}s)</template>
                <template #icon><ProiconsFastForward class="size-4" /></template>
            </ButtonText>
        </div>
    </Transition>
</template>
