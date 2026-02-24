<script setup lang="ts">
import { FormNumberField } from '@/components/cedar-ui/number-field';
import { computed, ref } from 'vue';
import { ButtonText } from '@/components/cedar-ui/button';

import ProiconsFastForward from '~icons/proicons/fast-forward';

const props = defineProps<{
    isShowingControls: boolean;
    handleAutoSeek: (seconds: number) => void;
    timeElapsed: number;
    timeDuration: number;
}>();

const introStart = ref(0);
const introDuration = ref(90);

const currentTime = computed(() => (props.timeElapsed * props.timeDuration) / 100); // This is pre-optimised because timeElapsed does not change if controls are not showing which is cool

const intro = computed(() => {
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
    <div class="text-foreground-0 pointer-events-auto absolute top-2 left-2 flex flex-col gap-2" style="z-index: 10">
        <FormNumberField :field="{ name: 'introStart', type: 'number', min: 0, default: 0 }" v-model="introStart" />
        <FormNumberField :field="{ name: 'introEnd', type: 'number', min: 0, default: 90 }" v-model="introDuration" />
        <p class="bg-surface-0 text-foreground-0 p-1">{{ currentTime }}</p>
    </div>
    <Transition
        enter-active-class="ease-out"
        enter-from-class="opacity-0"
        enter-to-class="opacity-100"
        leave-active-class="ease-in"
        leave-from-class="opacity-100"
        leave-to-class="opacity-0"
    >
        <div class="absolute bottom-22 left-2 transition-opacity duration-300" v-cloak v-show="intro.isActive" style="z-index: 7">
            <ButtonText
                class="text-foreground-0 pointer-events-auto ring-0 backdrop-blur-xs select-none dark:bg-neutral-900/60"
                @click="
                    () => {
                        handleAutoSeek(intro.timeRemaining);
                    }
                "
            >
                <template #default>Skip Intro ({{ Math.floor(intro.timeRemaining) }}s)</template>
                <template #icon><ProiconsFastForward class="size-4" /></template>
            </ButtonText>
        </div>
    </Transition>
</template>
