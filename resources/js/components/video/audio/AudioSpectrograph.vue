<script setup lang="ts">
import { inject, onMounted, onUnmounted, ref, useTemplateRef, watch, type Ref } from 'vue';
import { AudioSpectrograph } from '@/service/audio/AudioSpectrograph';
import { debounce } from 'lodash-es';

const props = defineProps<{ isEnabled?: boolean }>();

const canvas = useTemplateRef('canvas');
const player = inject<Ref<HTMLVideoElement>>('player');

const isDrawing = ref(false);

let spectrograph: AudioSpectrograph | null = null;
let ro: ResizeObserver | null = null;

const handlePlay = () => {
    if (!spectrograph || spectrograph.isDrawing || player?.value.paused) return;
    spectrograph.start();
    isDrawing.value = true;
};

const handlePause = () => {
    if (spectrograph) spectrograph.stop();
    isDrawing.value = false;
    // This was my original fill style spectrograph.setFillStyle(solidColour('hsl(329.15 100% 45%)'));
};

function resizeSpectrograph() {
    if (!spectrograph || !canvas.value || !player?.value) return;
    spectrograph.resize(player.value.offsetWidth, Math.floor(player.value.offsetHeight / 4));
}

function toggleScale() {
    if (spectrograph?.getUseLog()) spectrograph.setLinearScale();
    else spectrograph?.setLogScale();
}

onMounted(async () => {
    if (!player?.value || !canvas.value) return;

    spectrograph = new AudioSpectrograph(canvas.value, player.value);
    resizeSpectrograph();
    player.value.addEventListener('play', handlePlay);
    player.value.addEventListener('pause', handlePause);

    ro = new ResizeObserver(debounce(resizeSpectrograph, 150));
    ro.observe(player.value);
});

onUnmounted(() => {
    spectrograph?.destroy();

    if (player?.value) {
        player.value.removeEventListener('play', handlePlay);
        player.value.removeEventListener('pause', handlePause);
    }

    ro?.disconnect();
});

watch(
    () => props.isEnabled,
    (value) => {
        if (spectrograph === null) return;
        if (value) return handlePlay();
        handlePause();
    },
);

defineExpose({ toggleScale });
</script>

<template>
    <Transition enter-to-class="opacity-100" enter-from-class="opacity-0" leave-from-class="opacity-100" leave-to-class="opacity-0">
        <canvas v-cloak v-show="isEnabled && isDrawing" aria-hidden="true" tabindex="-1" class="mx-auto mt-auto w-full opacity-0 transition-opacity duration-700" ref="canvas" />
    </Transition>
</template>
