<script setup lang="ts">
import { ButtonCorner, ButtonIcon, ButtonText } from '@/components/cedar-ui/button';
import { ref } from 'vue';

import BasicInput from '@/components/cedar-ui/input/BasicInput.vue';

import ProiconsArrowSwap from '~icons/proicons/arrow-swap';
import ProiconsCancel from '~icons/proicons/cancel';

const props = defineProps<{
    isMaximised: boolean;
    closePanel: () => void;
    toggleScale: () => void;
}>();

const minFreq = ref(100);
const maxFreq = ref(16000);
const minDb = ref(-60);
const maxDb = ref(-24);
const numBins = ref(128);
const scale = ref<'log' | 'linear'>('linear');
</script>

<template>
    <div :class="['pointer-events-auto w-fit rounded-md border border-neutral-700/10 bg-neutral-800/90 p-2 backdrop-blur-xs sm:min-w-52', { 'top-6 p-4': isMaximised }]">
        <div
            class="scrollbar-minimal scrollbar-dark xs:h-16 flex max-h-12 w-full flex-col gap-1 overflow-y-auto pe-1 pb-1 *:ms-4 sm:h-auto sm:max-h-64 sm:overflow-visible! sm:pe-0"
        >
            <div class="ms-0! flex items-center justify-between gap-2">
                <h5>Audio Graph</h5>
                <ButtonCorner
                    title="Close Options"
                    @click="closePanel"
                    colour-classes="hover:bg-transparent"
                    text-classes="hover:text-danger-2"
                    position-classes="size-4 sticky top-0 self-start mx-1"
                >
                    <template #icon><ProiconsCancel /></template>
                </ButtonCorner>
            </div>
            <div class="flex items-center justify-between gap-2">
                <p title="File Buffer Health">
                    Scale: <span>{{ scale }}</span>
                </p>

                <ButtonIcon
                    class="hocus:ring-white size-6 h-full p-1 hover:bg-neutral-950"
                    :variant="'transparent'"
                    @click="
                        toggleScale();
                        scale = scale === 'log' ? 'linear' : 'log';
                    "
                >
                    <ProiconsArrowSwap
                /></ButtonIcon>
            </div>
            <template v-if="false">
                <div class="flex items-center justify-between gap-2">
                    <label for="min-freq" title="File Buffer Health">Min Frequency:</label>
                    <BasicInput id="min-freq" type="number" min="100" max="15000" class="h-5.5 w-16" v-model="minFreq"></BasicInput>
                </div>
                <div class="flex items-center justify-between gap-2">
                    <label for="max-freq" title="File Buffer Health">Max Frequency:</label>
                    <BasicInput id="max-freq" type="number" min="200" max="16000" class="h-5.5 w-16" v-model="maxFreq"></BasicInput>
                </div>
                <div class="flex items-center justify-between gap-2">
                    <label for="min-db" title="File Buffer Health">Min Db:</label>
                    <BasicInput id="min-db" type="number" min="100" max="15000" class="h-5.5 w-16" v-model="minFreq"></BasicInput>
                </div>
                <div class="flex items-center justify-between gap-2">
                    <label for="max-db" title="File Buffer Health">Max Db:</label>
                    <BasicInput id="max-db" type="number" min="200" max="16000" class="h-5.5 w-16" v-model="maxFreq"></BasicInput>
                </div>
                <div class="flex items-center justify-between gap-2">
                    <label for="num-bins" title="File Buffer Health">Bin Count:</label>
                    <BasicInput id="num-bins" class="h-5.5 w-16" v-model="numBins" type="number" min="16" max="512"></BasicInput>
                </div>

                <ButtonText class="ml-auto! h-5.5 w-16">Apply</ButtonText>
            </template>
        </div>
    </div>
</template>
<style lang="css" scoped>
span {
    color: var(--color-neutral-300); /* text-foreground-1 but dark only */
}
</style>
