<script setup lang="ts">
import { onMounted, onUnmounted, ref, useTemplateRef } from 'vue';
import { AudioSpectrograph } from '@/service/AudioSpectrograph/AudioSpectrograph';

const isDrawing = ref(false);
const isEnabled = ref(true);

const canvas = useTemplateRef('canvas');
const player = ref<HTMLVideoElement | null>(null);
const ctx = ref<CanvasRenderingContext2D | null>(null);

let spectrograph: AudioSpectrograph | null = null;

const handlePlay = () => {
    if (spectrograph) {
        isDrawing.value = true;
        spectrograph.start();
    }
};

const handlePause = () => {
    if (spectrograph) spectrograph.stop();
    isDrawing.value = false;
};

const setSpectrographColours = () => {
    if (!ctx.value || !canvas.value) return;

    const gradient = ctx.value.createLinearGradient(0, canvas.value.height, 0, 0);
    gradient.addColorStop(0, '#9f7aeaff'); // purple-400
    gradient.addColorStop(1, '#ed64a6ff'); // pink-400
    ctx.value.fillStyle = gradient;
};

onMounted(async () => {
    player.value = document.getElementById('video-source') as HTMLVideoElement;
    if (!player.value || !canvas.value) return;

    spectrograph = new AudioSpectrograph(canvas.value, player.value);

    function resize() {
        if (!spectrograph || !canvas.value || !player.value) return;
        spectrograph.resize(player.value.offsetWidth, Math.floor(player.value.offsetHeight / 4));
    }

    resize();
    player.value.addEventListener('play', handlePlay);
    player.value.addEventListener('pause', handlePause);

    window.addEventListener('resize', resize);
});

onUnmounted(() => {
    isDrawing.value = false;

    if (player.value) {
        player.value.removeEventListener('play', handlePlay);
        player.value.removeEventListener('pause', handlePause);
    }
    spectrograph?.destroy();

    window.removeEventListener('resize', () => {});
});
</script>

<template>
    <Transition enter-to-class="opacity-100" enter-from-class="opacity-0" leave-from-class="opacity-100" leave-to-class="opacity-0">
        <canvas v-cloak v-show="isDrawing && isEnabled" aria-hidden="true" tabindex="-1" class="mx-auto mt-auto w-full opacity-0 transition-opacity duration-700" ref="canvas" />
    </Transition>
</template>
