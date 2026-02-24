<script setup lang="ts">
import { useContentStore } from '@/stores/ContentStore';
import { storeToRefs } from 'pinia';
import { ButtonText } from '@/components/cedar-ui/button';
import { computed } from 'vue';

import ProiconsFastForward from '~icons/proicons/fast-forward';

const props = defineProps<{
    isShowingControls: boolean;
    handleAutoSeek: (seconds: number) => void;
    timeElapsed: number;
    timeDuration: number;
    isNormalView: boolean;
}>();

const { stateVideo, stateFolder } = storeToRefs(useContentStore());

const currentTime = computed(() => (props.timeElapsed * props.timeDuration) / 100); // This is pre-optimised because timeElapsed does not change if controls are not showing which is cool
const introDuration = computed(() => stateVideo.value.intro_duration ?? stateFolder.value.series?.avg_intro_duration);
const introStart = computed(() => stateVideo.value.intro_start);

const intro = computed(() => {
    // Catch intro start as a null value
    if (introStart.value == undefined || introDuration.value == undefined)
        return {
            isActive: false,
            timeRemaining: 0,
        };

    const current = currentTime.value;
    const start = introStart.value;
    const end = start + introDuration.value;

    console.log(current, start, end, introDuration.value, Math.max(end - current, 0));

    return {
        isActive: current >= start && current < end,
        timeRemaining: Math.max(end - current, 0),
    };
});
</script>
<template>
    <div class="text-foreground-0 pointer-events-auto absolute top-2 left-2 flex flex-col gap-2" style="z-index: 10">
        <!-- <FormNumberField :field="{ name: 'introStart', type: 'number', min: 0, default: 0 }" v-model="introStart" /> -->
        <!-- <FormNumberField :field="{ name: 'introDuration', type: 'number', min: 0, default: 90 }" v-model="introDuration" /> -->
        <p class="bg-surface-0 text-foreground-0 p-1">{{ introStart === undefined ? 'und' : (introStart ?? 'null') }}</p>
        <p class="bg-surface-0 text-foreground-0 p-1">{{ introDuration }}</p>
        <p class="bg-surface-0 text-foreground-0 p-1">{{ intro.timeRemaining }}</p>
    </div>
    <Transition enter-from-class="opacity-0" enter-to-class="opacity-100" leave-from-class="opacity-100" leave-to-class="opacity-0">
        <div
            :class="['absolute bottom-22 transition-opacity duration-200 ease-in-out', isNormalView ? 'left-2' : 'left-4']"
            v-cloak
            v-show="intro.isActive && isShowingControls"
            style="z-index: 7"
        >
            <ButtonText class="text-foreground-0 pointer-events-auto bg-neutral-900/60 ring-0 backdrop-blur-xs select-none" @click="handleAutoSeek(intro.timeRemaining)">
                <template #default>Skip Intro ({{ Math.floor(intro.timeRemaining) }}s)</template>
                <template #icon><ProiconsFastForward class="size-4" /></template>
            </ButtonText>
        </div>
    </Transition>
</template>
